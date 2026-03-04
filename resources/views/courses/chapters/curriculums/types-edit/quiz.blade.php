<div class="card">
<div class="card-body">
    <form class="pt-3 mt-6 create-form" method="POST" action="{{ route('course-chapters.curriculum.quiz.update', $curriculum->course_chapter_id) }}" data-parsley-validate enctype="multipart/form-data" data-success-function="formSuccessFunction">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="quiz_type_id" value="{{ $curriculum->id }}">
    {{-- Title --}}
    <div class="row">
    <div class="form-group mandatory col-12">
        <label class="form-label d-block" for="quiz-title">{{ __('Title') }} </label>
        <input type="text" name="quiz_title" id="quiz-title" class="form-control" placeholder="{{ __('Title') }}" value="{{ old('quiz_title', $curriculum->title) }}" required>
    </div>

    {{-- Description --}}
    <div class="form-group col-12">
        <label class="form-label d-block" for="quiz-description">{{ __('Description') }} </label>
        <textarea name="quiz_description" id="quiz-description" class="form-control" placeholder="{{ __('Description') }}">{{ old('quiz_description', $curriculum->description) }}</textarea>
    </div>

    {{-- Time Limit --}}
    <div class="form-group mandatory col-12 col-xl-4">
        <label class="form-label d-block" for="quiz-time-limit">{{ __('Time Limit (in seconds)') }} </label>
        <input type="number" name="quiz_time_limit" id="quiz-time-limit" class="form-control" placeholder="{{ __('Time Limit') }}" min="0" value="{{ old('quiz_time_limit', $curriculum->time_limit_seconds ?? $curriculum->time_limit) }}" required>
    </div>

    {{-- Total Points --}}
    <div class="form-group mandatory col-12 col-xl-4">
        <label class="form-label d-block" for="quiz-total-points">{{ __('Total Points') }} </label>
        <input type="number" name="quiz_total_points" id="quiz-total-points" class="form-control" placeholder="{{ __('Total Points') }}" min="0" value="{{ old('quiz_total_points', $curriculum->total_points) }}" required>
    </div>

    {{-- Passing Score --}}
    <div class="form-group mandatory col-12 col-xl-4">
        <label class="form-label d-block" for="quiz-passing-score">{{ __('Passing Score') }} </label>
        <input type="number" name="quiz_passing_score" id="quiz-passing-score" class="form-control" placeholder="{{ __('Passing Score (%)') }}" min="0" max="100" value="{{ old('quiz_passing_score', $curriculum->passing_score) }}" required>
    </div>

    {{-- Can Skip --}}
    <div class="form-group col-sm-12 col-lg-2">
        <label class="control-label">{{ __('Can Skip ?') }}</label>
        <div class="custom-switches-stacked mt-2">
            <label class="custom-switch">
                <input type="checkbox" class="custom-switch-input custom-toggle-switch can-skip-switch" {{ old('quiz_can_skip', $curriculum->can_skip) ? 'checked' : '' }}>
                <input type="hidden" name="quiz_can_skip" class="custom-toggle-switch-value quiz-can-skip" value="{{ old('quiz_can_skip', $curriculum->can_skip) }}">
                <span class="custom-switch-indicator"></span>
            </label>
        </div>
    </div>
    {{-- Is Active --}}
    <div class="form-group col-sm-12 col-md-6 col-xl-3">
        <div class="control-label">{{ __('Status') }}</div>
        <div class="custom-switches-stacked mt-2">
            <label class="custom-switch">
                <input type="checkbox" class="custom-switch-input custom-toggle-switch" {{ $curriculum->is_active == 1 ? 'checked' : '' }}>
                <input type="hidden" name="is_active" class="custom-toggle-switch-value" value="{{ $curriculum->is_active ?? 0 }}">
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">{{ __('Active') }}</span>
            </label>
        </div>
    </div>
