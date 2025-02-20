<div class="container mx-auto p-6">
    <h2 class="text-xl font-bold mb-4">Lista de Clientes</h2>
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $cliente->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $cliente->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
