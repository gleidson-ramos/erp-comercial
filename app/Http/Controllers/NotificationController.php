<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allowedTypes = $user->accessibleNotificationTypes();

        $filters = $request->validate([
            'type' => [
                'nullable',
                'in:ESTOQUE_BAIXO,CONTA_VENCIDA,CONTA_VENCENDO,COMPRA_ATRASADA',
            ],
            'status' => [
                'nullable',
                'in:NAO_LIDAS,LIDAS,TODAS',
            ],
        ]);

        $query = $user->notifications()->latest();

        $this->applyAccessibleTypes($query, $allowedTypes);

        if (!empty($filters['type'])) {
            $query->whereRaw(
                "(data::jsonb ->> 'type') = ?",
                [$filters['type']]
            );
        }

        if (($filters['status'] ?? 'TODAS') === 'NAO_LIDAS') {
            $query->whereNull('read_at');
        } elseif (($filters['status'] ?? 'TODAS') === 'LIDAS') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->paginate(15)
            ->withQueryString();

        $unreadQuery = $user->unreadNotifications();

        $this->applyAccessibleTypes($unreadQuery, $allowedTypes);

        return view('notifications.index', [
            'notifications' => $notifications,
            'filters' => array_merge([
                'type' => '',
                'status' => 'TODAS',
            ], $filters),
            'unreadCount' => $unreadQuery->count(),
        ]);
    }

    public function markAsRead(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allowedTypes = $user->accessibleNotificationTypes();

        $query = $user->notifications()
            ->where('id', $id);

        $this->applyAccessibleTypes($query, $allowedTypes);

        $notification = $query->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with(
            'success',
            'Notificação marcada como lida.'
        );
    }

    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allowedTypes = $user->accessibleNotificationTypes();

        if (!empty($allowedTypes)) {
            $query = $user->unreadNotifications();

            $this->applyAccessibleTypes($query, $allowedTypes);

            $query->update([
                'read_at' => now(),
            ]);
        }

        return back()->with(
            'success',
            'Notificações das áreas autorizadas foram marcadas como lidas.'
        );
    }

    /**
     * Restringe a consulta aos tipos de notificação autorizados.
     */
    private function applyAccessibleTypes($query, array $allowedTypes): void
    {
        if (empty($allowedTypes)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn(
            DB::raw("(data::jsonb ->> 'type')"),
            $allowedTypes
        );
    }
}