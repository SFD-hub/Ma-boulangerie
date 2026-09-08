<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ActivityLogRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private const PER_PAGE = 15;

    public function panel(Request $request): JsonResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        $userId        = auth()->id();
        $categorie     = $request->get('categorie', 'toutes');

        $query = ActivityLog::where('boulangerie_id', $boulangerieId)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId)->whereNotNull('dismissed_at'))
            ->with('user')
            ->orderByDesc('created_at');

        if ($categorie && $categorie !== 'toutes') {
            $query->where('categorie', $categorie);
        }

        $activites = $query->paginate(self::PER_PAGE)->withQueryString();

        $readIds = ActivityLogRead::where('user_id', $userId)
            ->whereIn('activity_log_id', $activites->pluck('id'))
            ->whereNotNull('read_at')
            ->pluck('activity_log_id')
            ->all();

        $html = view('partials.notifications-list', [
            'activites' => $activites,
            'readIds'   => $readIds,
            'categorie' => $categorie,
        ])->render();

        return response()->json([
            'html'        => $html,
            'unreadCount' => $this->unreadCount($boulangerieId, $userId),
        ]);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->unreadCount(auth()->user()->boulangerie_id, auth()->id()),
        ]);
    }

    public function lire(ActivityLog $activityLog): JsonResponse
    {
        abort_if($activityLog->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        ActivityLogRead::updateOrCreate(
            ['activity_log_id' => $activityLog->id, 'user_id' => auth()->id()],
            ['read_at' => now()]
        );

        return response()->json(['ok' => true, 'unreadCount' => $this->unreadCount(auth()->user()->boulangerie_id, auth()->id())]);
    }

    public function masquer(ActivityLog $activityLog): JsonResponse
    {
        abort_if($activityLog->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        ActivityLogRead::updateOrCreate(
            ['activity_log_id' => $activityLog->id, 'user_id' => auth()->id()],
            ['dismissed_at' => now(), 'read_at' => now()]
        );

        return response()->json(['ok' => true, 'unreadCount' => $this->unreadCount(auth()->user()->boulangerie_id, auth()->id())]);
    }

    public function toutLire(): JsonResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        $userId        = auth()->id();
        $now           = now();

        $unreadIds = ActivityLog::where('boulangerie_id', $boulangerieId)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId)->whereNotNull('read_at'))
            ->pluck('id');

        foreach ($unreadIds as $id) {
            ActivityLogRead::updateOrCreate(
                ['activity_log_id' => $id, 'user_id' => $userId],
                ['read_at' => $now]
            );
        }

        return response()->json(['ok' => true, 'unreadCount' => 0]);
    }

    public function viderLues(): JsonResponse
    {
        ActivityLogRead::where('user_id', auth()->id())
            ->whereNotNull('read_at')
            ->whereNull('dismissed_at')
            ->update(['dismissed_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function unreadCount(?int $boulangerieId, int $userId): int
    {
        if (! $boulangerieId) {
            return 0;
        }

        return ActivityLog::where('boulangerie_id', $boulangerieId)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $userId)->whereNotNull('read_at'))
            ->count();
    }
}
