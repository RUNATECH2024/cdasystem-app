<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;

class AdminAuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $auditorias = AdminAuditLog::query()
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    $query->where('actor_usuario', 'like', "%{$termino}%")
                        ->orWhere('accion', 'like', "%{$termino}%")
                        ->orWhere('objetivo', 'like', "%{$termino}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id_audit')
            ->paginate(30)
            ->withQueryString();

        return view('admin.auditoria.index', [
            'auditorias' => $auditorias,
            'q' => $filtros['q'] ?? '',
        ]);
    }
}
