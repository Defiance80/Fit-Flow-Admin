<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Course\Course;
use App\Models\Instructor;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * Display ratings listing with filters
     */
    public function index(Request $request)
    {
        $query = Rating::with(['user', 'rateable']);

        // Filter by rating type (course or instructor)
        if ($request->has('type') && $request->type != '') {
            if ($request->type === 'course') {
                $query->where('rateable_type', 'App\\Models\\Course\\Course');
            } elseif ($request->type === 'instructor') {
                $query->where('rateable_type', 'App\\Models\\Instructor');
            }
        }

        // Filter by rating value
        if ($request->has('rating') && $request->rating != '') {
            $query->where('rating', $request->rating);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('review', 'like', "%{$search}%")
                  ->orWhere(function($subQuery) use ($search) {
                      // Search in courses using direct table join
                      $subQuery->where('rateable_type', 'App\\Models\\Course\\Course')
                               ->whereExists(function($existsQuery) use ($search) {
                                   $existsQuery->select(DB::raw(1))
                                              ->from('courses')
                                              ->whereColumn('courses.id', 'ratings.rateable_id')
                                              ->where('courses.title', 'like', "%{$search}%")
                                              ->whereNull('courses.deleted_at');
                               });
                  })->orWhere(function($subQuery) use ($search) {
                      // Search in instructors using direct table joins
                      $subQuery->where('rateable_type', 'App\\Models\\Instructor')
                               ->whereExists(function($existsQuery) use ($search) {
                                   $existsQuery->select(DB::raw(1))
                                              ->from('instructors')
                                              ->join('users', 'instructors.user_id', '=', 'users.id')
                                              ->whereColumn('instructors.id', 'ratings.rateable_id')
                                              ->where('users.name', 'like', "%{$search}%")
                                              ->whereNull('instructors.deleted_at')
                                              ->whereNull('users.deleted_at');
                               });
                  });
            });
        }

        $ratings = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = $this->getStats();

        return view('admin.ratings.index', [
            'type_menu' => 'ratings',
            'ratings' => $ratings,
            'stats' => $stats,
            'filters' => [
                'type' => $request->type,
                'rating' => $request->rating,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'search' => $request->search
            ]
        ]);
    }

    /**
     * Show rating details
     */
    public function show($id)
    {
        $rating = Rating::with(['user'])
                       ->findOrFail($id);
        
        // Load rateable relationship safely
        if ($rating->rateable_type && $rating->rateable_id) {
            try {
                $rating->load('rateable');
            } catch (\Exception $e) {
                // Handle case where rateable no longer exists
                $rating->rateable = null;
            }
        }

        return view('admin.ratings.show', [
            'type_menu' => 'ratings',
            'rating' => $rating
        ]);
    }

    /**
     * Delete a rating
     */
    public function destroy($id)
    {
        try {
            $rating = Rating::findOrFail($id);
            $rating->delete();

            return ResponseService::successResponse('Rating deleted successfully');
        } catch (\Exception $e) {
            return ResponseService::errorResponse('Failed to delete rating: ' . $e->getMessage());
        }
    }

    /**
     * Get rating statistics
     */
    public function getStats()
    {
        $stats = [
            'total_ratings' => Rating::count(),
            'course_ratings' => Rating::where('rateable_type', 'App\\Models\\Course\\Course')->count(),
            'instructor_ratings' => Rating::where('rateable_type', 'App\\Models\\Instructor')->count(),
            'average_rating' => round(Rating::avg('rating'), 2),
            'rating_breakdown' => [
                '5_stars' => Rating::where('rating', 5)->count(),
                '4_stars' => Rating::where('rating', 4)->count(),
                '3_stars' => Rating::where('rating', 3)->count(),
                '2_stars' => Rating::where('rating', 2)->count(),
                '1_star' => Rating::where('rating', 1)->count(),
            ],
            'recent_ratings' => Rating::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'monthly_ratings' => $this->getMonthlyRatings()
        ];

        return $stats;
    }

    /**
     * Get monthly ratings data for charts
     */
    private function getMonthlyRatings()
    {
        return Rating::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as count,
                AVG(rating) as avg_rating
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get ratings data for dashboard
     */
    public function getDashboardData()
    {
        $stats = $this->getStats();
        
        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }
}
