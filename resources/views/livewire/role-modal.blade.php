<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-lg w-1/2">
        <h2 class="text-lg font-bold mb-4">{{ $isEditing ? 'Edit Role' : 'Create Role' }}</h2>

        <form wire:submit.prevent="{{ $isEditing ? 'updateRole' : 'createRole' }}">
            <div class="mb-4">
                <label for="name" class="block text-sm font-bold">Name</label>
                <input type="text" id="name" wire:model="name" class="w-full border rounded p-2">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label for="guard_name" class="block text-sm font-bold">Guard Name</label>
                <input type="text" id="guard_name" wire:model="guard_name" class="w-full border rounded p-2">
                @error('guard_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label for="permissions" class="block text-sm font-bold">Permissions</label>
                <select multiple id="permissions" wire:model="selectedPermissions" class="w-full border rounded p-2">
                    @foreach ($permissions as $permission)
                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <button type="button" wire:click="closeModal" class="btn btn-secondary mr-2">Cancel</button>
                <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update' : 'Save' }}</button>
            </div>
        </form>
    </div>
</div>
