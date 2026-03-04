@extends('layouts.app')

@section('title')
    {{ __('Manage Promo Codes') }}
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
                            {{ __('Create Promo Code') }}
                        </h4>
                        <form class="pt-3 mt-6 create-form" method="POST" action="{{ route('promo-codes.store') }}" data-parsley-validate> @csrf <div class="row">
                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Promo Code') }} <span class="text-danger"> * </span></label>
                                    <input type="text" name="promo_code" placeholder="PROMO10" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('Message') }} <span class="text-danger"> * </span></label>
                                    <input type="text" name="message" placeholder="10% off on your next order" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Start Date') }} <span class="text-danger"> * </span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('End Date') }} <span class="text-danger"> * </span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('No of Users') }} <span class="text-danger"> * </span></label>
                                    <input type="number" name="no_of_users" placeholder="e.g. 10" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Minimum Order Amount') }} <span class="text-danger"> * </span></label>
                                    <input type="number" name="minimum_order_amount" placeholder="e.g. 100" class="form-control" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Discount Type') }} <span class="text-danger"> * </span></label>
                                    <select name="discount_type" class="form-control" required id="discount_type">
                                        <option value="percentage">{{ __('Percentage') }}</option>
                                        <option value="amount">{{ __('Fixed') }}</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label>{{ __('Discount') }} <span class="text-danger"> * </span></label>
                                    <input type="number" name="discount" placeholder="e.g. 10" class="form-control" min="0" max="999999999.99" step="0.01" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-2" id="max_discount_amount_group">
                                    <label>{{ __('Max Discount Amount') }} <span class="text-danger"> * </span></label>
                                    <input type="number" name="max_discount_amount" id="max_discount_amount" placeholder="e.g. 100" class="form-control" min="0.01" max="999999999.99" step="0.01">
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
                            {{ __('List Promo Codes') }}
                        </h4>
                        <div class="col-12 mt-4 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('all') }}</a></b> {{ __('|') }} <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                        </div>
                        <table aria-describedby="mydesc" class="table reorder-table-row" id="table_list"
                            data-table="promo_codes" data-toggle="table" data-status-column="status"
                            data-url="{{ route('promo-codes.show', 0) }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-mobile-responsive="true" data-use-row-attr-func="true"
                            data-maintain-selected="true" data-export-data-type="all"
                            data-export-options='{ "fileName": "{{ __('promo-codes') }}-<?= date('d-m-y') ?>","ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-field="id" data-visible="false" data-escape="true">{{ __('id') }}</th>
                                    <th data-field="no" data-escape="true">{{ __('no.') }}</th>
                                    <th data-field="promo_code" data-escape="true">{{ __('Promo Code') }}</th>
                                    <th data-field="message" data-escape="true">{{ __('Message') }}</th>
                                    <th data-field="status" data-formatter="statusFormatter" data-escape="false" id="status-column">{{ __('Status') }}</th>
                                    <th data-field="operate" data-sortable="false" data-events="promoCodeAction" data-escape="false">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="promoCodeEditModal" tabindex="-1" role="dialog"
            aria-labelledby="promoCodeEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="promoCodeEditModalLabel">{{ __('Edit Promo Code') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <span aria-hidden="true" style="font-size: 1.5rem; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff;">&times;</span>
                        </button>
                    </div>
                    <form class="pt-3 mt-6 edit-form" method="POST" data-parsley-validate id="promoCodeEditForm"> @csrf
                        @method('PUT')
        <div class="modal-body row">
                            <input type="hidden" name="id" id="edit_promo_code_id">

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Promo Code') }} <span class="text-danger"> * </span></label>
                                <input type="text" name="promo_code" id="edit_promo_code" class="form-control" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Message') }} <span class="text-danger"> * </span></label>
                                <input type="text" name="message" id="edit_message" class="form-control" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Start Date') }} <span class="text-danger"> * </span></label>
                                <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('End Date') }} <span class="text-danger"> * </span></label>
                                <input type="date" name="end_date" id="edit_end_date" class="form-control" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('No of Users') }} <span class="text-danger"> * </span></label>
                                <input type="number" name="no_of_users" id="edit_no_of_users" class="form-control" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Minimum Order Amount') }} <span class="text-danger"> * </span></label>
                                <input type="number" name="minimum_order_amount" id="edit_minimum_order_amount" class="form-control" min="0" max="999999999.99" step="0.01" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Discount Type') }} <span class="text-danger"> * </span></label>
                                <select name="discount_type" class="form-control" id="edit_discount_type" required>
                                    <option value="percentage">{{ __('Percentage') }}</option>
                                    <option value="amount">{{ __('Fixed') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('Discount') }} <span class="text-danger"> * </span></label>
                                <input type="number" name="discount" id="edit_discount" class="form-control" min="0" max="999999999.99" step="0.01" required>
                            </div>

                            <div class="form-group col-sm-12 col-md-6" id="edit_max_discount_amount_group">
                                <label>{{ __('Max Discount Amount') }} <span class="text-danger"> * </span></label>
                                <input type="number" name="max_discount_amount" id="edit_max_discount_amount" class="form-control" min="0.01" max="999999999.99" step="0.01" data-minimum-order-field="edit_minimum_order_amount">
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
    </div> @endsection

