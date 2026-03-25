<?php

use App\Http\Controllers\DatabaseBackupController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('admin/databasebackup', [DatabaseBackupController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.databasebackup');

Route::get('/admin/db-backup/download', function () {
    if (! auth()->check() || (! auth()->user()->hasRole('admin') && ! auth()->user()->can('db.backup'))) {
        abort(403);
    }

    $path = session('db_backup_path');
    $name = session('db_backup_name');

    if (! $path || ! $name || ! Storage::disk('local')->exists($path)) {
        abort(404);
    }

    session()->forget(['db_backup_path', 'db_backup_name']);

    return Storage::disk('local')->download($path, $name)->deleteFileAfterSend(true);
})->name('admin.db-backup.download');
