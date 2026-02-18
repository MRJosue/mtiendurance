<div class="container mx-auto p-6">
    <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Respaldo completo de Base de Datos</h2>
                <p class="text-sm text-gray-600">
                    Genera un archivo <span class="font-medium">.sql.gz</span> con estructura y datos.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button
                    type="button"
                    wire:click="downloadBackup"
                    wire:loading.attr="disabled"
                    wire:target="downloadBackup"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="downloadBackup">Generar y descargar</span>
                    <span wire:loading wire:target="downloadBackup">Generando...</span>
                </button>
            </div>
        </div>

        <div class="mt-4 text-xs text-gray-500">
            <ul class="list-disc list-inside space-y-1">
                <li>Requiere <span class="font-medium">mysqldump</span> y <span class="font-medium">gzip</span> en el servidor.</li>
                <li>Se elimina el archivo del servidor después de enviarse.</li>
                <li>Respaldo ejecutado con <span class="font-medium">--single-transaction</span> para minimizar bloqueos (InnoDB).</li>
            </ul>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('download-backup', (e) => {
        const url = e.detail?.url;
        if (url) window.location.href = url;
    });
});
</script>
</div>