@section('script')
    <script>
        // Handle All/Trashed tab switching
        let showDeleted = 0;
        $('.table-list-type').on('click', function(e){
            e.preventDefault();
            $('.table-list-type').removeClass('active');
            $(this).addClass('active');
            showDeleted = $(this).data('id') === 1 ? 1 : 0;
            
            // Toggle status column visibility
            toggleStatusColumn();
            
            $('#table_list').bootstrapTable('refresh');
        });

        // Hide status column when viewing trashed items
        $(document).ready(function() {
            // Listen for table refresh/load events
            $('#table_list').on('load-success.bs.table', function() {
                toggleStatusColumn();
            });
        });

        function toggleStatusColumn() {
            const isTrashed = $('.table-list-type.active').data('id') == 1;
            const $table = $('#table_list');
            
            if (isTrashed) {
                // Hide status column when viewing trashed items
                $table.bootstrapTable('hideColumn', 'status');
            } else {
                // Show status column when viewing all items
                $table.bootstrapTable('showColumn', 'status');
            }
        }

        // Attach filters to table query params
        function queryParams(params) {
            params.show_deleted = showDeleted;
            return params;
        }
    </script>
    <script>
    // Define the event handler for Bootstrap Table before document ready
    window.promoCodeAction = {
        'click .edit-data': function (e, value, row) {
            
            // Clear all previous validation errors and reset form state (using common function)
            if (typeof window.clearEditFormValidationErrors === 'function') {
                window.clearEditFormValidationErrors($('#promoCodeEditForm'));
            }
            
            // Populate all form fields with row data
            $('#edit_promo_code_id').val(row.id);
            $('#edit_promo_code').val(row.promo_code);
            $('#edit_message').val(row.message);
            
            // Format dates for HTML date input (YYYY-MM-DD)
            const startDate = row.start_date ? new Date(row.start_date).toISOString().split('T')[0] : '';
            const endDate = row.end_date ? new Date(row.end_date).toISOString().split('T')[0] : '';
            $('#edit_start_date').val(startDate);
            $('#edit_end_date').val(endDate);
            $('#edit_no_of_users').val(row.no_of_users);
            $('#edit_minimum_order_amount').val(row.minimum_order_amount);
            $('#edit_discount_type').val(row.discount_type);
            
            // Handle discount value - parse and validate to prevent extremely large numbers
            let discountValue = parseFloat(row.discount);
            if (isNaN(discountValue) || discountValue < 0) {
                discountValue = 0;
            }
            // If percentage, max is 100; if amount, max is 999999999.99
            if (row.discount_type === 'percentage' && discountValue > 100) {
                discountValue = 100;
            } else if (row.discount_type === 'amount' && discountValue > 999999999.99) {
                discountValue = 999999999.99;
            }
            $('#edit_discount').val(discountValue);
            
            // Handle max_discount_amount value - parse and validate
            let maxDiscountValue = parseFloat(row.max_discount_amount);
            if (isNaN(maxDiscountValue) || maxDiscountValue < 0) {
                maxDiscountValue = '';
            } else if (maxDiscountValue > 999999999.99) {
                maxDiscountValue = 999999999.99;
            }
            $('#edit_max_discount_amount').val(maxDiscountValue || '');
            
            // Handle discount type change
            if (row.discount_type === 'amount') {
                $('#edit_max_discount_amount_group').addClass('d-none');
                $('#edit_max_discount_amount').val('');
                // For amount type, set max based on minimum order amount (must be less than minimum order)
                const minimumOrder = parseFloat(row.minimum_order_amount) || 0;
                if (minimumOrder > 0) {
                    // Set max to minimumOrder - 0.01 to ensure discount is strictly less than minimum order amount
                    const maxDiscount = (minimumOrder - 0.01).toFixed(2);
                    $('#edit_discount').attr('max', maxDiscount);
                } else {
                    $('#edit_discount').attr('max', '999999999.99');
                }
            } else {
                $('#edit_max_discount_amount_group').removeClass('d-none');
                // For percentage type, set max to 100
                $('#edit_discount').attr('max', '100');
            }
            
            // Set form action URL
            $('#promoCodeEditForm').attr('action', '{{ route("promo-codes.update", ":id") }}'.replace(':id', row.id));
            
            // Apply end_date validation after form is populated
            setTimeout(function() {
                const today = new Date().toISOString().split('T')[0];
                const startDate = $('#edit_start_date').val();
                // Set min to the later of start_date or today
                const minDate = startDate && startDate > today ? startDate : today;
                $('#edit_end_date').attr('min', minDate);
                
                // If end_date is in the past, clear it
                if ($('#edit_end_date').val() && $('#edit_end_date').val() < today) {
                    $('#edit_end_date').val('');
                }
            }, 100);
            
        }
    };

    $(document).ready(function () {
        // Hide/Show max discount based on discount type
        $('#discount_type').on('change', function () {
            const discountType = $(this).val();
            const $discountInput = $('input[name="discount"]');
            const $maxDiscountInput = $('#max_discount_amount_group input');
            const $minimumOrderInput = $('input[name="minimum_order_amount"]');
            
            if (discountType === 'amount') {
                $('#max_discount_amount_group').addClass('d-none');
                $maxDiscountInput.val('');
                // For amount type, set max based on minimum order amount
                updateDiscountMaxForAmount($discountInput, $minimumOrderInput);
            } else {
                $('#max_discount_amount_group').removeClass('d-none');
                // For percentage type, set max to 100
                $discountInput.attr('max', '100');
            }
        }).trigger('change');
        
        // Update discount max when minimum order amount changes (for fixed amount type)
        $('input[name="minimum_order_amount"]').on('input change', function() {
            const discountType = $('#discount_type').val();
            const $maxDiscountInput = $('#max_discount_amount');
            
            if (discountType === 'amount') {
                const $discountInput = $('input[name="discount"]');
                updateDiscountMaxForAmount($discountInput, $(this));
            } else if (discountType === 'percentage') {
                // For percentage type, validate minimum order amount > max discount amount
                const minimumOrder = parseFloat($(this).val()) || 0;
                const maxDiscount = parseFloat($maxDiscountInput.val()) || 0;
                
                if (maxDiscount > 0 && minimumOrder <= maxDiscount) {
                    $(this).addClass('is-invalid');
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Minimum Order Amount must be greater than Max Discount Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        // Validate discount when it changes (for fixed amount type)
        $('input[name="discount"]').on('input change', function() {
            if ($('#discount_type').val() === 'amount') {
                const minimumOrder = parseFloat($('input[name="minimum_order_amount"]').val()) || 0;
                const discount = parseFloat($(this).val()) || 0;
                
                if (discount >= minimumOrder) {
                    $(this).addClass('is-invalid');
                    // Show error message
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Discount must be less than Minimum Order Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        // Validate max discount amount when it changes (for percentage type)
        $('#max_discount_amount').on('input change', function() {
            if ($('#discount_type').val() === 'percentage') {
                const minimumOrder = parseFloat($('input[name="minimum_order_amount"]').val()) || 0;
                const maxDiscount = parseFloat($(this).val()) || 0;
                
                if (maxDiscount > 0 && minimumOrder <= maxDiscount) {
                    $(this).addClass('is-invalid');
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Max Discount Amount must be less than Minimum Order Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        
        // Helper function to update discount max for amount type
        function updateDiscountMaxForAmount($discountInput, $minimumOrderInput) {
            const minimumOrder = parseFloat($minimumOrderInput.val()) || 0;
            if (minimumOrder > 0) {
                // Set max to minimumOrder - 0.01 to ensure discount is strictly less than minimum order amount
                const maxDiscount = (minimumOrder - 0.01).toFixed(2);
                $discountInput.attr('max', maxDiscount);
            } else {
                $discountInput.attr('max', '999999999.99');
            }
        }

        // Prevent past dates for start_date
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);

        // Ensure end_date is not earlier than start_date
        $('#start_date').on('change', function () {
            $('#end_date').attr('min', $(this).val());
        });

        if ($('#start_date').val()) {
            $('#end_date').attr('min', $('#start_date').val());
        }
        
        // Custom validation before form submission (create form)
        $('.create-form').on('submit', function(e) {
            const discountType = $('#discount_type').val();
            let isValid = true;
            
            if (discountType === 'percentage') {
                const minimumOrder = parseFloat($('input[name="minimum_order_amount"]').val()) || 0;
                const maxDiscount = parseFloat($('#max_discount_amount').val()) || 0;
                
                if (maxDiscount > 0 && minimumOrder <= maxDiscount) {
                    isValid = false;
                    $('input[name="minimum_order_amount"]').addClass('is-invalid');
                    let errorMsg = $('input[name="minimum_order_amount"]').siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Minimum Order Amount must be greater than Max Discount Amount.</div>');
                        $('input[name="minimum_order_amount"]').after(errorMsg);
                    }
                    showErrorToast('Minimum Order Amount must be greater than Max Discount Amount.');
                }
            } else if (discountType === 'amount') {
                const minimumOrder = parseFloat($('input[name="minimum_order_amount"]').val()) || 0;
                const discount = parseFloat($('input[name="discount"]').val()) || 0;
                
                if (discount >= minimumOrder) {
                    isValid = false;
                    $('input[name="discount"]').addClass('is-invalid');
                    let errorMsg = $('input[name="discount"]').siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Discount must be less than Minimum Order Amount.</div>');
                        $('input[name="discount"]').after(errorMsg);
                    }
                    showErrorToast('Discount must be less than Minimum Order Amount.');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    });
     // Toggle max discount field based on discount type
     $('#edit_discount_type').on('change', function () {
            const discountType = $(this).val();
            const $discountInput = $('#edit_discount');
            const $maxDiscountInput = $('#edit_max_discount_amount');
            const $minimumOrderInput = $('#edit_minimum_order_amount');
            
            if (discountType === 'amount') {
                $('#edit_max_discount_amount_group').addClass('d-none');
                $maxDiscountInput.val('');
                // For amount type, set max based on minimum order amount
                updateEditDiscountMaxForAmount($discountInput, $minimumOrderInput);
            } else {
                $('#edit_max_discount_amount_group').removeClass('d-none');
                // For percentage type, set max to 100
                $discountInput.attr('max', '100');
            }
        });
        
        // Update discount max when minimum order amount changes (for fixed amount type in edit form)
        $('#edit_minimum_order_amount').on('input change', function() {
            const discountType = $('#edit_discount_type').val();
            const $maxDiscountInput = $('#edit_max_discount_amount');
            
            if (discountType === 'amount') {
                const $discountInput = $('#edit_discount');
                updateEditDiscountMaxForAmount($discountInput, $(this));
            } else if (discountType === 'percentage') {
                // For percentage type, validate minimum order amount > max discount amount
                const minimumOrder = parseFloat($(this).val()) || 0;
                const maxDiscount = parseFloat($maxDiscountInput.val()) || 0;
                
                if (maxDiscount > 0 && minimumOrder <= maxDiscount) {
                    $(this).addClass('is-invalid');
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Minimum Order Amount must be greater than Max Discount Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        // Validate discount when it changes (for fixed amount type in edit form)
        $('#edit_discount').on('input change', function() {
            if ($('#edit_discount_type').val() === 'amount') {
                const minimumOrder = parseFloat($('#edit_minimum_order_amount').val()) || 0;
                const discount = parseFloat($(this).val()) || 0;
                
                if (discount >= minimumOrder) {
                    $(this).addClass('is-invalid');
                    // Show error message
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Discount must be less than Minimum Order Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        // Validate max discount amount when it changes (for percentage type in edit form)
        $('#edit_max_discount_amount').on('input change', function() {
            if ($('#edit_discount_type').val() === 'percentage') {
                const minimumOrder = parseFloat($('#edit_minimum_order_amount').val()) || 0;
                const maxDiscount = parseFloat($(this).val()) || 0;
                
                if (maxDiscount > 0 && minimumOrder <= maxDiscount) {
                    $(this).addClass('is-invalid');
                    let errorMsg = $(this).siblings('.invalid-feedback');
                    if (errorMsg.length === 0) {
                        errorMsg = $('<div class="invalid-feedback">Max Discount Amount must be less than Minimum Order Amount.</div>');
                        $(this).after(errorMsg);
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                }
            }
        });
        
        
        // Helper function to update discount max for amount type in edit form
        function updateEditDiscountMaxForAmount($discountInput, $minimumOrderInput) {
            const minimumOrder = parseFloat($minimumOrderInput.val()) || 0;
            if (minimumOrder > 0) {
                // Set max to minimumOrder - 0.01 to ensure discount is strictly less than minimum order amount
                const maxDiscount = (minimumOrder - 0.01).toFixed(2);
                $discountInput.attr('max', maxDiscount);
            } else {
                $discountInput.attr('max', '999999999.99');
            }
        }

        // Prevent past start_date
        const today = new Date().toISOString().split('T')[0];
        $('#edit_start_date').attr('min', today);
        
        // Prevent past end_date - set minimum to today or start_date (whichever is later)
        $('#edit_end_date').attr('min', today);

        // Ensure end_date >= start_date and not in the past
        $('#edit_start_date').on('change', function () {
            const startDate = $(this).val();
            const todayDate = new Date().toISOString().split('T')[0];
            // Set min to the later of start_date or today
            const minDate = startDate > todayDate ? startDate : todayDate;
            $('#edit_end_date').attr('min', minDate);
            
            // If current end_date is before the new minimum, clear it
            if ($('#edit_end_date').val() && $('#edit_end_date').val() < minDate) {
                $('#edit_end_date').val('');
            }
        });
        
        // Also check on page load if start_date is already set
        if ($('#edit_start_date').val()) {
            const startDate = $('#edit_start_date').val();
            const todayDate = new Date().toISOString().split('T')[0];
            const minDate = startDate > todayDate ? startDate : todayDate;
            $('#edit_end_date').attr('min', minDate);
        }

        // Clear validation errors when modal is closed (using common function)
        // Note: This is also handled automatically by common.js, but keeping it here for specific modal
        $('#promoCodeEditModal').on('hidden.bs.modal', function () {
            if (typeof window.clearEditFormValidationErrors === 'function') {
                window.clearEditFormValidationErrors($('#promoCodeEditForm'));
            }
        });



</script>
@endsection


