<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'event' => ['nullable', 'in:created,updated,deleted'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $auditLogs = AuditLog::query()
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_name', 'ilike', "%{$search}%")
                        ->orWhere('user_email', 'ilike', "%{$search}%")
                        ->orWhere('auditable_type', 'ilike', "%{$search}%")
                        ->orWhere('auditable_id', 'ilike', "%{$search}%");
                });
            })
            ->when($validated['event'] ?? null, function ($query, $event) {
                $query->where('event', $event);
            })
            ->when($validated['date_from'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($validated['date_to'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('audit-logs.index', compact('auditLogs'));
    }

    public function show(AuditLog $auditLog): View
    {
        return view('audit-logs.show', compact('auditLog'));
    }
}