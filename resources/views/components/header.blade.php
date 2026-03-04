<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
                <i class="fas fa-bars"></i>
            </a></li>
        </ul>
    </form>
    
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                @if(auth()->user() && auth()->user()->avatar)
                    <img alt="image" src="{{ asset('storage/'.auth()->user()->avatar) }}" class="rounded-circle mr-1">
                @else
                    <div class="avatar-initial rounded-circle mr-1" style="background: #0D9488; color: white; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                @endif
                <div class="d-sm-none d-lg-inline-block">{{ auth()->user()->name ?? 'Admin' }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">{{ __('Logged in as') }} {{ auth()->user()->name ?? 'Admin' }}</div>
                <a href="{{ route('profile.edit') ?? '#' }}" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> {{ __('Profile') }}
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item has-icon text-danger" style="background: none; border: none; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
