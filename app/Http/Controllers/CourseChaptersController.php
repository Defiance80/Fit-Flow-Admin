<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Course\Course;
use App\Services\HelperService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;
use App\Models\Course\CourseChapter\CourseChapter;
use App\Http\Requests\CourseChapter\StoreCurriculumRequest;
use App\Models\Course\CourseChapter\Quiz\CourseChapterQuiz;
use App\Models\Course\CourseChapter\Lecture\CourseChapterLecture;
use App\Http\Requests\CourseChapter\UpdateLectureCurriculumRequest;
use App\Models\Course\CourseChapter\Resource\CourseChapterResource;
use App\Models\Course\CourseChapter\Assignment\CourseChapterAssignment;
use App\Models\Course\CourseChapter\Quiz\CourseChapterQuizQuestion;
use App\Models\Course\CourseChapter\Quiz\QuizOption;
use App\Models\Course\CourseChapter\Quiz\QuizQuestion;
use App\Models\Course\CourseChapter\Quiz\QuizResource;
use App\Models\Course\CourseChapter\Lecture\LectureResource;
use App\Models\Course\CourseChapter\Assignment\AssignmentResource;

class CourseChaptersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noAnyPermissionThenSendJson(array(
            'course-chapters-list',
            'course-chapters-create',
            'course-chapters-edit',
            'course-chapters-delete'
        ));

        // Get only active courses created by Admin users
        $adminUserIds = \App\Models\User::role(config('constants.SYSTEM_ROLES.ADMIN'))->pluck('id');
        
        $courses = Course::select('id','title')
            ->where('is_active', 1)
            ->whereIn('user_id', $adminUserIds)
            ->orderBy('title', 'asc')
            ->get();
        $instructors = \App\Models\User::role(['Instructor','Admin'])->select('id','name')->get();
        $coursesFilter = collect();
        return view('courses.chapters.index',compact('courses','instructors','coursesFilter'),['type_menu' => 'course-chapters']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-create');
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id,deleted_at,NULL',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }
        try {
            $data = $validator->validated(); // get validated data
            $course = Course::find($data['course_id']);
            
            $authUser = Auth::user();
            $isAdmin = $authUser->hasRole('admin');
            $isCourseOwner = $course->user_id === $authUser->id;
            
            // Check if user is a team member of the course instructor OR course owner is team member of auth instructor
            $isTeamMember = false;
            if (!$isCourseOwner && !$isAdmin) {
                // Case 1: Auth user is a team member of the course owner's instructor
                $courseOwnerInstructor = \App\Models\Instructor::where('user_id', $course->user_id)->first();
                
                if ($courseOwnerInstructor) {
                    $isTeamMember = \App\Models\TeamMember::where('instructor_id', $courseOwnerInstructor->id)
                        ->where('user_id', $authUser->id)
                        ->where('status', 'approved')
                        ->exists();
                }
                
                // Case 2: Auth user is an instructor and course owner is their team member
                if (!$isTeamMember) {
                    $authInstructor = \App\Models\Instructor::where('user_id', $authUser->id)->first();
                    
                    if ($authInstructor) {
                        $isTeamMember = \App\Models\TeamMember::where('instructor_id', $authInstructor->id)
                            ->where('user_id', $course->user_id)
                            ->where('status', 'approved')
                            ->exists();
                    }
                }
            }

            // ✅ Authorization check: Admin, Course Owner, or Approved Team Member
            if (!$isAdmin && !$isCourseOwner && !$isTeamMember) {
                return ResponseService::validationError('You are not authorized to add a chapter to this course');
            }
            
            $data['user_id'] = Auth::user()->id; // get user id
            $data['is_active'] = 1; // auto active on create
            CourseChapter::create($data); // create chapter
            return ResponseService::successResponse('Chapter created successfully'); // return success response
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage()); // return error response
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-list');

        // Get request data
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $showDeleted = request('show_deleted');
        $filterInstructorId = request('instructor_id');
        $filterCourseId = request('course_id');

        // Get course chapters - show all chapters, filter only if filters are applied
        $sql = CourseChapter::with(['course:id,title,user_id'])
            ->whereHas('course', function($q) use ($filterInstructorId, $filterCourseId){
                // Only apply filters if they are provided
                if (!empty($filterInstructorId)) {
                    $q->where('user_id', $filterInstructorId);
                }
                if (!empty($filterCourseId)) {
                    $q->where('id', $filterCourseId);
                }
                // If no filters, show all courses (no where clause)
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'LIKE', "%$search%")
                        ->orWhere('title', 'LIKE', "%$search%")
                        ->orWhereHas('course', function ($query) use ($search) {
                            $query->where('title', 'LIKE', "%$search%");
                        });
                });
            })
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
            });

        // Get total count of course chapters
        $total = $sql->count();

        // Order and limit course chapters
        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        // Get bulk data
        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = 1;

        // Loop through course chapters
        foreach ($res as $row) {
            if ($showDeleted) {
                $operate = BootstrapTableService::restoreButton(route('course-chapters.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('course-chapters.trash', $row->id)); // permanent delete
            } else {
                $operate = BootstrapTableService::button('fas fa-plus',route('course-chapters.curriculum.index', $row->id), ['btn-info'],array('title' => 'Add Curriculum'));
                $operate .= BootstrapTableService::editButton(route('course-chapters.update', $row->id),true);
                $operate .= BootstrapTableService::deleteButton(route('course-chapters.destroy', $row->id)); // soft delete
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-edit');

        $chapterId = $id ?? $request->id;

        $idValidator = Validator::make(['id' => $chapterId], [
            'id' => 'required|exists:course_chapters,id',
        ]);
        if ($idValidator->fails()) {
            return ResponseService::validationError($idValidator->errors()->first());
        }

        $validator = Validator::make($request->all(), [
            'course_id'     => 'nullable|exists:courses,id,deleted_at,NULL',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $data = $validator->validated();

            // Normalize is_active checkbox (auto true when checked, else false)
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // Update course_id if provided
            if ($request->filled('course_id')) {
                $data['course_id'] = $request->course_id;
            }

            $chapter = CourseChapter::with('course')->findOrFail($chapterId);

            $authUser = Auth::user();
            $isAdmin = $authUser->hasRole('admin');
            $isCourseOwner = $chapter->course->user_id === $authUser->id;
            
            // Check if user is a team member of the course instructor OR course owner is team member of auth instructor
            $isTeamMember = false;
            if (!$isCourseOwner && !$isAdmin) {
                // Case 1: Auth user is a team member of the course owner's instructor
                $courseOwnerInstructor = \App\Models\Instructor::where('user_id', $chapter->course->user_id)->first();
                
                if ($courseOwnerInstructor) {
                    $isTeamMember = \App\Models\TeamMember::where('instructor_id', $courseOwnerInstructor->id)
                        ->where('user_id', $authUser->id)
                        ->where('status', 'approved')
                        ->exists();
                }
                
                // Case 2: Auth user is an instructor and course owner is their team member
                if (!$isTeamMember) {
                    $authInstructor = \App\Models\Instructor::where('user_id', $authUser->id)->first();
                    
                    if ($authInstructor) {
                        $isTeamMember = \App\Models\TeamMember::where('instructor_id', $authInstructor->id)
                            ->where('user_id', $course->user_id)
                            ->where('status', 'approved')
                            ->exists();
                    }
                }
            }

            // ✅ Authorization check: Admin, Course Owner, or Approved Team Member
            if (!$isAdmin && !$isCourseOwner && !$isTeamMember) {
                return ApiResponseService::validationError('You are not authorized to view chapters of this course');
            }

            $chapter->update($data);

            return ResponseService::successResponse('Chapter updated successfully');
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-delete');
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:course_chapters,id',
            ]);
            if ($validator->fails()) {
                return ResponseService::validationError($validator->errors()->first());
            }
            $chapter = CourseChapter::find($id);
            $chapter->delete();
            return ResponseService::successResponse('Chapter deleted successfully'); // return success response
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage()); // return error response
        }
    }

    public function curriculumIndex($id)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-list');
        $chapter = CourseChapter::where('id',$id)->with('course:id,title')->first();
        $allowedFileTypes = HelperService::getAllowedFileTypes();

        return view('courses.chapters.curriculums.index', compact('chapter', 'allowedFileTypes'), ['type_menu' => 'course-chapters']);
    }

    public function curriculumStore(StoreCurriculumRequest $request, $chapterId = null)
    {
        $chapterId = $chapterId ?? request('chapter_id');
        $idValidator = Validator::make(['id' => $chapterId], [
            'id' => 'required|exists:course_chapters,id',
        ]);
        if ($idValidator->fails()) {
            return ResponseService::validationError($idValidator->errors()->first());
        }

        try {
            DB::beginTransaction();
            $type = $request->type;

            // Validate type field
            if (!$type) {
                return ResponseService::validationError('The curriculum type is required.');
            }

            $validTypes = ['lecture', 'document', 'quiz', 'assignment'];
            if (!in_array($type, $validTypes)) {
                return ResponseService::validationError('The curriculum type must be one of: lecture, document, quiz, assignment.');
            }

            $curriculumData = null;

            switch ($type) {
                case 'lecture':
                    $curriculumData = HelperService::updateAndGetLectureData($request, $chapterId); // update or create lecture
                    if($request->resource_status == 1){
                        HelperService::getTypeResourceData($type, $request, $curriculumData); // store resource data
                    }
                    break;
                case 'document':
                    $curriculumData = HelperService::updateAndGetDocumentData($request, $chapterId); // update or create document
                    break;
                case 'quiz':
                    $curriculumData = HelperService::updateAndGetQuizData($request, $chapterId, $request->qa_required ?? 1); // update or create quiz
                    if($request->resource_status == 1){
                        HelperService::getTypeResourceData($type, $request, null, $curriculumData); // store resource data
                    }
                    break;
                case 'assignment':
                    $curriculumData = HelperService::updateAndGetAssignmentData($request, $chapterId); // update or create assignment
                    if($request->resource_status == 1){
                        HelperService::getTypeResourceData($type, $request, null, null, $curriculumData); // store resource data
                    }
                    break;
            }
            DB::commit();

            // Load resources relationship for curriculum types that support resources
            if ($curriculumData && in_array($type, ['lecture', 'quiz', 'assignment'])) {
                $curriculumData->load('resources');
            }

            // Prepare response data (remove resources from toArray to avoid confusion)
            $curriculumArray = $curriculumData ? $curriculumData->toArray() : null;
            if ($curriculumArray && isset($curriculumArray['resources'])) {
                unset($curriculumArray['resources']);
            }

            $responseData = [
                'curriculum' => $curriculumArray,
                'type' => $type,
                'chapter_id' => $chapterId
            ];

            return ResponseService::successResponse('Curriculum created successfully', $responseData);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse($e->getMessage()); // return error response
        }
    }

    public function getCurriculumDataList($chapterId = null){
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $showDeleted = request('show_deleted');
        $chapterId = $chapterId ?? request('chapter_id');

        // Get the chapter
        $chapter = CourseChapter::findOrFail($chapterId);
        
        // Get all curriculum data
        $allCurriculumData = $chapter->all_curriculum_data;
        
        // Apply search filter if provided - search across all fields
        if (!empty($search)) {
            $allCurriculumData = $allCurriculumData->filter(function ($curriculum) use ($search) {
                $searchLower = strtolower($search);
                // Search in title
                $titleMatch = stripos($curriculum['title'] ?? '', $search) !== false;
                // Search in type/curriculum_type
                $typeMatch = stripos($curriculum['curriculum_type'] ?? '', $search) !== false;
                // Search in level
                $levelMatch = stripos($curriculum['level'] ?? '', $search) !== false;
                // Search in course_type
                $courseTypeMatch = stripos($curriculum['course_type'] ?? '', $search) !== false;
                // Search in instructor
                $instructorMatch = stripos($curriculum['instructor'] ?? '', $search) !== false;
                // Search in duration (formatted)
                $durationMatch = stripos($curriculum['formatted_duration'] ?? '', $search) !== false;
                // Search in resources (Yes/No)
                $resourcesText = !empty($curriculum['resources']) ? 'yes' : 'no';
                $resourcesMatch = stripos($resourcesText, $searchLower) !== false;
                // Search in status (Active/Inactive)
                $statusText = ($curriculum['is_active'] ?? false) ? 'active' : 'inactive';
                $statusMatch = stripos($statusText, $searchLower) !== false;
                // Search in ID
                $idMatch = stripos((string)($curriculum['id'] ?? ''), $search) !== false;
                
                return $titleMatch || $typeMatch || $levelMatch || $courseTypeMatch 
                    || $instructorMatch || $durationMatch || $resourcesMatch 
                    || $statusMatch || $idMatch;
            });
        }
        
        // Get total count before pagination
        $total = $allCurriculumData->count();
        
        // Sort the data
        if ($sort === 'id') {
            $allCurriculumData = $order === 'ASC' 
                ? $allCurriculumData->sortBy('id') 
                : $allCurriculumData->sortByDesc('id');
        } elseif ($sort === 'title') {
            $allCurriculumData = $order === 'ASC' 
                ? $allCurriculumData->sortBy('title') 
                : $allCurriculumData->sortByDesc('title');
        } else {
            // Default sort by chapter_order
            $allCurriculumData = $allCurriculumData->sortBy('chapter_order');
        }
        
        // Apply pagination
        $paginatedData = $allCurriculumData->slice($offset, $limit)->values();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = $offset + 1; // Start numbering from offset + 1

        foreach ($paginatedData as $curriculum) {
            if ($showDeleted) {
                $operate = BootstrapTableService::restoreButton(route('course-chapters.curriculum.restore', array('id' => $curriculum['id'], 'type' => $curriculum['curriculum_type'])));
                $operate .= BootstrapTableService::trashButton(route('course-chapters.trash', array('id' => $curriculum['id'], 'type' => $curriculum['curriculum_type']))); // permanent delete
            } else {
                $operate = BootstrapTableService::editButton(route('course-chapters.curriculum.edit', array('id' => $curriculum['id'], 'type' => $curriculum['curriculum_type'])),false);
                $operate .= BootstrapTableService::deleteButton(route('course-chapters.curriculum.destroy', array('id' => $curriculum['id'], 'type' => $curriculum['curriculum_type']))); // soft delete
            }

            $tempRow['no'] = $no++;
            $tempRow['id'] = $curriculum['id'];
            $tempRow['title'] = $curriculum['title'];
            $tempRow['type'] = $curriculum['curriculum_type'];
            $tempRow['table_name'] = $curriculum['curriculum_type'];
            $tempRow['level'] = $curriculum['level'] ?? '';
            $tempRow['course_type'] = $curriculum['course_type'] ?? '';
            $tempRow['instructor'] = $curriculum['instructor'] ?? '';
            $tempRow['duration'] = $curriculum['formatted_duration'];
            $tempRow['status'] = $curriculum['is_active'] ? true : false;
            $tempRow['all_details'] = $curriculum;
            $tempRow['resources'] = !empty($curriculum['resources']) ? 1 : 0;
            $tempRow['particular_details_url'] = route('course-chapters.curriculum.particular-details', [$curriculum['id'], $curriculum['curriculum_type']]);
            $tempRow['update_status_url'] = route('course-chapters.curriculum.change-status', $curriculum['id']);
            $tempRow['restore_url'] = route('course-chapters.curriculum.restore', [$curriculum['id'], $curriculum['curriculum_type']]);
            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function changeCurriculumStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }
        try {
            $data = $validator->validated();
            switch ($data['type']) {
                case 'lecture':
                    $curriculum = CourseChapterLecture::findOrFail($id);
                    break;
                case 'quiz':
                    $curriculum = CourseChapterQuiz::findOrFail($id);
                    break;
                case 'resource':
                    $curriculum = CourseChapterResource::findOrFail($id);
                    break;
                case 'assignment':
                    $curriculum = CourseChapterAssignment::findOrFail($id);
                    break;
                default:
                    return ResponseService::errorResponse('Invalid curriculum type');
            }

            $curriculum->is_active = $data['status'];
            $curriculum->save();

            return ResponseService::successResponse('Status updated successfully');
        } catch(Exception $e){
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    public function getParticularCurriculumDetails($id = null, $type = null)
    {
        // Fallback to query parameters if route params are not passed
        $id = $id ?? request('id');
        $type = $type ?? request('type');

        if (!$id || !$type) {
            return ResponseService::errorResponse('Curriculum ID and type are required');
        }

        try {
            $curriculum = HelperService::getCurriculumData($type, $id);
            if ($curriculum) {
                return ResponseService::successResponse('Curriculum details fetched successfully', $curriculum);
            } else {
                return ResponseService::errorResponse('Curriculum not found');
            }
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function curriculumEdit($id, $type)
    {
        try {
            $curriculum = HelperService::getCurriculumData($type, $id);
            $allowedFileTypes = HelperService::getAllowedFileTypeCategories();
            if($curriculum){
                return view('courses.chapters.curriculums.edit', compact('curriculum', 'allowedFileTypes'), ['type_menu' => 'course-chapters']);
            }else{
                return ResponseService::errorResponse('Curriculum not found');
            }
        } catch(Exception $e){
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    public function quizQuestionsList($id = null){
        $id = $id ?? request('id');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $showDeleted = request('show_deleted');

        $sql = CourseChapterQuiz::where('id', $id)->with('questions.options')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'LIKE', "%$search%")
                        ->orWhereHas('questions', function ($query) use ($search) {
                            $query->where('question', 'LIKE', "%$search%")
                                ->orWhereHas('options', function ($query) use ($search) {
                                    $query->where('option', 'LIKE', "%$search%");
                                });
                        });
                });
            })
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
            });

        $total = $sql->count();
        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->first();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = 1;

        // Check if quiz exists before accessing questions
        if (!$res) {
            return response()->json([
                'total' => 0,
                'rows' => [],
                'message' => 'Quiz not found'
            ]);
        }

        foreach ($res->questions as $question) {
            $operate = BootstrapTableService::editButton(route('course-chapters.quiz.questions.update', array('id' => $question['id'])),true);
            $operate .= BootstrapTableService::deleteButton(route('course-chapters.quiz.questions.destroy', array('id' => $question['id']))); // soft delete
            //$operate .= BootstrapTableService::reorderButton(route('course-chapters.curriculum.reorder', array('id' => $question['id'], 'type' => 'questions'))); 
            $tempRow = $question->toArray();
            $tempRow['no'] = $no++;
            $tempRow['status'] = $question['is_active'] ? true : false;
            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        if (request()->has('id')) {
            return ResponseService::successResponse('Quiz questions fetched successfully', $res ? $res->questions : []);
        }
        return response()->json($bulkData);
    }

    // public function quizQuestionEdit($id, $type)
    // {
    //     try {
    //         // Find the quiz question by ID
    //         $question = CourseChapterQuizQuestion::with('options')->findOrFail($id);

    //         // Optionally, get the parent quiz if needed
    //         $quiz = null;
    //         if ($type === 'quiz' && $question->quiz_id) {
    //             $quiz = CourseChapterQuiz::find($question->quiz_id);
    //         }

    //         // Return the edit view with the question (and quiz if needed)
    //         return view('courses.chapters.curriculums.types-edit.quiz-question-edit', compact('question', 'quiz'));
    //     } catch (\Exception $e) {
    //         return ResponseService::errorResponse($e->getMessage());
    //     }
    // }

   public function curriculumLectureUpdate(UpdateLectureCurriculumRequest $request, $chapterId = null)
   {
        $chapterId = $chapterId ?? request('chapter_id');

        // Validate chapter ID
        if (!$chapterId) {
            return ResponseService::validationError('Chapter ID is required.');
        }

        try {
            $lectureData = HelperService::updateAndGetLectureData($request, $chapterId); // update or create lecture
            if($request->resource_status == 1){
                HelperService::getTypeResourceData('lecture', $request, $lectureData); // update resource data
            } else {
                // If resource toggle is off, remove all existing lecture resources
                \App\Models\Course\CourseChapter\Lecture\LectureResource::where('lecture_id', $lectureData->id)->delete();
            }

            // Load resources relationship
            $lectureData->load('resources');

            // Calculate resource statistics
            $lectureResources = $lectureData->resources ?? collect();
            $resourceStats = [
                'total_resources' => $lectureResources->count(),
                'file_resources' => $lectureResources->where('type', 'file')->count(),
                'url_resources' => $lectureResources->where('type', 'url')->count(),
                'youtube_resources' => $lectureResources->where('type', 'youtube_url')->count()
            ];

            // Create curriculum data without relationships to avoid duplication
            $curriculumArray = $lectureData->toArray();
            unset($curriculumArray['resources']); // Remove duplicate resources

            $responseData = [
                'curriculum' => $curriculumArray,
                'type' => 'lecture',
                'chapter_id' => $chapterId,
                'resource_stats' => $resourceStats,
                'resources' => $lectureResources->map(function($resource) {
                    return [
                        'id' => $resource->id,
                        'lecture_id' => $resource->lecture_id,
                        'type' => $resource->type,
                        'file' => $resource->file,
                        'file_extension' => $resource->file_extension,
                        'url' => $resource->url,
                        'youtube_url' => $resource->youtube_url,
                        'title' => $resource->title ?? '',
                        'is_active' => $resource->is_active,
                        'order' => $resource->order
                    ];
                })
            ];

            return ResponseService::successResponse('Lecture updated successfully', $responseData);
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
   }
   public function curriculumQuizUpdate(Request $request, $chapterId = null)
    {
        $chapterId = $chapterId ?? request('chapter_id');

        // Validate chapter ID
        if (!$chapterId) {
            return ResponseService::validationError('Chapter ID is required.');
        }

        try {
            $quizData = HelperService::updateAndGetQuizData($request, $chapterId, $request->qa_required ?? 1); // update or create quiz); // update or create quiz
            if ($request->resource_status == 1) {
                HelperService::getTypeResourceData('quiz', $request, null, $quizData); // update resource data
            }

            // Load resources relationship
            $quizData->load('resources');

            // Calculate resource statistics
            $quizResources = $quizData->resources ?? collect();
            $resourceStats = [
                'total_resources' => $quizResources->count(),
                'file_resources' => $quizResources->where('type', 'file')->count(),
                'url_resources' => $quizResources->where('type', 'url')->count()
            ];

            // Create curriculum data without relationships to avoid duplication
            $curriculumArray = $quizData->toArray();
            unset($curriculumArray['resources']); // Remove duplicate resources

            $responseData = [
                'curriculum' => $curriculumArray,
                'type' => 'quiz',
                'chapter_id' => $chapterId,
                'resource_stats' => $resourceStats,
                'resources' => $quizResources->map(function($resource) {
                    return [
                        'id' => $resource->id,
                        'quiz_id' => $resource->quiz_id,
                        'type' => $resource->type,
                        'file' => $resource->file,
                        'file_extension' => $resource->file_extension,
                        'url' => $resource->url,
                        'title' => $resource->title ?? '',
                        'is_active' => $resource->is_active,
                        'order' => $resource->order
                    ];
                })
            ];

            return ResponseService::successResponse('Quiz updated successfully', $responseData);
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function curriculumResourceUpdate(Request $request, $chapterId = null)
    {
        $chapterId = $chapterId ?? request('chapter_id');

        // Validate chapter ID
        if (!$chapterId) {
            return ResponseService::validationError('Chapter ID is required.');
        }

        try {
            $documentData = HelperService::updateAndGetDocumentData($request, $chapterId); // update or create resource
            if ($request->resource_status == 1) {
                HelperService::getTypeResourceData('resource', $request, null, $documentData); // update resource data
            }

            // Get chapter resources for context
            $chapterResources = \App\Models\Course\CourseChapter\CourseChapter::find($chapterId)
                ->resources()
                ->where('is_active', 1)
                ->get();

            // Calculate resource statistics
            $resourceStats = [
                'total_resources_in_chapter' => $chapterResources->count(),
                'document_resources' => $chapterResources->where('type', 'file')->count(),
                'url_resources' => $chapterResources->where('type', 'url')->count(),
                'current_resource_position' => $chapterResources->where('id', '<=', $documentData->id)->count()
            ];

            $responseData = [
                'curriculum' => $documentData->toArray(),
                'type' => 'document',
                'chapter_id' => $chapterId,
                'resource_stats' => $resourceStats,
                'chapter_resources' => $chapterResources->map(function($resource) {
                    return [
                        'id' => $resource->id,
                        'title' => $resource->title,
                        'type' => $resource->type,
                        'file' => $resource->file,
                        'url' => $resource->url,
                        'is_active' => $resource->is_active,
                        'chapter_order' => $resource->chapter_order
                    ];
                })
            ];

            return ResponseService::successResponse('Resource updated successfully', $responseData);
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function curriculumAssignmentUpdate(Request $request, $chapterId = null)
    {
        $chapterId = $chapterId ?? request('chapter_id');

        // Validate chapter ID
        if (!$chapterId) {
            return ResponseService::validationError('Chapter ID is required.');
        }

        try {
            $assignmentData = HelperService::updateAndGetAssignmentData($request, $chapterId); // update or create Assignment
            if ($request->resource_status == 1) {
                HelperService::getTypeResourceData('assignment', $request, null, null, $assignmentData); // update Assignment data
            }

            // Load resources relationship
            $assignmentData->load('resources');

            // Calculate resource statistics
            $assignmentResources = $assignmentData->resources ?? collect();
            $resourceStats = [
                'total_resources' => $assignmentResources->count(),
                'file_resources' => $assignmentResources->where('type', 'file')->count(),
                'url_resources' => $assignmentResources->where('type', 'url')->count()
            ];

            // Create curriculum data without relationships to avoid duplication
            $curriculumArray = $assignmentData->toArray();
            unset($curriculumArray['resources']); // Remove duplicate resources

            $responseData = [
                'curriculum' => $curriculumArray,
                'type' => 'assignment',
                'chapter_id' => $chapterId,
                'resource_stats' => $resourceStats,
                'resources' => $assignmentResources->map(function($resource) {
                    return [
                        'id' => $resource->id,
                        'assignment_id' => $resource->assignment_id,
                        'type' => $resource->type,
                        'file' => $resource->file,
                        'file_extension' => $resource->file_extension,
                        'url' => $resource->url,
                        'title' => $resource->title ?? '',
                        'is_active' => $resource->is_active,
                        'order' => $resource->order
                    ];
                })
            ];

            return ResponseService::successResponse('Assignment updated successfully', $responseData);
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function curriculumDestroy($id = null, $type = null)
    {
        $id = $id ?? request('id');
        $type = $type ?? request('type');

        if (!$id || !$type) {
            return ResponseService::errorResponse('Curriculum ID and type are required');
        }

        try {
            switch ($type) {
                case 'lecture':
                    $curriculum = CourseChapterLecture::findOrFail($id);
                    break;
                case 'quiz':
                    $curriculum = CourseChapterQuiz::findOrFail($id);
                    break;
                case 'document':
                    $curriculum = CourseChapterResource::findOrFail($id);
                    break;
                case 'assignment':
                    $curriculum = CourseChapterAssignment::findOrFail($id);
                    break;
                default:
                    return ResponseService::errorResponse('Invalid curriculum type');
            }
            $curriculum->delete();
            return ResponseService::successResponse('Curriculum deleted successfully');
        } catch (\Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    /**
     * Get trashed curriculum list
     */
    public function getTrashedCurriculumList($chapterId = null)
    {
        $chapterId = $chapterId ?? request('chapter_id');

        if (!$chapterId) {
            return ResponseService::validationError('Chapter ID is required');
        }

        try {
            $trashedCurriculums = collect();

            // Get trashed lectures
            $trashedLectures = CourseChapterLecture::onlyTrashed()
                ->where('course_chapter_id', $chapterId)
                ->get()
                ->map(function($item) {
                    $item->curriculum_type = 'lecture';
                    $item->formatted_duration = HelperService::getFormattedDuration($item->duration ?? 0);
                    $item->free_preview = $item->free_preview ? true : false;
                    return $item;
                });
            $trashedCurriculums = $trashedCurriculums->merge($trashedLectures);

            // Get trashed quizzes
            $trashedQuizzes = CourseChapterQuiz::onlyTrashed()
                ->where('course_chapter_id', $chapterId)
                ->get()
                ->map(function($item) {
                    $item->curriculum_type = 'quiz';
                    $item->time_limit = HelperService::getFormattedDuration($item->time_limit ?? 0);
                    return $item;
                });
            $trashedCurriculums = $trashedCurriculums->merge($trashedQuizzes);

            // Get trashed assignments
            $trashedAssignments = CourseChapterAssignment::onlyTrashed()
                ->where('course_chapter_id', $chapterId)
                ->get()
                ->map(function($item) {
                    $item->curriculum_type = 'assignment';
                    return $item;
                });
            $trashedCurriculums = $trashedCurriculums->merge($trashedAssignments);

            // Get trashed documents
            $trashedDocuments = CourseChapterResource::onlyTrashed()
                ->where('course_chapter_id', $chapterId)
                ->get()
                ->map(function($item) {
                    $item->curriculum_type = 'document';
                    $item->formatted_duration = HelperService::getFormattedDuration($item->duration ?? 0);
                    return $item;
                });
            $trashedCurriculums = $trashedCurriculums->merge($trashedDocuments);

            return ResponseService::successResponse('Trashed curriculum list fetched successfully', $trashedCurriculums->values());

        } catch (\Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    /**
     * Restore trashed curriculum
     */
    public function restoreCurriculum($id = null, $type = null)
    {
        $id = $id ?? request('id');
        $type = $type ?? request('type');

        if (!$id || !$type) {
            return ResponseService::errorResponse('Curriculum ID and type are required');
        }

        try {
            switch ($type) {
                case 'lecture':
                    $curriculum = CourseChapterLecture::withTrashed()->findOrFail($id);
                    break;
                case 'quiz':
                    $curriculum = CourseChapterQuiz::withTrashed()->findOrFail($id);
                    break;
                case 'document':
                    $curriculum = CourseChapterResource::withTrashed()->findOrFail($id);
                    break;
                case 'assignment':
                    $curriculum = CourseChapterAssignment::withTrashed()->findOrFail($id);
                    break;
                default:
                    return ResponseService::errorResponse('Invalid curriculum type');
            }

            $curriculum->restore();
            return ResponseService::successResponse('Curriculum restored successfully');

        } catch (\Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    public function reorder($id, $type)
    {
      //  try {
            $curriculum = HelperService::getCurriculumData($type, $id);
            $allowedFileTypes = HelperService::getAllowedFileTypeCategories();
            if($curriculum){
                return view('courses.chapters.curriculums.reorder', compact('curriculum', 'allowedFileTypes'), ['type_menu' => 'course-chapters']);
            }else{
                return ResponseService::errorResponse('Curriculum not found');
            }
        
    }
    public function reorderUpdate(Request $request, $id, $type)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);
        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        switch ($type) {
            case 'questions':
                $model = \App\Models\Course\CourseChapter\Quiz\QuizQuestion::class;
                break;
            case 'assignment_resources':
                $model = AssignmentResource::class;
                $foreignKey = 'assignment_id';
                break;
            case 'quiz_resources':
                $model = QuizResource::class;
                $foreignKey = 'quiz_id';
                break;
            case 'lecture_resources':
                $model = LectureResource::class;
                $foreignKey = 'lecture_id';
                break;
            default:
                return ResponseService::errorResponse('Invalid type');
        }

        foreach ($request->order as $index => $itemId) {
           
            $model::where('id', $itemId)
                ->update(['order' => $index + 1]);
        }

        return ResponseService::successResponse('Items reordered successfully');
    }


    /**
     * Update curriculum order for all items in a chapter (using standard pattern like sliders)
     */
    public function updateRankOfCurriculum(Request $request, $chapterId = null)
    {
        /**** Check if User has any of the permissions ****/
        ResponseService::noPermissionThenSendJson('course-chapters-edit');

        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'chapter_id' => 'nullable|exists:course_chapters,id'
            ]);
            if ($validator->fails()) {
                return ResponseService::errorResponse($validator->errors()->first());
            }

            // Use route param if available, otherwise fallback to request->chapter_id
            $chapterId = $chapterId ?? $request->chapter_id;

            if (!$chapterId) {
                return ResponseService::errorResponse('Chapter ID is required');
            }

            // Get all curriculum items for this chapter
            $chapter = CourseChapter::findOrFail($chapterId);
            $allCurriculum = $chapter->all_curriculum_data;

            foreach ($request->ids as $index => $id) {
                // Find the curriculum item by ID
                $curriculumItem = collect($allCurriculum)->firstWhere('id', $id);
                
                if ($curriculumItem) {
                    $model = null;
                    
                    switch ($curriculumItem['curriculum_type']) {
                        case 'lecture':
                            $model = CourseChapterLecture::class;
                            break;
                        case 'quiz':
                            $model = CourseChapterQuiz::class;
                            break;
                        case 'assignment':
                            $model = CourseChapterAssignment::class;
                            break;
                        case 'document':
                            $model = CourseChapterResource::class;
                            break;
                    }

                    if ($model) {
                        $model::where('id', $id)
                            ->where('course_chapter_id', $chapterId)
                            ->update(['chapter_order' => $index + 1]);
                    }
                }
            }

            return ResponseService::successResponse('Curriculum items reordered successfully');
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function quizQuestionsStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:course_chapter_quizzes,id',
            'quiz_data' => 'required|array',
            'quiz_data.*.question' => 'required|string',
            'quiz_data.*.option_data' => 'required|array',
            'quiz_data.*.option_data.*.option' => 'required|string',
            'quiz_data.*.option_data.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        $quizId = $request->quiz_id;

        try {
            DB::beginTransaction();

            // Iterate through submitted quiz data
            foreach ($request->quiz_data as $index => $questionData) {
                // Create or update the question
                $question = QuizQuestion::create([
                    'user_id' => Auth::id(),
                    'course_chapter_quiz_id' => $quizId,
                    'question' => $questionData['question'],
                    'points' => 1.0, // Default points, can be made configurable
                    'order' => $index + 1,
                    'is_active' => true,
                ]);

                // Handle options/answers
                if (isset($questionData['option_data']) && is_array($questionData['option_data'])) {
                    foreach ($questionData['option_data'] as $optionIndex => $optionData) {
                        QuizOption::create([
                            'user_id' => Auth::id(),
                            'quiz_question_id' => $question->id,
                            'option' => $optionData['option'],
                            'is_correct' => (bool) $optionData['is_correct'],
                            'order' => $optionIndex + 1,
                            'is_active' => true,
                        ]);
                    }
                }
            }

            DB::commit();
            return ResponseService::successResponse('Quiz questions saved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function quizQuestionsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:course_chapter_quizzes,id',
            'quiz_data' => 'required|array',
            'quiz_data.*.question_id' => 'nullable|integer|exists:quiz_questions,id',
            'quiz_data.*.question' => 'required|string',
            'quiz_data.*.option_data' => 'required|array',
            'quiz_data.*.option_data.*.option_id' => 'nullable|integer|exists:quiz_options,id',
            'quiz_data.*.option_data.*.option' => 'required|string',
            'quiz_data.*.option_data.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        $quizId = $request->quiz_id;

        try {
            DB::beginTransaction();

            $quiz = CourseChapterQuiz::findOrFail($quizId);

            $questionCount = count($request->quiz_data);
            $perQuestionPoints = $quiz->total_points > 0 ? ($quiz->total_points / $questionCount) : 1.0;

            foreach ($request->quiz_data as $index => $questionData) {
                // Save or update question
                $question = QuizQuestion::updateOrCreate(
                    ['id' => $questionData['question_id'] ?? null],
                    [
                        'user_id' => Auth::id(),
                        'course_chapter_quiz_id' => $quiz->id,
                        'question' => $questionData['question'],
                        'points' => $perQuestionPoints,
                        'order' => $index + 1,
                        'is_active' => true,
                    ]
                );

                // Save or update options
                if (isset($questionData['option_data']) && is_array($questionData['option_data'])) {
                    // Get existing option IDs for this question
                    $existingOptionIds = $question->options()->pluck('id')->toArray();
                    $submittedOptionIds = [];
                    
                    foreach ($questionData['option_data'] as $optionIndex => $optionData) {
                        $option = QuizOption::updateOrCreate(
                            ['id' => $optionData['option_id'] ?? null],
                            [
                                'user_id' => Auth::id(),
                                'quiz_question_id' => $question->id,
                                'option' => $optionData['option'],
                                'is_correct' => (bool) $optionData['is_correct'],
                                'order' => $optionIndex + 1,
                                'is_active' => true,
                            ]
                        );
                        
                        // Collect submitted option IDs
                        if ($option->id) {
                            $submittedOptionIds[] = $option->id;
                        }
                    }
                    
                    // Delete options that were not submitted (removed from frontend)
                    $optionsToDelete = array_diff($existingOptionIds, $submittedOptionIds);
                    if (!empty($optionsToDelete)) {
                        QuizOption::whereIn('id', $optionsToDelete)->delete();
                    }
                }
            }

            DB::commit();

            return ResponseService::successResponse('Quiz questions updated successfully', [
                'quiz_id' => $quiz->id,
                'questions' => $quiz->questions()->with('options')->get(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    public function quizQuestionGet(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_id' => 'required|exists:quiz_questions,id',
            ]);

            if ($validator->fails()) {
                return ResponseService::validationError($validator->errors()->first());
            }

            $question = QuizQuestion::where('id', $request->question_id)
                ->where('is_active', true)
                ->first();

            if (!$question) {
                return ResponseService::errorResponse('Question not found or inactive');
            }

            // Get options using the relationship (automatically excludes soft-deleted records)
            $options = $question->options()->where('is_active', true)->get();

            return ResponseService::successResponse('Question fetched successfully', [
                'question' => $question,
                'options' => $options->map(function($option) {
                    return [
                        'id' => $option->id,
                        'option' => $option->option,
                        'is_correct' => $option->is_correct,
                        'order' => $option->order,
                        'is_active' => $option->is_active,
                    ];
                })
            ]);
        } catch (Exception $e) {
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function quizQuestionsDelete(Request $request)
    {
        try {
            DB::beginTransaction();

            $question = QuizQuestion::findOrFail($request->id);
         // Delete related options first
            QuizOption::where('quiz_question_id', $question->id)->delete();

            // Delete the question itself
            $question->delete();

            DB::commit();

            return ResponseService::successResponse('Quiz question deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse($e->getMessage());
        }
    }
    public function quizQuestionsBulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ids' => 'required|array',
            'question_ids.*' => 'integer|exists:quiz_questions,id',
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $questions = QuizQuestion::whereIn('id', $request->question_ids)->get();

            foreach ($questions as $question) {
                QuizOption::where('quiz_question_id', $question->id)->delete();
                $question->delete();
            }

            DB::commit();

            return ResponseService::successResponse('Selected quiz questions deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseService::errorResponse($e->getMessage());
        }
    }

    
}
