@extends('layouts.app')

@section('title')
    {{ __('Instructor Wallet History') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1>
@endsection

@section('main')
    <div class="content-wrapper">
        <!-- Table List -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('Instructor Wallet History') }}
                        </h4>
                        <p class="text-muted">{{ __('View all wallet transactions for instructors including commissions, withdrawals, and other activities.') }}</p>
                        
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label mb-1">{{ __('Filter by Transaction Type') }}</label>
                                <select id="filter_transaction_type" class="form-control">
                                    <option value="">{{ __('All Transactions') }}</option>
                                    <option value="credit">{{ __('Credit Only') }}</option>
                                    <option value="debit">{{ __('Debit Only') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1">{{ __('Filter by Instructor') }}</label>
                                <select id="filter_instructor_id" class="form-control select2">
                                    <option value="">{{ __('All Instructors') }}</option>
                                    @foreach ($instructors as $instructor)
                                        <option value="{{ $instructor->id }}">{{ $instructor->name }} ({{ $instructor->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" id="reset_filters">{{ __('Reset Filters') }}</button>
                            </div>
                        </div>
                        
                        <table aria-describedby="mydesc" class="table" id="table_list"
                            data-toggle="table" data-url="{{ route('instructor.wallet-history.data') }}" 
                            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-mobile-responsive="true" data-use-row-attr-func="true"
                            data-maintain-selected="true" data-export-data-type="all"   
                            data-export-options='{ "fileName": "{{ __('instructor-wallet-history') }}-<?= date('d-m-y') ?>","ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-field="id" data-visible="false">{{ __('ID') }}</th>
                                    <th data-field="no">{{ __('No.') }}</th>
                                    <th data-field="instructor_name">{{ __('Instructor Name') }}</th>
                                    <th data-field="instructor_email">{{ __('Email') }}</th>
                                    <th data-field="type">{{ __('Type') }}</th>
                                    <th data-field="transaction_type">{{ __('Transaction Type') }}</th>
                                    <th data-field="entry_type">{{ __('Entry Type') }}</th>
                                    <th data-field="amount" data-sortable="true" data-escape="false">{{ __('Amount') }}</th>
                                    <th data-field="description">{{ __('Description') }}</th>
                                    <th data-field="created_at" data-sortable="true">{{ __('Date & Time') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize select2 for instructor filter
        $('#filter_instructor_id').select2({
            placeholder: '{{ __("Select Instructor") }}',
            allowClear: true
        });
        
        // Filter change handlers
        $('#filter_transaction_type, #filter_instructor_id').on('change', function(){
            $('#table_list').bootstrapTable('refresh');
        });
        
        // Reset filters
        $('#reset_filters').on('click', function(){
            $('#filter_transaction_type').val('');
            $('#filter_instructor_id').val('').trigger('change');
            $('#table_list').bootstrapTable('refresh');
        });
    });
    
    // Query params function for bootstrap table
    function queryParams(params) {
        params.transaction_type = $('#filter_transaction_type').val();
        params.instructor_id = $('#filter_instructor_id').val();
        return params;
    }
</script>
@endsection
