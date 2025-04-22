<div x-data="{
    color: @entangle('color'),
    mostrarLogo: @entangle('mostrar_logo'),
    baseImage: '/images/mockups/base_producto.png',
    logoImage: '/images/mockups/logo_cliente.png'
}" class="relative w-full max-w-md mx-auto mt-6 border p-4 bg-white rounded-lg shadow">

    <!-- Imagen base -->
    <img :src=\"baseImage\" class=\"w-full rounded\" alt=\"Mockup Base\">

    <!-- Capa de color -->
    <div class=\"absolute inset-0 rounded opacity-30\" :style=\"{ backgroundColor: color }\"></div>

    <!-- Logo (opcional) -->
    <img x-show=\"mostrarLogo\" :src=\"logoImage\" class=\"absolute top-8 left-8 w-20 h-20 rounded\" alt=\"Logo\">

    <!-- Controles -->
    <div class=\"mt-4 space-y-2\">
        <label class=\"text-sm text-gray-600\">Color del fondo:</label>
        <input type=\"color\" x-model=\"color\" class=\"w-16 h-8 rounded border\" />

        <label class=\"inline-flex items-center space-x-2\">
            <input type=\"checkbox\" x-model=\"mostrarLogo\" class=\"rounded border-gray-300\" />
            <span class=\"text-sm text-gray-600\">Mostrar logo</span>
        </label>
    </div>
</div>
