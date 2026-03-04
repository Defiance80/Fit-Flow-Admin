@extends('layouts.app')

@section('title')
    {{ __('Create Categories') }}
@endsection

@section('page-title')
    <h1 class="mb-0">@yield('title')</h1>

    <div class="section-header-button ml-auto">
        <a class="btn btn-primary" href="{{ route('categories.create') }}">
            + {{ __('Add Category') }}
        </a>
    </div> @endsection

@section('main')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="text-left col-md-12">
                            <a href="{{ route('categories.order') }}">+ {{ __('Set Order of Categories') }} </a>
                        </div>
                         {{-- Show Trash Button --}}
                        <div class="mt-4 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{ __('All') }}</a></b> {{ __('|') }} <a href="#" class="ml-2 table-list-type" data-id="1">{{ __('Trashed') }}</a>
                        </div>
                    </div>
                    <div id="toolbar"></div>
                    <div class="table-responsive">
                        <table class="table table-border" id="table_list" data-toggle="table" 
                            data-url="{{ route('categories.show', 0) }}" data-pagination="true"
                            data-side-pagination="server" data-search="true" data-toolbar="#toolbar"
                            data-page-list="[5, 10, 20, 50, 100]" data-show-columns="true" data-show-refresh="true"
                            data-sort-name="id" data-sort-order="desc" data-show-columns="true"
                            data-status-column="status" data-query-params="categoriesQueryParams" data-mobile-responsive="true"
                            data-table="categories" data-show-export="true"
                            data-export-data-type="all"
                            data-export-options='{"fileName": "category-list","ignoreColumn": ["operate", "image"]}'
                            data-export-types='["csv", "excel", "pdf"]'>
                            <thead>
                                <tr>
                                    <th data-field="id" data-align="center" data-sortable="true" data-escape="true">{{ __('ID') }}</th>
                                    <th data-field="name" data-sortable="true" data-formatter="categoryNameFormatter" data-escape="false">{{ __('Name') }}</th>
                                    <th data-field="image" data-align="center" data-formatter="imageFormatter" data-escape="false">{{ __('Image') }}</th>
                                    <th data-field="subcategories_count" data-align="center" data-formatter="subCategoryFormatter" data-escape="false">{{ __('Subcategories') }}</th>
                                    {{-- <th scope="col" data-field="custom_fields_count" data-align="center" data-sortable="false" data-formatter="customFieldFormatter">{{ __('Custom Fields') }}</th> --}}
                                    <th data-field="status" data-align="center" data-sortable="true" data-formatter="statusFormatter" data-escape="false">{{ __('Active') }}</th>
                                    <th data-field="operate" data-align="" data-formatter="actionColumnFormatter" data-escape="false">{{ __('Action') }}</th>
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
            // Handle All/Trashed tab switching
            $('.table-list-type').on('click', function(e){
                e.preventDefault();
                $('.table-list-type').removeClass('active');
                $(this).addClass('active');
                
                const isTrashed = $(this).data('id') === 1;
                
                // Hide/Show Active column based on tab
                if (isTrashed) {
                    $('#table_list').bootstrapTable('hideColumn', 'status');
                } else {
                    $('#table_list').bootstrapTable('showColumn', 'status');
                }
                
                // Refresh table
                $('#table_list').bootstrapTable('refresh');
            });
            
            // Export formatters to clean HTML for CSV/Excel export
            $('#table_list').on('export.bs.table', function (e, name, args) {
                // Clean HTML from formatters for export
                args.data.forEach(function(row) {
                    // Clean category name (remove button HTML)
                    if (row.name) {
                        row.name = row.name.replace(/<[^>]*>/g, '').trim();
                    }
                    // Clean image field (just show URL or "-")
                    if (row.image) {
                        const imgMatch = row.image.match(/href=['"]([^'"]+)['"]/);
                        row.image = imgMatch ? imgMatch[1] : (row.image.includes('http') ? row.image : '-');
                    }
                    // Clean subcategories count
                    if (row.subcategories_count !== undefined) {
                        row.subcategories_count = row.subcategories_count || 0;
                    }
                    // Clean status (convert to Yes/No)
                    if (row.status !== undefined) {
                        row.status = (row.status == 1 || row.status == 'true') ? 'Yes' : 'No';
                    }
                });
            });
        });
    </script>
@endsection

