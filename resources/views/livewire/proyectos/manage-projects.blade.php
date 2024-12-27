<div>
    <h1 class="text-xl font-bold mb-4">Lista de Proyectos</h1>

    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Cliente</th>
                <th class="border border-gray-300 px-4 py-2">Nombre</th>
                <th class="border border-gray-300 px-4 py-2">Descripción</th>
                <th class="border border-gray-300 px-4 py-2">Estado</th>
                <th class="border border-gray-300 px-4 py-2">Fecha de Creación</th>
                <th class="border border-gray-300 px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)


                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->user->name ?? 'N/A' }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->nombre }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->descripcion }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->estado }}</td>

                    <td class="border border-gray-300 px-4 py-2">{{ $project->created_at->format('d-m-Y') }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $project->created_at->format('d-m-Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
