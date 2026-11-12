<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class AdminRespaldoController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('local');
        $respaldos = collect($disk->files('backups'))
            ->filter(fn ($path) => str_ends_with($path, '.sql'))
            ->map(fn ($path) => [
                'nombre' => basename($path),
                'bytes' => $disk->size($path),
                'fecha' => $disk->lastModified($path),
            ])
            ->sortByDesc('fecha')
            ->values();

        return view('admin.respaldos.index', compact('respaldos'));
    }

    public function crear()
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'pgsql') {
            return back()->with('error', 'La creación de respaldos está configurada para PostgreSQL.');
        }

        $disk = Storage::disk('local');
        $disk->makeDirectory('backups');

        $nombre = 'cda_backup_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.sql';
        $rutaRelativa = 'backups/'.$nombre;
        $rutaCompleta = $disk->path($rutaRelativa);
        $dumpBinary = config('database.connections.pgsql.dump_binary', 'pg_dump');

        if ($dumpBinary === 'pg_dump' && PHP_OS_FAMILY === 'Windows') {
            $version = (int) DB::scalar('SHOW server_version_num');
            $major = intdiv($version, 10000);
            $windowsBinary = 'C:\\Program Files\\PostgreSQL\\'.$major.'\\bin\\pg_dump.exe';

            if (is_file($windowsBinary)) {
                $dumpBinary = $windowsBinary;
            }
        }

        $command = [
            $dumpBinary,
            '--no-password',
            '--no-owner',
            '--no-acl',
            '--format=plain',
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? '5432'),
            '--username='.($connection['username'] ?? ''),
            '--dbname='.($connection['database'] ?? ''),
            '--file',
            $rutaCompleta,
        ];

        try {
            $process = new Process($command, base_path(), [
                'PGPASSWORD' => (string) ($connection['password'] ?? ''),
                'PGSSLMODE' => (string) ($connection['sslmode'] ?? 'prefer'),
            ], null, 300);
            $process->run();

            if (! $process->isSuccessful() || ! $disk->exists($rutaRelativa) || $disk->size($rutaRelativa) === 0) {
                $disk->delete($rutaRelativa);
                Log::error('Falló la creación del respaldo PostgreSQL.', [
                    'exit_code' => $process->getExitCode(),
                    'stderr' => $process->getErrorOutput(),
                    'stdout' => $process->getOutput(),
                    'binary' => $dumpBinary,
                    'file' => $rutaRelativa,
                ]);

                return back()->with('error', 'No se pudo crear el respaldo. Verifica pg_dump y la conexión a la base de datos.');
            }
        } catch (Throwable $exception) {
            $disk->delete($rutaRelativa);
            Log::error('No se pudo ejecutar pg_dump.', ['error' => $exception->getMessage()]);

            return back()->with('error', 'No se pudo ejecutar pg_dump. Configura PG_DUMP_BINARY y verifica que PostgreSQL esté instalado.');
        }

        return redirect()->route('admin.respaldos.index')->with('success', 'Respaldo creado: '.$nombre);
    }

    public function descargar(string $nombre)
    {
        abort_if($nombre !== basename($nombre) || ! str_ends_with($nombre, '.sql'), 404);

        $path = Storage::disk('local')->path('backups/'.$nombre);
        abort_unless(is_file($path), 404);

        return response()->download($path, $nombre, ['Content-Type' => 'application/sql']);
    }
}
