<div class="p-4 bg-white rounded-lg shadow max-w-md mx-auto">
    <form wire:submit.prevent="switchUser">
        <div class="mb-4">
            <label class="block mb-1 font-medium text-gray-700">Seleccionar Usuario</label>
            <select wire:model="userId" class="w-full border-gray-300 rounded-lg shadow-sm">
                @foreach($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }} ({{ $user->email }})
                        @if(auth()->id() == $user->id) — Actual
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

       <button :class="'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full'">
            Switch User
        </button>
    </form>
</div>
