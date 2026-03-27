<div x-data="{
    color: @entangle('color'),
    mostrarLogo: @entangle('mostrar_logo'),
    baseImage: '/images/mockups/base_producto.png',
    logoImage: '/images/mockups/logo_cliente.png'
}" class="relative mx-auto mt-6 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-900">

    <!-- Imagen base -->
    <img :src=\"baseImage\" class=\"w-full rounded\" alt=\"Mockup Base\">

    <!-- Capa de color -->
    <div class=\"absolute inset-0 rounded opacity-30\" :style=\"{ backgroundColor: color }\"></div>

    <!-- Logo (opcional) -->
    <img x-show=\"mostrarLogo\" :src=\"logoImage\" class=\"absolute top-8 left-8 w-20 h-20 rounded\" alt=\"Logo\">

    <!-- Controles -->
    <div class=\"mt-4 space-y-2\">
        <label class=\"text-sm text-gray-600 dark:text-gray-300\">Color del fondo:</label>
        <input type=\"color\" x-model=\"color\" class=\"h-8 w-16 rounded border border-gray-300 dark:border-gray-600\" />

        <label class=\"inline-flex items-center space-x-2\">
            <input type=\"checkbox\" x-model=\"mostrarLogo\" class=\"rounded border-gray-300\" />
            <span class=\"text-sm text-gray-600 dark:text-gray-300\">Mostrar logo</span>
        </label>
    </div>
</div>
