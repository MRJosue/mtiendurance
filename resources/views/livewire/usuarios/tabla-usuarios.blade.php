<div class="p-3">
    <div class="mb-4 grid grid-cols-3 gap-4">
        <div>
            <label for="search-id" class="block text-sm font-medium text-gray-700">Buscar por ID</label>
            <input id="search-id" type="text" wire:model="search.id" placeholder="ID"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="search-name" class="block text-sm font-medium text-gray-700">Buscar por Nombre</label>
            <input id="search-name" type="text" wire:model="search.name" placeholder="Nombre"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="search-email" class="block text-sm font-medium text-gray-700">Buscar por Email</label>
            <input id="search-email" type="text" wire:model="search.email" placeholder="Email"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div class="mb-4">
        <button wire:click="searchUsers" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-md shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Buscar
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $index => $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">@livewire('modal-component', ['id' => $user->id, 'component' =>'assign-roles','titulo' => 'Editar Permisos','methodname'=>'userId'], key('modal-'.$user->id))</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4" wire:noscroll>
        {{ $users->links() }}
    </div>


</div>


@push('scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

        <!-- Cargar jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
        $(document).ready(function () {
            console.log('jQuery activo');
            scrollTopValue = 0;
            $(window).on('scroll', function () {
                scrollTopValue = $(this).scrollTop();

            });

            // Apuntar a los enlaces de paginación de Livewire
            $(document).on('click', 'a[wire\\:click], button[wire\\:click]', function (e) {

                // Realiza scroll suave hacia arriba
                $('html, body').animate({
                    scrollTop: scrollTopValue
                }, 300); // 600ms para el efecto
            });
        });
        </script>

@endpush
