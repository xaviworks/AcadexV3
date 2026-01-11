<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /**
     * Get all active tutorials for a specific role
     *
     * @param string $role
     * @return JsonResponse
     */
    public function getTutorialsByRole(string $role): JsonResponse
    {
        $tutorials = Tutorial::with(['steps', 'dataCheck'])
            ->active()
            ->forRole($role)
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function ($tutorial) {
                return $tutorial->toJavaScriptFormat();
            });

        return response()->json([
            'success' => true,
            'tutorials' => $tutorials,
        ]);
    }

    /**
     * Get a specific tutorial by role and page identifier
     *
     * @param string $role
     * @param string $pageId
     * @return JsonResponse
     */
    public function getTutorialByPage(string $role, string $pageId): JsonResponse
    {
        $tutorial = Tutorial::with(['steps', 'dataCheck'])
            ->active()
            ->forRole($role)
            ->forPage($pageId)
            ->orderBy('priority', 'desc')
            ->first();

        if (!$tutorial) {
            return response()->json([
                'success' => false,
                'message' => 'Tutorial not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'tutorial' => $tutorial->toJavaScriptFormat(),
        ]);
    }

    /**
     * Get tutorial statistics (optional - for analytics)
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_tutorials' => Tutorial::count(),
            'active_tutorials' => Tutorial::active()->count(),
            'by_role' => Tutorial::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
