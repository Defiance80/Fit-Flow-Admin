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

            {{-- 1. DASHBOARD --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Dashboard') }}</li>
            <li class="nav-item {{ \ === 'dashboard' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i><span>{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- 2. FITNESS --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Fitness') }}</li>

            @if(Route::has('trainers.index'))
            <li class="nav-item dropdown {{ in_array(\, ['trainers']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-tie"></i><span>{{ __('Trainers') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('trainers') ? 'active' : '' }}"><a class="nav-link" href="{{ route('trainers.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Trainers') }}</a></li>
                    @if(Route::has('trainers.create'))
                    <li class="{{ request()->is('trainers/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('trainers.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Add Trainer') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(Route::has('clients.index'))
            <li class="nav-item dropdown {{ in_array(\, ['clients']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-users"></i><span>{{ __('Clients') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('clients') ? 'active' : '' }}"><a class="nav-link" href="{{ route('clients.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Clients') }}</a></li>
                    @if(Route::has('clients.create'))
                    <li class="{{ request()->is('clients/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('clients.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Add Client') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(Route::has('programs.index'))
            <li class="nav-item dropdown {{ in_array(\, ['programs']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-dumbbell"></i><span>{{ __('Programs') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('fitness/programs') ? 'active' : '' }}"><a class="nav-link" href="{{ route('programs.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Programs') }}</a></li>
                    @if(Route::has('programs.create'))
                    <li class="{{ request()->is('fitness/programs/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('programs.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Create Program') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(Route::has('exercises.index'))
            <li class="nav-item {{ \ === 'exercises' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('exercises.index') }}">
                    <i class="fas fa-running"></i><span>{{ __('Exercise Library') }}</span>
                </a>
            </li>
            @endif

            {{-- 3. HEALTH & NUTRITION --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Health & Nutrition') }}</li>

            @if(Route::has('health.metrics.index'))
            <li class="nav-item {{ \ === 'health-metrics' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('health.metrics.index') }}">
                    <i class="fas fa-heartbeat"></i><span>{{ __('Health Metrics') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('health.alerts.index'))
            <li class="nav-item {{ \ === 'health-alerts' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('health.alerts.index') }}">
                    <i class="fas fa-bell"></i><span>{{ __('Health Alerts') }}</span>
                    @php
                        try {
                            \ = \\App\\Models\\Health\\HealthAlert::where('acknowledged', false)->count();
                        } catch (\\Exception \) {
                            \ = 0;
                        }
                    @endphp
                    @if(\ > 0)
                        <span class="badge badge-danger ml-2">{{ \ }}</span>
                    @endif
                </a>
            </li>
            @endif

            @if(Route::has('meal-plans.index'))
            <li class="nav-item dropdown {{ in_array(\, ['meal-plans']) ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-utensils"></i><span>{{ __('Meal Plans') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('fitness/meal-plans') ? 'active' : '' }}"><a class="nav-link" href="{{ route('meal-plans.index') }}"><i class="fas fa-list mr-1"></i> {{ __('All Plans') }}</a></li>
                    @if(Route::has('meal-plans.create'))
                    <li class="{{ request()->is('fitness/meal-plans/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('meal-plans.create') }}"><i class="fas fa-plus mr-1"></i> {{ __('Create Plan') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- 4. SCHEDULING --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Scheduling') }}</li>
            @if(Route::has('schedules.index'))
            <li class="nav-item {{ \ === 'schedules' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('schedules.index') }}">
                    <i class="fas fa-calendar-alt"></i><span>{{ __('Calendar') }}</span>
                </a>
            </li>
            @endif

            {{-- 5. MANAGEMENT (Original eLMS) --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Management') }}</li>
            
            @if(Route::has('categories.index'))
            @can('categories-list')
            <li class="nav-item {{ \ === 'categories' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('categories.index') }}">
                    <i class="fas fa-tags"></i><span>{{ __('Categories') }}</span>
                </a>
            </li>
            @endcan
            @endif

            @if(Route::has('faqs.index'))
            <li class="nav-item {{ \ === 'faqs' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('faqs.index') }}">
                    <i class="fas fa-question-circle"></i><span>{{ __('FAQ') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('pages.index'))
            <li class="nav-item {{ \ === 'pages' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pages.index') }}">
                    <i class="fas fa-file-alt"></i><span>{{ __('Pages') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('admin.certificates.index'))
            <li class="nav-item {{ \ === 'certificates' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.certificates.index') }}">
                    <i class="fas fa-certificate"></i><span>{{ __('Certificates') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('notifications.index'))
            <li class="nav-item {{ \ === 'notifications' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('notifications.index') }}">
                    <i class="fas fa-bell"></i><span>{{ __('Notifications') }}</span>
                </a>
            </li>
            @endif

            {{-- 6. COURSE MANAGEMENT --}}
            @if(Route::has('courses.index') || Route::has('course-chapters.index'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Course Management') }}</li>
            
            @if(Route::has('courses.index'))
            <li class="nav-item dropdown {{ \ === 'courses' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-graduation-cap"></i><span>{{ __('Courses') }}</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('courses') ? 'active' : '' }}"><a class="nav-link" href="{{ route('courses.index') }}">{{ __('All Courses') }}</a></li>
                    @if(Route::has('courses.create'))
                    <li class="{{ request()->is('courses/create') ? 'active' : '' }}"><a class="nav-link" href="{{ route('courses.create') }}">{{ __('Add Course') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(Route::has('course-chapters.index'))
            <li class="nav-item {{ \ === 'course-chapters' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('course-chapters.index') }}">
                    <i class="fas fa-book-open"></i><span>{{ __('Course Chapters') }}</span>
                </a>
            </li>
            @endif
            @endif

            {{-- 7. USERS & INSTRUCTORS --}}
            @if(Route::has('admin.users.index') || Route::has('instructor.index'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Users & Instructors') }}</li>
            
            @if(Route::has('admin.users.index'))
            <li class="nav-item {{ \ === 'users' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users"></i><span>{{ __('Users') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('instructor.index'))
            <li class="nav-item {{ \ === 'instructors' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('instructor.index') }}">
                    <i class="fas fa-chalkboard-teacher"></i><span>{{ __('Instructors') }}</span>
                </a>
            </li>
            @endif
            @endif

            {{-- 8. BUSINESS --}}
            <li class="menu-header" style="color: #0D9488;">{{ __('Business') }}</li>

            @if(Route::has('facilities.index'))
            <li class="nav-item {{ \ === 'facilities' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('facilities.index') }}">
                    <i class="fas fa-building"></i><span>{{ __('Facilities') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('subscriptions.index'))
            <li class="nav-item {{ \ === 'subscriptions' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('subscriptions.index') }}">
                    <i class="fas fa-credit-card"></i><span>{{ __('Subscriptions') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('admin.enrollments.index'))
            <li class="nav-item {{ \ === 'enrollments' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.enrollments.index') }}">
                    <i class="fas fa-user-graduate"></i><span>{{ __('Enrollments') }}</span>
                </a>
            </li>
            @endif

            @if(Route::has('admin.assignments.index'))
            <li class="nav-item {{ \ === 'assignments' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.assignments.index') }}">
                    <i class="fas fa-tasks"></i><span>{{ __('Assignment Management') }}</span>
                </a>
            </li>
            @endif

            {{-- 9. HOME SCREEN MANAGEMENT --}}
            @if(Route::has('sliders.index'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Home Screen Management') }}</li>
            
            <li class="nav-item {{ \ === 'sliders' ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('sliders.index') }}">
                    <i class="fas fa-images"></i><span>{{ __('Sliders') }}</span>
                </a>
            </li>
            @endif

            {{-- 10. REPORTS --}}
            @if(Route::has('reports.revenue') || Route::has('reports.course') || Route::has('reports.enrollment'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Reports') }}</li>
            
            <li class="nav-item dropdown {{ \ === 'reports' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-bar"></i><span>{{ __('Reports') }}</span></a>
                <ul class="dropdown-menu">
                    @if(Route::has('reports.revenue'))
                    <li class="{{ request()->is('reports/revenue') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.revenue') }}">{{ __('Revenue') }}</a></li>
                    @endif
                    @if(Route::has('reports.course'))
                    <li class="{{ request()->is('reports/course') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.course') }}">{{ __('Courses') }}</a></li>
                    @endif
                    @if(Route::has('reports.enrollment'))
                    <li class="{{ request()->is('reports/enrollment') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.enrollment') }}">{{ __('Enrollments') }}</a></li>
                    @endif
                    @if(Route::has('reports.instructor'))
                    <li class="{{ request()->is('reports/instructor') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.instructor') }}">{{ __('Instructors') }}</a></li>
                    @endif
                    @if(Route::has('reports.commission'))
                    <li class="{{ request()->is('reports/commission') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.commission') }}">{{ __('Commission') }}</a></li>
                    @endif
                    @if(Route::has('reports.sales'))
                    <li class="{{ request()->is('reports/sales') ? 'active' : '' }}"><a class="nav-link" href="{{ route('reports.sales') }}">{{ __('Sales') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- 11. STAFF MANAGEMENT --}}
            @if(auth()->user()->can('roles-list') || auth()->user()->can('staff-list'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Staff Management') }}</li>
            
            <li class="nav-item dropdown {{ \ === 'roles' || \ === 'staffs' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-shield"></i><span>{{ __('Staff') }}</span></a>
                <ul class="dropdown-menu">
                    @can('roles-list')
                    @if(Route::has('roles.index'))
                    <li class="{{ request()->is('roles') ? 'active' : '' }}"><a class="nav-link" href="{{ route('roles.index') }}">{{ __('Roles') }}</a></li>
                    @endif
                    @endcan
                    @can('staff-list')
                    @if(Route::has('staffs.index'))
                    <li class="{{ request()->is('staffs') ? 'active' : '' }}"><a class="nav-link" href="{{ route('staffs.index') }}">{{ __('Staff') }}</a></li>
                    @endif
                    @endcan
                </ul>
            </li>
            @endif

            {{-- 12. SETTINGS --}}
            @if(auth()->user()->can('settings-system-list') || auth()->user()->can('settings-payment-gateway-list'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Settings') }}</li>
            
            <li class="nav-item dropdown {{ \ === 'settings' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-cog"></i><span>{{ __('Settings') }}</span></a>
                <ul class="dropdown-menu">
                    @can('settings-system-list')
                    @if(Route::has('settings.system'))
                    <li class="{{ request()->is('system-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.system') }}">{{ __('System') }}</a></li>
                    @endif
                    @endcan
                    @can('settings-firebase-list')
                    @if(Route::has('settings.firebase'))
                    <li class="{{ request()->is('firebase-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.firebase') }}">{{ __('Firebase') }}</a></li>
                    @endif
                    @endcan
                    @can('settings-payment-gateway-list')
                    @if(Route::has('settings.payment-gateway'))
                    <li class="{{ request()->is('payment-gateway-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.payment-gateway') }}">{{ __('Payments') }}</a></li>
                    @endif
                    @endcan
                    @can('settings-language-list')
                    @if(Route::has('settings.language'))
                    <li class="{{ request()->is('language-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.language') }}">{{ __('Languages') }}</a></li>
                    @endif
                    @endcan
                    @can('settings-app-list')
                    @if(Route::has('settings.app'))
                    <li class="{{ request()->is('app-settings') ? 'active' : '' }}"><a class="nav-link" href="{{ route('settings.app') }}">{{ __('App Settings') }}</a></li>
                    @endif
                    @endcan
                </ul>
            </li>
            @endif

            {{-- 13. HELP DESK --}}
            @if(auth()->user()->can('helpdesk-groups-list') || Route::has('admin.helpdesk.questions.index'))
            <li class="menu-header" style="color: #0D9488;">{{ __('Help Desk') }}</li>
            
            <li class="nav-item dropdown {{ \ === 'help-desk' ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-headset"></i><span>{{ __('Help Desk') }}</span></a>
                <ul class="dropdown-menu">
                    @can('helpdesk-groups-list')
                    @if(Route::has('groups.index'))
                    <li><a class="nav-link" href="{{ route('groups.index') }}">{{ __('Groups') }}</a></li>
                    @endif
                    @endcan
                    @if(Route::has('admin.helpdesk.questions.index'))
                    <li><a class="nav-link" href="{{ route('admin.helpdesk.questions.index') }}">{{ __('Questions') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

        </ul>
    </aside>
</div>
