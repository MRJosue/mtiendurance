export default function imageZoom() {
  return {
    open: false,
    src: '',
    scale: 1,
    translateX: 0,
    translateY: 0,
    startX: 0,
    startY: 0,
    dragging: false,

    openModal(url) {
      this.src = url;
      this.open = true;
      this.reset();
    },

    onWheel(e) {
      const delta = e.deltaY > 0 ? -0.1 : 0.1;
      this.scale = Math.min(Math.max(this.scale + delta, 1), 5);
      this.applyTransform();
    },

    onMouseDown(e) {
      this.dragging = true;
      this.startX = e.clientX - this.translateX;
      this.startY = e.clientY - this.translateY;
      this.$refs.img.classList.add('cursor-grabbing');
      window.addEventListener('mousemove', this.onMouseMove.bind(this));
      window.addEventListener('mouseup', this.onMouseUp.bind(this));
    },

    onMouseMove(e) {
      if (!this.dragging) return;
      this.translateX = e.clientX - this.startX;
      this.translateY = e.clientY - this.startY;
      this.applyTransform();
    },

    onMouseUp() {
      this.dragging = false;
      this.$refs.img.classList.remove('cursor-grabbing');
      window.removeEventListener('mousemove', this.onMouseMove);
      window.removeEventListener('mouseup', this.onMouseUp);
    },

    zoomIn() {
      this.scale = Math.min(this.scale + 0.2, 5);
      this.applyTransform();
    },

    zoomOut() {
      this.scale = Math.max(this.scale - 0.2, 1);
      this.applyTransform();
    },

    reset() {
      this.scale = 1;
      this.translateX = 0;
      this.translateY = 0;
      this.applyTransform();
    },

    applyTransform() {
      this.$refs.img.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`;
    },

    close() {
      this.open = false;
    }
  };
}
