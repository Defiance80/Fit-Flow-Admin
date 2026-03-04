<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Validator;
use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Http;
use App\Helpers\FirebaseHelper;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function index()
    {
        // Get authenticated admin user
        $authUser = Auth::user();
        
        // Fetch courses - only show courses that belong to the admin
        $coursesQuery = \App\Models\Course\Course::select('id', 'title');
        
        // If user is admin, filter courses by admin's user_id
        if ($authUser && $authUser->hasRole(config('constants.SYSTEM_ROLES.ADMIN'))) {
            $coursesQuery->where('user_id', $authUser->id);
        }
        
        $courses = $coursesQuery->get();
        
        $instructors = \App\Models\Instructor::with('user:id,name') // eager load user
            ->where('status', 'approved') // Only approved instructors
            ->select('id', 'user_id')
            ->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->user->name ?? 'Unknown',
                ];
            });
        return view('notifications.index', [
            'type_menu'   => 'notifications',
            'courses'     => $courses,
            'instructors' => $instructors,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'message'   => 'required|string|max:250',
            'type'      => 'required|in:default,course,instructor,url',
            'type_id'   => 'required_if:type,course,instructor|nullable|integer',
            'type_link' => 'required_if:type,url|nullable|url',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:2048',
        ]);
    
        // Handle file upload if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
        }
    
        // Save notification in DB
        $notification = Notification::create([
            'title'     => $request->title,
            'message'   => $request->message,
            'type'      => $request->type,
            'type_id'   => $request->type_id ?? 0,
            'type_link' => $request->type_link,
            'image'     => $imagePath,
            'date_sent' => now(),
        ]);
    
            /**
             * 🔔 Send push notifications
             */
            $tokens = UserFcmToken::select('fcm_token', 'platform_type')->get();

            foreach ($tokens as $token) {
                $fcmMsg = [
                    'title' => $notification->title,
                    'body'  => $notification->message,
                    'type'  => $notification->type,
                    'id'    => (string) $notification->type_id,
                    'link'  => $notification->type_link,
                ];
        
                // Map DB value to helper expected format
                $platform = match (strtolower($token->platform_type)) {
                    'ios'     => 'ios',
                    'android' => 'android',
                    default   => 'web', // null or unknown → treat as web
                };

                FirebaseHelper::send($platform, $token->fcm_token, $fcmMsg, [
                    'title' => $notification->title,
                    'body'  => $notification->message,
                ]);
                
            }
    
        return ResponseService::successResponse("Notification created & sent successfully");
    }


    public function show(Request $request, $id)
    {
        //ResponseService::noPermissionThenSendJson('taxes-list');

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $search = $request->input('search');
        $showDeleted = $request->input('show_deleted');

        $sql = Notification::query()
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('message', 'LIKE', "%$search%");
                });
            });

        $sql->orderBy($sort, $order);

        $total = $sql->count();
        $result = $sql->skip($offset)->take($limit)->get();

        // Fetch courses and instructors for display
        $coursesMap = \App\Models\Course\Course::select('id', 'title')->get()->keyBy('id');
        $instructorsMap = \App\Models\Instructor::with('user:id,name')
            ->select('id', 'user_id')
            ->get()
            ->keyBy('id');

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = 1;

        foreach ($result as $row) {
            if ($showDeleted) {
                $operate = BootstrapTableService::restoreButton(route('notifications.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('notifications.trash', $row->id));
            } else {
                $operate = BootstrapTableService::deleteButton(route('notifications.destroy', $row->id));
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            
            // Add formatted type_id_display based on type
            $typeIdDisplay = '-';
            if ($row->type === 'course' && $row->type_id && isset($coursesMap[$row->type_id])) {
                $typeIdDisplay = $coursesMap[$row->type_id]->title . ' (ID: ' . $row->type_id . ')';
            } elseif ($row->type === 'instructor' && $row->type_id && isset($instructorsMap[$row->type_id])) {
                $instructor = $instructorsMap[$row->type_id];
                $typeIdDisplay = ($instructor->user->name ?? 'Unknown') . ' (ID: ' . $row->type_id . ')';
            } elseif ($row->type === 'url' && $row->type_link) {
                $typeIdDisplay = $row->type_link;
            } elseif ($row->type === 'default') {
                $typeIdDisplay = 'N/A';
            } elseif ($row->type_id) {
                $typeIdDisplay = 'ID: ' . $row->type_id;
            }
            
            $tempRow['type_id_display'] = $typeIdDisplay;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return ResponseService::successResponse("Notification deleted successfully");
    }
}
