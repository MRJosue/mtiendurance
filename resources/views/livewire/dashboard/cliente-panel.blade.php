<div>
    
    @can('dashboardclientepreproyectos')
        @livewire('preproyectos.manage-preprojects')
    @endcan
    @can('dashboardclienteproyectos')
       @livewire('proyectos.manage-projects')
    @endcan

    @can('dashboardclientepedidos')
    @livewire('dashboard.cliente-panel.pedidos')
    @endcan

</div>
 