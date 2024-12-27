<div>
    <h1 class="text-lg font-bold mb-4">Roles Asignados a Usuarios</h1>
    <table class="table-auto w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Usuario</th>
                <th class="border px-4 py-2">Roles</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="border px-4 py-2">{{ $user->name }}</td>
                    <td class="border px-4 py-2">
                        @foreach($user->roles as $role)
                            <span class="bg-blue-500 text-white text-sm px-2 py-1 rounded">{{ $role->name }}</span>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
