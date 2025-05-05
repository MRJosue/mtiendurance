<div 


    x-data="{ elementos: @js($elementos) }"
    class="relative border border-gray-300 bg-white h-[500px] w-full rounded">

    <template x-for="el in elementos" :key="el.id">
        <div 
            x-bind:style="`left: ${el.posicion_x}px; top: ${el.posicion_y}px; width: ${el.ancho}px; height: ${el.alto}px;`"
            class="absolute bg-blue-100 border border-blue-500 text-xs text-center text-blue-900 font-semibold flex flex-col items-center justify-center rounded shadow p-1"
        >
            <span x-text="el.tipo.toUpperCase()"></span>
            <span x-show="el.caracteristica_nombre" class="text-[10px] mt-1" x-text="el.caracteristica_nombre"></span>
        </div>
    </template>


</div>
