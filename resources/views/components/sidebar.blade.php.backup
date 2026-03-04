<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('img/fitflow-logo.png') }}" alt="Fit Flow" class="img-fluid" style="max-height: 45px;">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('dashboard') }}" style="color: var(--success); font-weight: 700;">FF</a>
        </div>
        <ul class="sidebar-menu">

            {{-- Dashboard --}}
            <li class="menu-header">{{ __('Dashboard') }}</li>
            <li class="nav-item {{ $type_menu === 'dashboard' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i><span>{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- Fitness --}}
            <li class="menu-header">{{ __('Fitness') }}</li>

            <li class="nav-item dropdown {{ in_array($type_menu, ['trainers']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-tie"></i><span>{{ __('Trainers') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('trainers') ? 'active' : '' }}"><a class="nav-link" href="{{ route('trainers.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Trainers') }}</a></li>
                    <li class="{{ request()->is('trainers/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('trainers.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Add Trainer') }}</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown {{ in_array($type_menu, ['clients']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-users"></i><span>{{ __('Clients') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('clients') ? 'active' : '' }}"><a class="nav-link" href="{{ route('clients.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Clients') }}</a></li>
                    <li class="{{ request()->is('clients/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('clients.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Add Client') }}</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown {{ in_array($type_menu, ['programs']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-dumbbell"></i><span>{{ __('Programs') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('fitness/programs') ? 'active' : '' }}"><a class="nav-link" href="{{ route('programs.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Programs') }}</a></li>
                    <li class="{{ request()->is('fitness/programs/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('programs.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Create Program') }}</a></li>
                </ul>
            </li>

            <li class="nav-item {{ $type_menu === 'exercises' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('exercises.index') }}">
                    <i class="fas fa-running"></i><span>{{ __('Exercise Library') }}</span>
                </a>
            </li>

            {{-- Health & Nutrition --}}
            <li class="menu-header">{{ __('Health & Nutrition') }}</li>

            <li class="nav-item {{ $type_menu === 'health-metrics' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('health.metrics.index') }}">
                    <i class="fas fa-heartbeat"></i><span>{{ __('Health Metrics') }}</span>
                </a>
            </li>

            <li class="nav-item {{ $type_menu === 'health-alerts' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('health.alerts.index') }}">
                    <i class="fas fa-bell"></i><span>{{ __('Health Alerts') }}</span>
                    @php
                        $unackAlerts = \App\Models\Health\HealthAlert::where('acknowledged', false)->count();
                    @endphp
                    @if($unackAlerts > 0)
                        <span class="badge badge-danger ml-2">{{ $unackAlerts }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item dropdown {{ in_array($type_menu, ['meal-plans']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-utensils"></i><span>{{ __('Meal Plans') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('fitness/meal-plans') ? 'active' : '' }}"><a class="nav-link" href="{{ route('meal-plans.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Plans') }}</a></li>
                    <li class="{{ request()->is('fitness/meal-plans/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('meal-plans.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Create Plan') }}</a></li>
                </ul>
            </li>

            {{-- Scheduling --}}
            <li class="menu-header">{{ __('Scheduling') }}</li>
            <li class="nav-item {{ $type_menu === 'schedules' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('schedules.index') }}">
                    <i class="fas fa-calendar-alt"></i><span>{{ __('Calendar') }}</span>
                </a>
            </li>

            {{-- Business --}}
            <li class="menu-header">{{ __('Business') }}</li>

            <li class="nav-item {{ $type_menu === 'facilities' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('facilities.index') }}">
                    <i class="fas fa-building"></i><span>{{ __('Facilities') }}</span>
                </a>
            </li>

            <li class="nav-item {{ $type_menu === 'subscriptions' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('subscriptions.index') }}">
                    <i class="fas fa-credit-card"></i><span>{{ __('Subscriptions') }}</span>
                </a>
            </li>

            @can('categories-list')
            <li class="nav-item {{ $type_menu === 'categories' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('categories.index') }}">
                    <i class="fas fa-tags"></i><span>{{ __('Categories') }}</span>
                </a>
            </li>
            @endcan

            {{-- Settings --}}
            <li class="menu-header">{{ __('Settings') }}</li>

            @if(auth()->user()->can('roles-list') || auth()->user()->can('staff-list'))
            <li class="nav-item dropdown {{ $type_menu === 'roles' || $type_menu === 'staffs' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-shield"></i><span>{{ __('Staff') }}</span></a>
                <ul class="dropdown-menu">
                    @can('roles-list')<li class="{{ request()->is('roles') ? 'active' : '' }}"><a class="nav-link" href="{{ route('roles.index') }}">{{ __('Roles') }}</a></li>@endcan
                    @can('staff-list')<li class="{{ request()->is('staffs') ? 'active' : '' }}"><a class="nav-link" href="{{ route('staffs.index') }}">{{ __('Staff') }}</a></li>@endcan
                </ul>
            </li>
            @endif

            @if(auth()->user()->can('settings-system-list') || auth()->user()->can('settings-payment-gateway-list'))
            <li class="nav-item dropdown {{ $type_menu === 'settings' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-cog"></i><span>{{ __('Settings') }}</span></a>
                <ul class="dropdown-menu">
                    @can('settings-system-list')<li class="{{ request()->is('system-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.system') }}">{{ __('System') }}</a></li>@endcan
                    @can('settings-firebase-list')<li class="{{ request()->is('firebase-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.firebase') }}">{{ __('Firebase') }}</a></li>@endcan
                    @can('settings-payment-gateway-list')<li class="{{ request()->is('payment-gateway-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.payment-gateway') }}">{{ __('Payments') }}</a></li>@endcan
                    @can('settings-language-list')<li class="{{ request()->is('language-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.language') }}">{{ __('Languages') }}</a></li>@endcan
                    @can('settings-app-list')<li class="{{ request()->is('app-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.app') }}">{{ __('App Settings') }}</a></li>@endcan
                </ul>
            </li>
            @endif

            @if(auth()->user()->can('helpdesk-groups-list'))
            <li class="nav-item dropdown {{ $type_menu === 'help-desk' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-headset"></i><span>{{ __('Help Desk') }}</span></a>
                <ul class="dropdown-menu">
                    <li><a class="nav-link" href="{{ route('groups.index') }}">{{ __('Groups') }}</a></li>
                    <li><a class="nav-link" href="{{ route('admin.helpdesk.questions.index') }}">{{ __('Questions') }}</a></li>
                </ul>
            </li>
            @endif

        </ul>
    </aside>
</div>
