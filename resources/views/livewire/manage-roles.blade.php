<div>
    <h1 class="text-xl font-bold mb-4">Manage Roles</h1>

    <button class="btn btn-primary mb-4" wire:click="openModal">Create Role</button>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <table class="table-auto w-full">
        <thead>
            <tr>
                <th>Name</th>
                <th>Guard</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->guard_name }}</td>
                    <td>
                        <button class="btn btn-secondary" wire:click="editRole({{ $role->id }})">Edit</button>
                        <button class="btn btn-danger" wire:click="deleteRole({{ $role->id }})">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($isModalOpen)
        @include('livewire.role-modal')
    @endif
</div>
