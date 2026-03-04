@extends('layouts.app')

@section('title')
    {{ __('Manage Taxes') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1>
    <div class="section-header-button ml-auto">
    </div>
@endsection

@section('main')
    <div class="content-wrapper">

        <!-- Create Form -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            {{ __('Create Tax') }}
                        </h4>
                        <form class="pt-3 mt-6 create-form" method="POST" action="{{ route('taxes.store') }}" data-parsley-validate> @csrf <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('Name') }} <span class="text-danger"> * </span></label>
                                    <input type="text" name="name" placeholder="{{ __('Tax name, e.g. GST') }}" class="form-control" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Percentage') }} (%) <span class="text-danger"> * </span></label>
                                    <input type="number" step="0.01" min="1" max="99.99" name="percentage" placeholder="e.g. 2.00" class="form-control" required>
                                </div>
                               
                            </div>
                            <input class="btn btn-primary float-right ml-3" type="submit" value="{{ __('Submit') }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table List -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('List Taxes') }}
                        </h4>
                        <div class="col-12 mt-4 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('all') }}</a></b> {{ __('|') }} <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                        </div>
                        <table aria-describedby="mydesc" class="table reorder-table-row" id="table_list"
                            data-table="taxes" data-toggle="table" data-status-column="is_active"
                            data-url="{{ route('taxes.show', 0) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-mobile-responsive="true" data-use-row-attr-func="true"
                            data-maintain-selected="true" data-export-data-type="all"
                            data-export-options='{ "fileName": "{{ __('taxes') }}-<?= date('d-m-y') ?>","ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-field="id" data-visible="false" data-escape="true">{{ __('id') }}</th>
                                    <th data-field="no" data-escape="true">{{ __('no.') }}</th>
                                    <th data-field="name" data-escape="true">{{ __('Name') }}</th>
                                    <th data-field="percentage" data-escape="true">{{ __('Percentage %') }}</th>
                                    <th data-field="is_active" data-formatter="statusFormatter" data-escape="false" id="is-active-column">{{ __('Status') }}</th>
                                    <th data-field="operate" data-sortable="false" data-events="taxAction" data-escape="false">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="taxEditModal" tabindex="-1" role="dialog"
            aria-labelledby="taxEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taxEditModalLabel">{{ __('Edit Tax') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <span aria-hidden="true" style="font-size: 1.5rem; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff;">&times;</span>
                        </button>
                    </div>
                    <form class="pt-3 mt-6 edit-form" method="POST" data-parsley-validate id="taxEditForm"> @csrf
                        @method('PUT')
        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_tax_id">
                            <div class="form-group">
                                <label>{{ __('Name') }} <span class="text-danger"> * </span></label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Percentage') }} (%)</label>
                                <input type="number" name="percentage" step="0.01" min="1" max="99.99" id="edit_percentage" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input class="btn btn-primary" type="submit" value="{{ __('Update') }}">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Handle All/Trashed tab switching
        let showDeleted = 0;
        $('.table-list-type').on('click', function(e){
            e.preventDefault();
            $('.table-list-type').removeClass('active');
            $(this).addClass('active');
            showDeleted = $(this).data('id') === 1 ? 1 : 0;
            
            // Toggle is_active column visibility
            toggleIsActiveColumn();
            
            $('#table_list').bootstrapTable('refresh');
        });

        // Hide is_active column when viewing trashed items
        $(document).ready(function() {
            // Listen for table refresh/load events
            $('#table_list').on('load-success.bs.table', function() {
                toggleIsActiveColumn();
            });
        });

        function toggleIsActiveColumn() {
            const isTrashed = $('.table-list-type.active').data('id') == 1;
            const $table = $('#table_list');
            
            if (isTrashed) {
                // Hide is_active column when viewing trashed items
                $table.bootstrapTable('hideColumn', 'is_active');
            } else {
                // Show is_active column when viewing all items
                $table.bootstrapTable('showColumn', 'is_active');
            }
        }

        // Attach filters to table query params
        function queryParams(params) {
            params.show_deleted = showDeleted;
            return params;
        }
    </script>
@endsection


