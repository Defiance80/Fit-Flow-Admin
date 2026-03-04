@extends('layouts.app')

@section('title')
    {{ __('Manage Replies') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1>
@endsection

@section('main')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            {{ __('Helpdesk Replies') }}
                        </h4>
                        
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Replies</h5>
                                        <h3 id="total-replies">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Recent Replies</h5>
                                        <h3 id="recent-replies">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-control" id="question-filter">
                                    <option value="">All Questions</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="user-filter">
                                    <option value="">All Users</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="search-input" placeholder="Search by reply content, user, or question...">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary" id="search-btn">Search</button>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table id="replies-table" class="table table-striped" 
                                   data-url="{{ route('admin.helpdesk.replies.index') }}"
                                   data-pagination="true" data-side-pagination="server" 
                                   data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" 
                                   data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" 
                                   data-trim-on-search="false" data-mobile-responsive="true" 
                                   data-use-row-attr-func="true" data-maintain-selected="true" 
                                   data-export-data-type="all" data-export-options='{"fileName": "replies-<?= date('d-m-y') ?>","ignoreColumn":["operate"]}' 
                                   data-show-export="true" data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-visible="false">{{ __('ID') }}</th>
                                        <th data-field="no">{{ __('No.') }}</th>
                                        <th data-field="reply">{{ __('Reply') }}</th>
                                        <th data-field="question_title">{{ __('Question') }}</th>
                                        <th data-field="user_name">{{ __('User') }}</th>
                                        <th data-field="parent_reply">{{ __('Parent Reply') }}</th>
                                        <th data-field="created_at">{{ __('Created At') }}</th>
                                        <th data-field="operate" data-sortable="false" data-events="replyAction" data-escape="false">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Initialize table
    $('#replies-table').bootstrapTable();
    
    // Load statistics
    loadStatistics();
    
    // Load filters
    loadQuestions();
    loadUsers();
    
    // Search functionality
    $('#search-btn').click(function() {
        $('#replies-table').bootstrapTable('refresh');
    });
    
    // Filter functionality
    $('#question-filter, #user-filter').change(function() {
        $('#replies-table').bootstrapTable('refresh');
    });
});

function queryParams(params) {
    params.question_id = $('#question-filter').val();
    params.user_id = $('#user-filter').val();
    params.search = $('#search-input').val();
    return params;
}

function loadStatistics() {
    $.get('{{ route("admin.helpdesk.replies.dashboard") }}', function(data) {
        $('#total-replies').text(data.total_replies);
        $('#recent-replies').text(data.recent_replies.length);
    });
}

function loadQuestions() {
    $.get('{{ route("admin.helpdesk.questions.index") }}', function(data) {
        let options = '<option value="">All Questions</option>';
        data.rows.forEach(function(question) {
            options += `<option value="${question.id}">${question.title}</option>`;
        });
        $('#question-filter').html(options);
    });
}

function loadUsers() {
    // This would need a users API endpoint
    $('#user-filter').html('<option value="">All Users</option>');
}

// Action events
window.replyAction = {
    'click .view-reply': function (e, value, row) {
        window.open(`{{ url('admin/helpdesk/replies') }}/${row.id}`, '_blank');
    },
    'click .edit-reply': function (e, value, row) {
        window.open(`{{ url('admin/helpdesk/replies') }}/${row.id}/edit`, '_blank');
    },
    'click .delete-reply': function (e, value, row) {
        if (confirm('Are you sure you want to delete this reply?')) {
            deleteReply(row.id);
        }
    }
};

function deleteReply(id) {
    $.ajax({
        url: `{{ url('admin/helpdesk/replies') }}/${id}`,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                $('#replies-table').bootstrapTable('refresh');
                loadStatistics();
                showAlert('success', response.message);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            showAlert('error', 'An error occurred while deleting reply');
        }
    });
}

function showAlert(type, message) {
    let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    let alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`;
    
    $('.content-wrapper').prepend(alertHtml);
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endsection
