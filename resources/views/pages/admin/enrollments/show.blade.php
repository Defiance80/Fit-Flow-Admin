@extends('layouts.app')

@section('title')
    {{ __('Enrollment Details') }}
@endsection

@section('main')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-graduation-cap"></i> {{ __('Enrollment Details') }}</h1>
            <div class="section-header-button">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Enrollments') }}
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Student Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-user"></i> {{ __('Student Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xl bg-primary text-white">
                                {{ substr($enrollment->order->user->name ?? 'N/A', 0, 1) }}
                            </div>
                            <h5 class="mt-2">{{ $enrollment->order->user->name ?? 'N/A' }}</h5>
                            <p class="text-muted">{{ $enrollment->order->user->email ?? 'N/A' }}</p>
                        </div>
                        
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{ __('Phone') }}:</strong></td>
                                <td>{{ $enrollment->order->user->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Registration Date') }}:</strong></td>
                                <td>{{ $enrollment->order->user->created_at->format('d M Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Total Enrollments') }}:</strong></td>
                                <td>{{ $enrollment->order->user->orders->sum(function($order) { return $order->orderCourses->count(); }) ?? 0 }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Course Information -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-book"></i> {{ __('Course Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <h5>{{ $enrollment->course->title ?? 'N/A' }}</h5>
                        <p class="text-muted">{{ $enrollment->course->description ?? 'N/A' }}</p>
                        
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>{{ __('Instructor') }}:</strong></td>
                                <td>{{ $enrollment->course->user->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Price') }}:</strong></td>
                                <td><strong>₹{{ number_format($enrollment->price) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Duration') }}:</strong></td>
                                <td>{{ $enrollment->course->duration ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Level') }}:</strong></td>
                                <td>{{ ucfirst($enrollment->course->level ?? 'N/A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enrollment Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle"></i> {{ __('Enrollment Details') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('Enrollment ID') }}:</strong></td>
                                        <td>#{{ $enrollment->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('Enrollment Date') }}:</strong></td>
                                        <td>{{ $enrollment->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('Order Number') }}:</strong></td>
                                        <td>#{{ $enrollment->order->order_number ?? $enrollment->order->id }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('Payment Method') }}:</strong></td>
                                        <td><span class="badge badge-info">{{ ucfirst($enrollment->order->payment_method) }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('Order Status') }}:</strong></td>
                                        <td>
                                            @switch($enrollment->order->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">{{ __('Pending') }}</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-success">{{ __('Completed') }}</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($enrollment->order->status) }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Content -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-list"></i> {{ __('Course Content') }}</h4>
                    </div>
                    <div class="card-body">
                        @if($enrollment->course->chapters->count() > 0)
                            <div class="accordion" id="courseChapters">
                                @foreach($enrollment->course->chapters as $index => $chapter)
                                    <div class="card">
                                        <div class="card-header" id="heading{{ $chapter->id }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{{ $chapter->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $chapter->id }}">
                                                    <i class="fas fa-chevron-down"></i> {{ $chapter->title }}
                                                    <span class="badge badge-secondary ml-2">{{ $chapter->lectures->count() }} {{ __('lectures') }}</span>
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapse{{ $chapter->id }}" class="collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $chapter->id }}" data-parent="#courseChapters">
                                            <div class="card-body">
                                                @if($chapter->lectures->count() > 0)
                                                    <div class="list-group">
                                                        @foreach($chapter->lectures as $lecture)
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">{{ $lecture->title }}</h6>
                                                                    <small class="text-muted">{{ $lecture->description ?? '' }}</small>
                                                                </div>
                                                                <div>
                                                                    <span class="badge badge-info">{{ ucfirst($lecture->type) }}</span>
                                                                    @if($lecture->duration)
                                                                        <span class="badge badge-secondary">{{ $lecture->duration }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted">{{ __('No lectures available in this chapter.') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-book fa-3x mb-3"></i>
                                <p>{{ __('No course content available.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Enrollment Actions -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-cogs"></i> {{ __('Actions') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.orders.show', $enrollment->order->id) }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-shopping-cart"></i> {{ __('View Order Details') }}
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.tracking.show', $enrollment->id) }}" class="btn btn-info btn-block">
                                    <i class="fas fa-chart-line"></i> {{ __('View Progress') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
@endpush
