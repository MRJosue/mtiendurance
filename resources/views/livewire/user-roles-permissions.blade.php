<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Roles y Permisos Asignados</h2>

        <div x-data="{ roles: @entangle('roles'), permissions: @entangle('permissions') }">
            <div class="max-h-[300px] overflow-y-auto space-y-4 pr-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Roles:</h3>
                    <template x-if="roles.length > 0">
                        <ul class="list-disc list-inside space-y-2">
                            <template x-for="role in roles" :key="role">
                                <li class="text-gray-700 px-4 py-2 bg-gray-100 rounded-md">
                                    <span class="font-semibold text-blue-600" x-text="role"></span>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="roles.length === 0">
                        <p class="text-gray-500">No tienes roles asignados.</p>
                    </template>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mt-4 mb-2">Permisos:</h3>
                    <template x-if="permissions.length > 0">
                        <ul class="list-disc list-inside space-y-2">
                            <template x-for="permission in permissions" :key="permission">
                                <li class="text-gray-700 px-4 py-2 bg-green-100 rounded-md">
                                    <span class="font-semibold text-green-600" x-text="permission"></span>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="permissions.length === 0">
                        <p class="text-gray-500">No tienes permisos asignados.</p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