</div>

    {{-- Quiz Questions Repeater Section --}}
    <div class="quiz-questions-section">
        <div data-repeater-list="quiz_data">
            @foreach($curriculum->questions as $qIndex => $question)
            <div class="row quiz-question-input-section d-flex align-items-center mb-2 bg-light p-2 rounded mt-2" data-repeater-item>
                {{-- Remove Question --}}
                <div class="form-group col-sm-12 col-lg-2 mt-4">
                    <button data-repeater-delete type="button" class="btn btn-danger remove-question" title="{{ __('remove') }}" {{ $loop->first ? 'disabled' : '' }}>
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="question_id" class="question-id" value="{{ $question->id }}">
                {{-- Question Input --}}
                <div class="form-group mandatory question col-12">
                    <label class="form-label d-block" for="question">{{ __('Question') }} </label>
                    <textarea name="question" id="question" class="form-control question-input" placeholder="{{ __('Question') }}">{{ old("quiz_data.$qIndex.question", $question->question) }}</textarea>
                </div>

                {{-- Quiz Options Repeater Section --}}
                <div class="quiz-options-section col-12">
                    <div data-repeater-list="option_data">
                        @foreach($question->options as $oIndex => $option)
                        <div class="row quiz-option-input-section" data-repeater-item>
                            {{-- Option Input --}}
                            <div class="form-group mandatory option-1 col-sm-12 col-lg-6">
                                <label class="form-label d-block option-label" for="option-1">{{ __('Option') }} </label>
                                <input type="text" name="option" class="form-control option-input" placeholder="{{ __('Option') }}" value="{{ old("quiz_data.$qIndex.option_data.$oIndex.option", $option->option) }}">
                            </div>
                            <input type="hidden" name="option_id" class="option-id" value="{{ $option->id }}">

                            {{-- Is Answer --}}
                            <div class="form-group col-sm-12 col-lg-2">
                                <label class="control-label">{{ __('Is Answer') }}</label>
                                <div class="custom-switches-stacked mt-2">
                                    <label class="custom-switch">
                                        <input type="checkbox" class="custom-switch-input custom-toggle-switch answer-switch" {{ $option->is_correct ? 'checked' : '' }}>
                                        <input type="hidden" name="is_correct" class="custom-toggle-switch-value is-answer" value="{{ $option->is_correct }}">
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                </div>
                            </div>

                            {{-- Remove Option --}}
                            <div class="form-group col-sm-12 col-lg-2 mt-4">
                                <button data-repeater-delete type="button" class="btn btn-danger remove-option-1" title="{{ __('remove') }}">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    {{-- Add New Option --}}
                    <button type="button" class="btn btn-primary mt-3 add-new-option" data-repeater-create title="{{ __('Add New Option') }}" style="width: 100%; padding: 10px 15px; font-weight: 500; border-radius: 6px;">
                        <i class="fa fa-plus mr-2"></i> {{ __('Add New Option') }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
         {{-- Add New Question --}}
        <button type="button" class="btn btn-success mt-1" data-repeater-create title="{{ __('Add New Question') }}">
            <i class="fa fa-plus"></i> {{ __('Add New Question') }}
        </button>
    </div>
    <div><hr></div>
                {{-- Resource Toggle Section --}}
                <div class="form-group col-12 resource-toggle-section">
                    <div class="control-label">{{ __('Resource') }}</div>
                    <div class="custom-switches-stacked mt-2">
                        <label class="custom-switch">
                            <input type="checkbox" class="custom-switch-input custom-toggle-switch" id="resource-toggle">
                            <input type="hidden" name="resource_status" class="custom-toggle-switch-value" value="0" id="resource-status">
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">{{ __('Yes') }}</span>
                        </label>
                    </div>
                </div>
                <div class="resource-container" style="display: none;">
                    {{-- Resource Repeater Section --}}
                    <div class="resource-section">
                        <div data-repeater-list="resource_data">
                            <div class="row resource-input-section d-flex align-items-center mb-2 bg-light p-3 rounded mt-2" data-repeater-item>
                                {{-- Remove Resource --}}
                                <div class="form-group col-12">
                                    <button data-repeater-delete type="button" class="btn btn-danger remove-resource" title="{{ __('remove') }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                                {{-- Resource Type --}}
                                <div class="form-group mandatory col-sm-12 col-lg-6">
                                    <label class="form-label d-block">{{ __('Resource Type') }} </label>
                                    <select name="resource_type" class="form-control course-chapter-resource-type">
                                        <option value="">{{ __('Select Resource Type') }}</option>
                                        <option value="url">{{ __('External URL') }}</option>
                                        <option value="file">{{ __('File') }}</option>
                                        <option value="document">{{ __('Document') }}</option>
                                        <option value="video">{{ __('Video') }}</option>
                                        <option value="audio">{{ __('Audio') }}</option>
                                        <option value="image">{{ __('Image') }}</option>
                                    </select>
                                </div>

                                {{-- Resource Title --}}
                                <div class="form-group mandatory col-sm-12 col-lg-6 resource-title-field" style="display: none;">
                                    <label class="form-label d-block">{{ __('Resource Title') }} </label>
                                    <input type="text" name="resource_title" class="form-control resource-title-input" placeholder="{{ __('Resource Title') }}">
                                </div>

                                {{-- Assignment Resource Id --}}
                                <input type="hidden" name="id" class="assignment-resource-id">
                                {{-- Resource URL Input --}}
                                <div class="form-group mandatory resource-url col-sm-12 col-lg-6" style="display: none;">
                                    <label class="form-label d-block">{{ __('Resource URL') }} </label>
                                    <input type="text" name="resource_url" class="form-control resource-url-input" placeholder="{{ __('Resource URL') }}">
                                </div>

                                {{-- Resource File Input --}}
                                <div class="form-group mandatory resource-file col-sm-12 col-lg-6" style="display: none;">
                                    <label class="form-label d-block">{{ __('Resource File') }} </label>
                                    <input type="file" name="resource_file" class="form-control resource-file-input" placeholder="{{ __('Resource File') }}" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.odt,.ods,.odp,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.tif,.svg,.webp,.ico,.psd,.ai,.eps,.mp4,.mov,.avi,.wmv,.flv,.mkv,.webm,.m4v,.3gp,.3g2,.asf,.rm,.rmvb,.vob,.ogv,.mts,.m2ts,.mp3,.wav,.ogg,.m4a,.m4b,.m4p,.aac,.flac,.wma,.aiff,.au,.ra,.amr,.opus,.zip,.rar,.7z,.tar,.gz,.bz2,.xz">
                                    <input type="hidden" name="resource_file_url" class="resource-file-url">
                                    <a target="_blank" class="btn btn-primary mt-2 resource-file-preview" style="display: none;">{{ __('File Preview') }}</a>
                                </div>
                            </div>
                        </div>
                        {{-- Add New Resource --}}
                        <button type="button" class="btn btn-success mt-1" data-repeater-create title="{{ __('Add New Resource') }}">
                            <i class="fa fa-plus"></i> {{ __('Add New Resource') }}
                        </button>
                    </div>
                    <div><hr></div>
                </div>
                {{-- End Resource Container --}}

    <input class="btn btn-primary float-right ml-3" id="create-btn" type="submit" value="{{ __('Update Quiz') }}">
</form>
</div>
</div>

@push('scripts')
<script>
        $(document).ready(function() {
            // Initialize answer switches for existing questions
            setTimeout(function() {
                $('.quiz-questions-section').find('.quiz-question-input-section').each(function() {
                    var $questionSection = $(this);
                    $questionSection.find('.quiz-options-section').each(function() {
                        // Set is_correct value based on checkbox state
                        $(this).find('.answer-switch').each(function() {
                            var isChecked = $(this).is(':checked');
                            $(this).closest('.quiz-option-input-section').find('.is-answer').val(isChecked ? 1 : 0);
                        });
                        manageAnswerSwitch($(this));
                    });
                });
            }, 100);

            // Resources
            let resourcesExists = '{{ $curriculum->resources->count() }}';
            if (resourcesExists > 0) {
                $('#resource-toggle').prop('checked', true);
                $('#resource-status').val(1);
                $('.resource-container').show();

                resourceSectionRepeater.setList([
                    @foreach($curriculum->resources as $resource)
                        @if($resource->type == 'url')
                        {
                            'id': '{{ $resource->id }}',
                            'resource_type': '{{ $resource->type }}',
                            'resource_title': '{{ $resource->title ?? '' }}',
                            'resource_url': '{{ $resource->url }}',
                        },
                        @elseif($resource->type == 'file')
                        {
                            'id': '{{ $resource->id }}',
                            'resource_type': '{{ $resource->type }}',
                            'resource_title': '{{ $resource->title ?? '' }}',
                            'resource_file_url': '{{ $resource->file }}',
                        }
                        @else
                        {
                            'id': '{{ $resource->id }}',
                            'resource_type': '{{ $resource->type }}',
                            'resource_title': '{{ $resource->title ?? '' }}',
                            'resource_url': '{{ $resource->url }}',
                        }
                        @endif
                    @endforeach
                ]);

                // Bind change event to each resource_type after repeater sets the list
                setTimeout(function () {
                    $('.resource-container').find('.course-chapter-resource-type').off('change').on('change', function () {
                        let $row = $(this).closest('[data-repeater-item]');
                        let selectedType = $(this).val();

                        // Hide all resource-specific fields
                        $row.find('.resource-url, .resource-file, .resource-title-field').hide();

                        if (selectedType && selectedType !== '') {
                            $row.find('.resource-title-field').show();
                        }

                        if (selectedType === 'url') {
                            $row.find('.resource-url').show();
                        } else if (selectedType === 'file') {
                            $row.find('.resource-file').show();
                            let fileUrl = $row.find('.resource-file-url').val();
                            if (fileUrl) {
                                $row.find('.resource-file-preview').attr('href', '{{ asset("storage") }}/' + fileUrl).show();
                            }
                        }
                    }).trigger('change'); // trigger once to reflect current state
                }, 100); // Delay ensures DOM is ready after `setList`

            } else {
                $('#resource-toggle').prop('checked', false);
                $('#resource-status').val(0);
                $('.resource-container').hide();
            }

        });

        // Handle resource type change to show/hide resource title field for new resources
        $(document).on('change', '.course-chapter-resource-type', function() {
            var resourceType = $(this).val();
            var resourceTitleField = $(this).closest('.resource-input-section').find('.resource-title-field');
            
            if (resourceType && resourceType !== '') {
                resourceTitleField.show();
            } else {
                resourceTitleField.hide();
            }
        });

        function formSuccessFunction(response){
            setTimeout(function(){
                window.location.reload();
            }, 1500);
        }
    </script>
@endpush
