<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\User;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderCourse::with(['order.user', 'course.user'])
            ->whereHas('order', function($q) {
                $q->where('status', 'completed');
            })
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('instructor_id')) {
            $query->where(function($q) use ($request) {
                // Check if instructor is the course owner
                $q->whereHas('course', function($courseQuery) use ($request) {
                    $courseQuery->where('user_id', $request->instructor_id);
                })
                // OR check if instructor is in the course_instructors pivot table
                ->orWhereHas('course.instructors', function($instructorQuery) use ($request) {
                    $instructorQuery->where('user_id', $request->instructor_id);
                });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order.user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('course', function($courseQuery) use ($search) {
                    $courseQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        $enrollments = $query->paginate(15);

        // Get summary statistics
        $stats = [
            'total_enrollments' => OrderCourse::whereHas('order', function($q) {
                $q->where('status', 'completed');
            })->count(),
            'today_enrollments' => OrderCourse::whereHas('order', function($q) {
                $q->where('status', 'completed');
            })->whereDate('created_at', today())->count(),
            'monthly_enrollments' => OrderCourse::whereHas('order', function($q) {
                $q->where('status', 'completed');
            })->whereMonth('created_at', now()->month)->count(),
            'active_students' => User::whereHas('orders.orderCourses', function($q) {
                $q->whereHas('order', function($orderQuery) {
                    $orderQuery->where('status', 'completed');
                });
            })->distinct()->count(),
        ];

        // Get courses and instructors for filters
        $courses = Course::select('id', 'title')->get();
        $instructors = User::role('instructor')->select('id', 'name')->get();

        return view('pages.admin.enrollments.index', compact('enrollments', 'stats', 'courses', 'instructors'), ['type_menu' => 'enrollments']);
    }

    public function show($id)
    {
        $enrollment = OrderCourse::with([
            'order.user.orders.orderCourses', 
            'course.user',
            'course.chapters.lectures'
        ])->findOrFail($id);

        return view('pages.admin.enrollments.show', compact('enrollment'), ['type_menu' => 'enrollments']);
    }

    public function getDashboardData()
    {
        $data = [
            'recent_enrollments' => OrderCourse::with(['order.user', 'course'])
                ->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'top_courses' => OrderCourse::select('course_id', DB::raw('count(*) as enrollments'))
                ->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                })
                ->with('course:id,title')
                ->groupBy('course_id')
                ->orderBy('enrollments', 'desc')
                ->limit(5)
                ->get(),
            'monthly_enrollments' => OrderCourse::whereHas('order', function($q) {
                $q->where('status', 'completed');
            })
            ->whereMonth('created_at', now()->month)
            ->count(),
            'daily_enrollments' => OrderCourse::whereHas('order', function($q) {
                $q->where('status', 'completed');
            })
            ->whereDate('created_at', today())
            ->count()
        ];

        return response()->json($data);
    }
}
