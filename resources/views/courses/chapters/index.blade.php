@extends('layouts.app')

@section('title')
    {{ __('Manage Chapters') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1>
    <div class="section-header-button ml-auto">
    </div> @endsection

@section('main')
    <div class="content-wrapper">
        <!-- Create Form -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            {{ __('Create Chapter') }}
                        </h4>
                        {{-- Form Start --}}
                        <form class="pt-3 mt-6 create-form" method="POST" action="" data-parsley-validate enctype="multipart/form-data">
                            <div class="row">
                                {{-- Courses --}}
                                <div class="form-group mandatory col-sm-12 col-md-6 col-xl-3 ">
                                    <label for="course_id" class="form-label">{{ __('Course') }}</label>
                                    <select name="course_id" id="course_id" class="form-control" data-parsley-required="true">
                                        <option value="">{{ __('Select Course') }}</option> @if(isset($courses) && collect($courses)->isNotEmpty())
                                            @foreach ($courses as $course) <option value="{{ $course->id }}">{{ $course->title }}</option> @endforeach
                                        @endif </select>
                                </div>

                                {{-- Title --}}
                                <div class="form-group mandatory col-sm-12 col-md-6 col-xl-3">
                                    <label for="title" class="form-label">{{ __('Title') }}</label>
                                    <input type="text" name="title" id="title" class="form-control" data-parsley-required="true" placeholder="{{ __('Title') }}">
                                </div>

                                {{-- Auto active; no status toggle on create --}}

                                {{-- Description --}}
                                <div class="form-group col-12 mandatory">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                    <textarea name="description" id="description" class="form-control" data-parsley-required="true" placeholder="{{ __('Description') }}"></textarea>
                                </div>
                            </div>
                            <input class="btn btn-primary float-right ml-3" id="create-btn" type="submit" value="{{ __('Submit') }}">
                        </form>
                        {{-- Form End --}}
                    </div>
                </div>
            </div>
        </div>
        <!-- Table List -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        {{-- Table Title --}}
                        <h4 class="card-title">
                            {{ __('List Course Chapters') }}
                        </h4>
                        {{-- Show Trash Button --}}
                        <div class="col-12 mt-2 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('all') }}</a></b> {{ __('|') }} <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                        </div>
                        {{-- Table Start --}}
                        <table aria-describedby="mydesc" class="table" id="table_list" data-toggle="table" data-url="{{ route('course-chapters.show',0) }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-mobile-responsive="true" data-use-row-attr-func="true" data-maintain-selected="true" data-export-data-type="all" data-export-options='{ "fileName": "{{ __('course-chapters') }}-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}' data-show-export="true" data-query-params="queryParams" data-table="course_chapters" data-status-column="is_active">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false" data-escape="true"> {{ __('ID') }}</th>
                                    <th scope="col" data-field="no" data-escape="true">{{ __('No.') }}</th>
                                    <th scope="col" data-field="course.title" data-escape="true">{{ __('Course') }}</th>
                                    <th scope="col" data-field="title" data-escape="true">{{ __('Title') }}</th>
                                    <th scope="col" data-field="slug" data-escape="true">{{ __('Slug') }}</th> 
                                    <th scope="col" data-field="is_active" data-formatter="statusFormatter" data-escape="false">{{ __('Status') }}</th>
                                    <th scope="col" data-field="description" data-escape="true">{{ __('Description') }}</th>
                                    <th scope="col" data-field="operate" data-sortable="false" data-events="courseChapterAction" data-escape="false">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                        {{-- Filters Toolbar --}}
                        <div class="row mt-3" id="toolbar">
                            @if($shouldShowInstructorFilters ?? true)
                            <div class="col-md-4">
                                <label class="form-label mb-1">{{ __('Filter by Instructor') }}</label>
                                <select id="filter_instructor_id" class="form-control select2">
                                    <option value="">{{ __('All') }}</option> @if(isset($instructors) && collect($instructors)->isNotEmpty())
                                        @foreach ($instructors as $instructor) <option value="{{ $instructor->id }}">{{ $instructor->name }}</option> @endforeach
                                    @endif </select>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <label class="form-label mb-1">{{ __('Filter by Course') }}</label>
                                <select id="filter_course_id" class="form-control select2">
                                    <option value="">{{ __('All') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" id="reset_filters">{{ __('Reset Filters') }}</button>
                            </div>
                        </div>
                        {{-- Table End --}}
                    </div>
                </div>
            </div>
        </div>
    </div>



<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">{{ __('Edit Chapter') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <span aria-hidden="true" style="font-size: 1.5rem; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff;">&times;</span>
                </button>
            </div>
            <form class="pt-3 pb-3 mt-6 edit-form" method="POST" data-parsley-validate enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" >
                    <div class="row edit-content">
                        {{-- Course --}}
                        <div class="form-group mandatory col-12">
                            <label for="edit-course-id" class="form-label">{{ __('Course') }}</label>
                            <select name="course_id" id="edit-course-id" class="form-control" data-parsley-required="true">
                                <option value="">{{ __('Select Course') }}</option>
                                @if(isset($courses) && collect($courses)->isNotEmpty())
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Title --}}
                        <div class="form-group mandatory col-12">
                            <label for="edit-title" class="form-label">{{ __('Title') }}</label>
                            <input type="text" name="title" id="edit-title" class="form-control" data-parsley-required="true" placeholder="{{ __('Title') }}">
                        </div>

                        {{-- Is Active --}}
                        <div class="form-group col-12">
                            <div class="control-label">{{ __('Status') }}</div>
                            <div class="custom-switches-stacked mt-2">
                                <label class="custom-switch">
                                    <input type="checkbox" name="is_active" id="edit-is-active" class="custom-switch-input">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">{{ __('Active') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group col-12 mandatory">
                            <label for="edit-description" class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" id="edit-description" class="form-control" data-parsley-required="true" placeholder="{{ __('Description') }}"></textarea>
                        </div>
                    </div>
                    <input class="btn btn-primary float-right ml-3" id="edit-btn" type="submit" value="{{ __('Submit') }}">
                </div>
            </form>
        </div>
    </div>
</div> @endsection

@section('script')
    <script>
    let showDeleted = 0;
    function queryParams(params){
        params.instructor_id = $('#filter_instructor_id').val();
        params.course_id = $('#filter_course_id').val();
        params.show_deleted = showDeleted;
        return params;
    }

    $(document).ready(function(){
        $('#filter_instructor_id').on('change', function(){
            const instructorId = $(this).val();
            // populate courses for selected instructor
            $('#filter_course_id').empty().append(`<option value="">{{ __('All') }}</option>`);
            if(instructorId){
                $.get(`{{ route('course-chapters.instructor.courses', ['instructor_id' => 'INSTR_ID']) }}`.replace('INSTR_ID', instructorId), function(res){
                    if(Array.isArray(res)){
                        res.forEach(function(c){
                            $('#filter_course_id').append(`<option value="${c.id}">${c.title}</option>`);
                        });
                    }
                });
            }
            $('#table_list').bootstrapTable('refresh');
        });
        $('#filter_course_id').on('change', function(){
            $('#table_list').bootstrapTable('refresh');
        });
        $('.table-list-type').on('click', function(e){
            e.preventDefault();
            $('.table-list-type').removeClass('active');
            $(this).addClass('active');
            showDeleted = $(this).data('id') === 1 ? 1 : 0;
            $('#table_list').bootstrapTable('refresh');
        });

        $('#reset_filters').on('click', function(){
            $('#filter_instructor_id').val('').trigger('change');
            $('#filter_course_id').val('').trigger('change');
            showDeleted = 0;
            $('.table-list-type').removeClass('active');
            $('.table-list-type[data-id="0"]').addClass('active');
            $('#table_list').bootstrapTable('refresh');
        });
    });
</script>
@endsection


