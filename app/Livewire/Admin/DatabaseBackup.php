<?php

namespace App\Livewire\Admin;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseBackup extends Component
{
    public bool $working = false;

public function downloadBackup()
{
    if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->can('db.backup'))) {
        abort(403);
    }

    $this->working = true;

    try {
        $connection = config('database.default');
        $cfg = config("database.connections.$connection");

        if (($cfg['driver'] ?? null) !== 'mysql') {
            throw new \Exception('Solo se soporta MySQL.');
        }

        $dbHost = $cfg['host'] ?? '127.0.0.1';
        $dbPort = $cfg['port'] ?? '3306';
        $dbName = $cfg['database'] ?? '';
        $dbUser = $cfg['username'] ?? '';
        $dbPass = $cfg['password'] ?? '';

        if (!$dbName || !$dbUser) {
            throw new \Exception('Configuración de base de datos incompleta.');
        }

        // Detectar mysqldump
        $finder = new \Symfony\Component\Process\ExecutableFinder();
        $mysqldump = $finder->find('mysqldump');

        if (!$mysqldump && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $possible = glob('C:\laragon\bin\mysql\*\bin\mysqldump.exe');
            if ($possible && isset($possible[0])) {
                $mysqldump = $possible[0];
            }
        }

        if (!$mysqldump) {
            throw new \Exception('mysqldump no encontrado en el sistema.');
        }

        // Crear carpeta
        $dir = 'backups';
        Storage::disk('local')->makeDirectory($dir);

        $stamp = now()->format('Ymd_His');
        $sqlName = "backup_{$dbName}_{$stamp}.sql";
        $gzName  = $sqlName . '.gz';

        $sqlRelative = $dir.'/'.$sqlName;
        $gzRelative  = $dir.'/'.$gzName;

        $sqlPath = Storage::disk('local')->path($sqlRelative);
        $gzPath  = Storage::disk('local')->path($gzRelative);

        // Ejecutar mysqldump (a .sql)
        $process = new Process([
            $mysqldump,
            "--host={$dbHost}",
            "--port={$dbPort}",
            "--user={$dbUser}",
            "--single-transaction",
            "--quick",
            "--routines",
            "--triggers",
            "--events",
            "--hex-blob",
            "--default-character-set=utf8mb4",
            "--result-file={$sqlPath}",
            $dbName,
        ]);

        $process->setTimeout(300);
        $process->setEnv(['MYSQL_PWD' => $dbPass]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Comprimir con PHP
        $raw = file_get_contents($sqlPath);
        file_put_contents($gzPath, gzencode($raw, 9));
        @unlink($sqlPath);

        // Guardar en sesión para descargar vía ruta
        session([
            'db_backup_path' => $gzRelative,
            'db_backup_name' => $gzName,
        ]);

        $this->working = false;

        // Disparar descarga "real" via navegador
        $this->dispatch('download-backup', url: route('admin.db-backup.download'));

        $this->dispatch('toast', message: 'Respaldo generado. Iniciando descarga...', type: 'success');
        return null;

    } catch (\Throwable $e) {
        $this->working = false;
        \Log::error('Error backup BD: '.$e->getMessage());

        $this->dispatch('toast',
            message: 'Error al generar respaldo: '.$e->getMessage(),
            type: 'error'
        );

        return null;
    }
}


    public function render()
    {
        return view('livewire.admin.database-backup');
    }
}