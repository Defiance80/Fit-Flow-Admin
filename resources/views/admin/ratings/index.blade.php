@extends('layouts.app')

@section('title', 'Ratings Management')

@section('main')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Ratings Management') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></div>
            <div class="breadcrumb-item">{{ __('Ratings Management') }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length">
                                <h4 class="card-title">{{ __('All Ratings & Reviews') }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 d-flex justify-content-end">
                            <button class="btn btn-primary btn-sm" onclick="refreshRatings()">
                                <i class="fas fa-sync-alt"></i> {{ __('Refresh') }}
                            </button>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('admin.ratings.index') }}">
                                <div class="row">
                                    <div class="col-md-2">
                                        <select name="type" class="form-control">
                                            <option value="">{{ __('All Types') }}</option>
                                            <option value="course" {{ request('type') == 'course' ? 'selected' : '' }}>{{ __('Courses') }}</option>
                                            <option value="trainer" {{ request('type') == 'trainer' ? 'selected' : '' }}>{{ __('Trainers') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="rating" class="form-control">
                                            <option value="">{{ __('All Ratings') }}</option>
                                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>{{ __('5 Stars') }}</option>
                                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>{{ __('4 Stars') }}</option>
                                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>{{ __('3 Stars') }}</option>
                                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>{{ __('2 Stars') }}</option>
                                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>{{ __('1 Star') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control" placeholder="Search by user, review, or item..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <a href="{{ route('admin.ratings.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i> {{ __('Reset Filters') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">{{ __('Total Ratings') }}</h6>
                                            <h3>{{ $stats['total_ratings'] }}</h3>
                                        </div>
                                        <i class="fas fa-star fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">{{ __('Course Ratings') }}</h6>
                                            <h3>{{ $stats['course_ratings'] }}</h3>
                                        </div>
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">{{ __('Trainer Ratings') }}</h6>
                                            <h3>{{ $stats['trainer_ratings'] }}</h3>
                                        </div>
                                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">{{ __('Average Rating') }}</h6>
                                            <h3>{{ $stats['average_rating'] }}</h3>
                                        </div>
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('Rating Breakdown') }}</h6>
                                    <div class="row text-center">
                                        <div class="col-2">
                                            <small>5★</small><br>
                                            <strong>{{ $stats['rating_breakdown']['5_stars'] }}</strong>
                                        </div>
                                        <div class="col-2">
                                            <small>4★</small><br>
                                            <strong>{{ $stats['rating_breakdown']['4_stars'] }}</strong>
                                        </div>
                                        <div class="col-2">
                                            <small>3★</small><br>
                                            <strong>{{ $stats['rating_breakdown']['3_stars'] }}</strong>
                                        </div>
                                        <div class="col-2">
                                            <small>2★</small><br>
                                            <strong>{{ $stats['rating_breakdown']['2_stars'] }}</strong>
                                        </div>
                                        <div class="col-2">
                                            <small>1★</small><br>
                                            <strong>{{ $stats['rating_breakdown']['1_star'] }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ratings Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th>{{ __('Review') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ratings as $rating)
                                <tr>
                                    <td>{{ $rating->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $rating->user->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $rating->user->email ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($rating->rateable_type == 'App\\Models\\Course\\Course')
                                            <span class="badge badge-info">{{ __('Course') }}</span>
                                        @elseif($rating->rateable_type == 'App\\Models\\Trainer')
                                            <span class="badge badge-warning">{{ __('Trainer') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('Unknown') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            @if($rating->rateable)
                                                <strong>{{ $rating->rateable->title ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">ID: {{ $rating->rateable_id }}</small>
                                            @else
                                                <strong class="text-muted">{{ __('Item Deleted') }}</strong><br>
                                                <small class="text-muted">ID: {{ $rating->rateable_id }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="mr-1">{{ $rating->rating }}</span>
                                            <div class="stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $rating->rating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-muted"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($rating->review)
                                            <div class="review-text" style="max-width: 200px;">
                                                {{ \Illuminate\Support\Str::limit($rating->review, 100) }}
                                                @if(strlen($rating->review) > 100)
                                                    <a href="#" onclick="showFullReview({{ $rating->id }})" class="text-primary">{{ __('Read more') }}</a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('No review') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $rating->created_at->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $rating->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.ratings.show', $rating->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-star fa-3x text-muted"></i>
                                            <h5 class="mt-2">{{ __('No ratings found') }}</h5>
                                            <p class="text-muted">{{ __('There are no ratings matching your criteria.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($ratings->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $ratings->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Full Review') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fullReviewContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
function refreshRatings() {
    location.reload();
}

function showFullReview(ratingId) {
    // This would typically fetch the full review via AJAX
    // For now, we'll show a placeholder
    document.getElementById('fullReviewContent').innerHTML = '<p>Loading full review...</p>';
    $('#reviewModal').modal('show');
}

</script>
@endpush
