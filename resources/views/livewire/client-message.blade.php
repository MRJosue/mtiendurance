
<div>
    @if ($showMessage)
    <div class="container mx-auto p-6">
        <div class="bg-green-100 text-green-700 p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Bienvenido Cliente</h2>
            <p>¡Tienes acceso especial como cliente con permisos exclusivos!</p>
        </div>
    </div>
@endif

<!-- Muestra la información de depuración solo para probar -->
<div class="bg-gray-100 p-4 mt-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold">Depuración:</h3>
    <p><strong>Roles del usuario:</strong> {{ implode(', ', $roles) }}</p>
    <p><strong>Permisos del usuario:</strong> {{ implode(', ', $permissions) }}</p>
</div>


</div>

