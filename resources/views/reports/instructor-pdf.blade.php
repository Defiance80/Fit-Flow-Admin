<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Trainer Report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .filters h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-primary {
            background-color: #007bff;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Trainer Report') }}</h1>
        <p>Generated on: {{ $generated_at }}</p>
        <p>Total Trainers: {{ $trainers->count() }}</p>
    </div>
    @if(!empty($filters))
    <div class="filters">
        <h3>{{ __('Applied Filters:') }}</h3>
        @if(isset($filters['date_from']) && $filters['date_from'])
        <div class="filter-item">
            <span class="filter-label">{{ __('From:') }}</span> {{ $filters['date_from'] }}
        </div>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
        <div class="filter-item">
            <span class="filter-label">{{ __('To:') }}</span> {{ $filters['date_to'] }}
        </div>
        @endif
        @if(isset($filters['trainer_id']) && $filters['trainer_id'])
        <div class="filter-item">
            <span class="filter-label">{{ __('Trainer:') }}</span> {{ $filters['trainer_name'] ?? $filters['trainer_id'] }}
        </div>
        @endif
        @if(isset($filters['trainer_type']) && $filters['trainer_type'])
        <div class="filter-item">
            <span class="filter-label">{{ __('Type:') }}</span> {{ ucfirst($filters['trainer_type']) }}
        </div>
        @endif
        @if(isset($filters['status']) && $filters['status'])
        <div class="filter-item">
            <span class="filter-label">{{ __('Status:') }}</span> {{ ucfirst($filters['status']) }}
        </div>
        @endif
    </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>{{ __('Trainer Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Total Courses') }}</th>
                <th>{{ __('Total Enrollments') }}</th>
                <th>{{ __('Total Revenue') }}</th>
                <th>{{ __('Join Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trainers as $trainer)
            <tr>
                <td>{{ $trainer->name }}</td>
                <td>{{ $trainer->email ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-{{ $trainer->trainer_details->type === 'individual' ? 'info' : 'primary' }}">
                        {{ ucfirst($trainer->trainer_details->type ?? 'N/A') }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $trainer->trainer_details->status === 'approved' ? 'success' : ($trainer->trainer_details->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($trainer->trainer_details->status ?? 'N/A') }}
                    </span>
                </td>
                <td>{{ $trainer->total_courses ?? 0 }}</td>
                <td>{{ $trainer->total_enrollments ?? 0 }}</td>
                <td>{{ $currency_symbol ?? '₹' }}{{ number_format($trainer->total_revenue ?? 0, 2) }}</td>
                <td>{{ $trainer->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #666;">{{ __('No trainers found matching the selected criteria.') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('This report was generated automatically by the Learning Management System') }}</p>
        <p>© {{ date('Y') }} - All rights reserved</p>
    </div>
</body>
</html>

