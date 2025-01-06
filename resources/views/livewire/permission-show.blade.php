<div>
    <div class="mb-4">
        <input
            type="text"
            class="form-control"
            placeholder="Buscar permisos..."
            wire:model="search"
        />
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($permissions as $permission)
                <tr>
                    <td>{{ $permission->id }}</td>
                    <td>{{ $permission->name }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" wire:click="edit({{ $permission->id }})">Editar</button>
                        <button class="btn btn-danger btn-sm" wire:click="delete({{ $permission->id }})">Eliminar</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No se encontraron permisos</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $permissions->links() }}
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success mt-3">
            {{ session('message') }}
        </div>
    @endif
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        // Swal.fire({
        //             title: "Eliminar vacante?",
        //             text: "Una vacante eliminada no se puede recuperar !",
        //             icon: "warning",
        //             showCancelButton: true,
        //             confirmButtonColor: "#3085d6",
        //             cancelButtonColor: "#d33",
        //             confirmButtonText: "Si, eliminalo !",
        //             cancelButtonText: "Cancelar"
        //             }).then((result) => {
        //             if (result.isConfirmed) {
        //                 Swal.fire({
        //                 title: "vacante eliminada!",
        //                 text: "tu vacante se elimino correctamente.",
        //                 icon: "success"
        //                 });
        //             }
        });
    </script>
@endpush
