import '../css/app.css';
import './bootstrap';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

import imageZoom from './components/image-zoom';

window.imageZoom = imageZoom;
window.Echo = Echo;
window.Pusher = Pusher;
window.Dropzone = Dropzone;

const THEME_STORAGE_KEY = 'theme';
const DARK_CLASS = 'dark';

const getPreferredTheme = () => {
  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);

  if (savedTheme === 'light' || savedTheme === 'dark') {
    return savedTheme;
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme) => {
  const isDark = theme === 'dark';

  document.documentElement.classList.toggle(DARK_CLASS, isDark);
  document.documentElement.setAttribute('data-theme', theme);
};

window.themeManager = {
  init() {
    applyTheme(getPreferredTheme());
  },
  set(theme) {
    localStorage.setItem(THEME_STORAGE_KEY, theme);
    applyTheme(theme);
  },
  toggle() {
    const currentTheme = document.documentElement.classList.contains(DARK_CLASS) ? 'dark' : 'light';
    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

    this.set(nextTheme);

    return nextTheme;
  },
  current() {
    return document.documentElement.classList.contains(DARK_CLASS) ? 'dark' : 'light';
  }
};

window.themeManager.init();

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
  if (localStorage.getItem(THEME_STORAGE_KEY)) {
    return;
  }

  applyTheme(event.matches ? 'dark' : 'light');
});

document.addEventListener('alpine:init', () => {
  Alpine.store('theme', {
    current: window.themeManager.current(),
    toggle() {
      this.current = window.themeManager.toggle();
    },
    set(theme) {
      window.themeManager.set(theme);
      this.current = theme;
    }
  });

  if (!Alpine.store('ui')) {
    Alpine.store('ui', {
      sidebarOpen: window.innerWidth >= 1024,
      sidebarForced: false,
      openSections: JSON.parse(localStorage.getItem('openSections') || '{}'),
      selectedRoute: document.body.dataset.routeName || '',

      toggleSection(name) {
        this.openSections[name] = !this.openSections[name];
        localStorage.setItem('openSections', JSON.stringify(this.openSections));
      },
      isActive(route) {
        return this.selectedRoute === route;
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024) {
        Alpine.store('ui').sidebarOpen = true;
      } else if (Alpine.store('ui').sidebarForced) {
        Alpine.store('ui').sidebarOpen = false;
      }
    });
  }
});
