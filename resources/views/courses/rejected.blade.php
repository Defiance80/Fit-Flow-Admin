@extends('layouts.app')

@section('title')
    {{ __('Rejected Courses') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1> @endsection

@section('main')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <div id="rejectedToolbar" class="mb-3">
                            <div class="row">
                                @if($shouldShowTrainerFilters ?? true)
                                <div class="col-md-4">
                                    <label class="form-label mb-1">{{ __('Filter by Trainer') }}</label>
                                    <select id="rejected_trainer_id" class="form-control select2">
                                        <option value="">{{ __('All') }}</option> @foreach ($trainers as $trainer) <option value="{{ $trainer->id }}">{{ $trainer->name }}</option> @endforeach </select>
                                </div>
                                @endif
                                <div class="col-md-2 d-flex align-items-end">
                                <label class="form-label mb-1">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-secondary w-100" id="reset_rejected_filters">{{ __('Reset') }}</button>
                                </div>
                            </div>
                        </div>

                        <table class="table" id="table_rejected" data-toggle="table" data-url="{{ route('courses.rejected.list') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#rejectedToolbar" data-show-refresh="true" data-trim-on-search="false" data-mobile-responsive="true" data-maintain-selected="true" data-escape="true" data-query-params="rejectedQueryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                    <th scope="col" data-field="no">{{ __('no.') }}</th>
                                    <th scope="col" data-field="title" data-sortable="true">{{ __('Title') }}</th>
                                    <th scope="col" data-field="trainer_name" data-sortable="false">{{ __('Trainer') }}</th>
                                    <th scope="col" data-field="category.name" data-sortable="false">{{ __('Category') }}</th>
                                    <th scope="col" data-field="created_at" data-sortable="true" data-formatter="rejectedDateFormatter">{{ __('Requested On') }}</th>
                                    <th scope="col" data-field="operate" data-formatter="rejectedOperateFormatter" data-escape="false">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> @endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
@endpush

@section('script')
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            // Initialize Select2
            $('#rejected_trainer_id').select2({
                placeholder: '{{ __("All") }}',
                allowClear: true,
                width: '100%'
            });

            $('#rejected_trainer_id').on('change', function(){
                $('#table_rejected').bootstrapTable('refresh');
            });
            $('#reset_rejected_filters').on('click', function(){
                $('#rejected_trainer_id').val(null).trigger('change');
                $('#table_rejected').bootstrapTable('refresh');
            });
        });

        function rejectedQueryParams(params){
            params.trainer_id = $('#rejected_trainer_id').val();
            return params;
        }

        function rejectedDateFormatter(value, row){
            if (row.created_at_human) {
                return `<span title="${row.created_at || ''}">${row.created_at_human}</span>`;
            }
            return value || '-';
        }

        function rejectedOperateFormatter(value, row){
            const viewBtn = `<a href="{{ url('courses') }}/${row.id}/edit" class="btn icon btn-xs btn-rounded btn-icon rounded-pill btn-info" title="{{ __('View') }}"><i class="fa fa-eye"></i></a>`;
            return viewBtn;
        }
    </script>
@endsection




