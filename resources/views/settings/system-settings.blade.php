@extends('layouts.app')
@section('title')
    {{ __('System Settings') }}
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
                <form action="{{ route('settings.system.update') }}" method="POST" enctype="multipart/form-data" class="create-form" data-success-function="formSuccessFunction" data-pre-submit-function="prepareMaintenanceMode">
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('System Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                <!-- System Version (Read-only) -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="system_version" class="form-label">{{ __('System Version') }}</label>
                                        <input type="text" name="system_version" class="form-control" id="system_version" value="{{ $settings['system_version'] ?? '1.0.0' }}" readonly disabled style="background-color: #e9ecef; cursor: not-allowed;">
                                        <small class="form-text text-muted">{{ __('Current system version (read-only)') }}</small>
                                    </div>
                                </div>

                                <!-- App Name -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group mandatory">
                                        <label for="app_name" class="form-label">{{ __('App Name') }}</label>
                                        <input type="text" name="app_name" class="form-control" id="app_name" value="{{ $settings['app_name'] ?? '' }}" placeholder="{{ __('App Name') }}" required>
                                    </div>
                                </div>

                                <!-- Website URL -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="website_url" class="form-label">{{ __('Website URL') }}</label>
                                        <input type="url" name="website_url" class="form-control" id="website_url" value="{{ $settings['website_url'] ?? '' }}" placeholder="{{ __('https://example.com') }}">
                                        <small class="form-text text-muted">{{ __('Enter your website URL (e.g., https://example.com)') }}</small>
                                    </div>
                                </div>

                                <!-- Announcement Bar -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="announcement_bar" class="form-label">{{ __('Announcement Bar') }}</label>
                                        <input type="text" name="announcement_bar" class="form-control" id="announcement_bar" value="{{ $settings['announcement_bar'] ?? '' }}" placeholder="{{ __('Enter announcement text') }}">
                                        <small class="form-text text-muted">{{ __('This text will be displayed at the top of your website') }}</small>
                                    </div>
                                </div>

                                    <!-- Favicon -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @if (isset($settings['favicon']) && $settings['favicon'] != '')
                                    <div class="form-group">
                                            <label for="favicon" class="form-label">{{ __('Favicon') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="favicon" class="custom-file-input" id="favicon" accept="image/png, image/jpeg, image/jpg, .ico">
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                            <div class="form-text text-muted"> {{ __('The image must have a maximum size of 1MB') }} </div>
                                            {{-- File Preview --}}
                                            <div class="mt-2">
                                                <img src="{{ $settings['favicon'] }}" alt="Favicon" class="img-thumbnail" style="max-height: 50px;">
                                            </div>
                                        </div> @else <div class="form-group mandatory">
                                            <label for="favicon" class="form-label">{{ __('Favicon') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="favicon" class="custom-file-input" id="favicon" accept="image/png, image/jpeg, image/jpg, .ico" required>
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                        </div> @endif </div>


                                <!-- Vertical Logo -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @if (isset($settings['vertical_logo']) && $settings['vertical_logo'] != '')
                                    <div class="form-group">
                                            <label for="vertical_logo" class="form-label">{{ __('Vertical Logo') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="vertical_logo" class="custom-file-input" id="vertical_logo">
                                            <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                            <div class="form-text text-muted"> {{ __('The image must have a maximum size of 1MB') }} </div>
                                            {{-- File Preview --}}
                                            <div class="mt-2">
                                                <img src="{{ $settings['vertical_logo'] }}" alt="Vertical Logo" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        </div> @else <div class="form-group mandatory">
                                            <label for="vertical_logo" class="form-label">{{ __('Vertical Logo') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="vertical_logo" class="custom-file-input" id="vertical_logo" accept="image/png, image/jpeg, image/jpg" required>
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                        </div> @endif </div>

                                <!-- Horizontal Logo -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @if (isset($settings['horizontal_logo']) && $settings['horizontal_logo'] != '')
                                    <div class="form-group">
                                            <label for="horizontal_logo" class="form-label">{{ __('Horizontal Logo') }} ({{ __('Web Logo') }})</label>
                                            <div class="custom-file">
                                                <input type="file" name="horizontal_logo" class="custom-file-input" id="horizontal_logo">
                                                <label class="custom-file-label" for="horizontal_logo">{{ __('Choose File') }}</label>
                                            </div>
                                            <div class="form-text text-muted"> {{ __('The image must have a maximum size of 1MB') }} </div>
                                            {{-- File Preview --}}
                                            <div class="mt-2">
                                                <img src="{{ $settings['horizontal_logo'] }}" alt="Horizontal Logo" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        </div> @else <div class="form-group mandatory">
                                            <label for="horizontal_logo" class="form-label">{{ __('Horizontal Logo') }} ({{ __('Web Logo') }})</label>
                                            <div class="custom-file">
                                                <input type="file" name="horizontal_logo" class="custom-file-input" id="horizontal_logo" accept="image/png, image/jpeg, image/jpg" required>
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                        </div> @endif </div>

                                <!-- Placeholder Image -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @if (isset($settings['placeholder_image']) && $settings['placeholder_image'] != '')
                                    <div class="form-group">
                                            <label for="placeholder_image" class="form-label">{{ __('Placeholder Image') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="placeholder_image" class="custom-file-input" id="placeholder_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <label class="custom-file-label" for="placeholder_image">{{ __('Choose File') }}</label>
                                            </div>
                                            <div class="form-text text-muted"> {{ __('The image must have a maximum size of 2MB') }} </div>
                                            {{-- File Preview --}}
                                            <div class="mt-2">
                                                <img src="{{ $settings['placeholder_image'] }}" alt="Placeholder Image" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        </div> @else <div class="form-group">
                                            <label for="placeholder_image" class="form-label">{{ __('Placeholder Image') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="placeholder_image" class="custom-file-input" id="placeholder_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                            <small class="form-text text-muted">{{ __('Default image to display when no image is available') }}</small>
                                        </div> @endif </div>

                                <!-- Login Banner Image -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @if (isset($settings['login_banner_image']) && $settings['login_banner_image'] != '')
                                    <div class="form-group">
                                            <label for="login_banner_image" class="form-label">{{ __('Login Banner Image') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="login_banner_image" class="custom-file-input" id="login_banner_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <label class="custom-file-label" for="login_banner_image">{{ __('Choose File') }}</label>
                                            </div>
                                            <div class="form-text text-muted"> {{ __('The image must have a maximum size of 2MB') }} </div>
                                            {{-- File Preview --}}
                                            <div class="mt-2">
                                                <img src="{{ $settings['login_banner_image'] }}" alt="Login Banner Image" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        </div> @else <div class="form-group">
                                            <label for="login_banner_image" class="form-label">{{ __('Login Banner Image') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="login_banner_image" class="custom-file-input" id="login_banner_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <label class="custom-file-label">{{ __('Choose File') }}</label>
                                            </div>
                                            <small class="form-text text-muted">{{ __('Banner image displayed on the login page') }}</small>
                                        </div> @endif </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Contact Information') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                <!-- Contact Address -->
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="contact_address" class="form-label">{{ __('Contact Address') }}</label>
                                        <textarea name="contact_address" class="form-control" id="contact_address" rows="3" placeholder="{{ __('Enter contact address') }}">{{ $settings['contact_address'] ?? '' }}</textarea>
                                        <small class="form-text text-muted">{{ __('Your business or organization address') }}</small>
                                    </div>
                                </div>

                                <!-- Contact Email -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="contact_email" class="form-label">{{ __('Contact Email') }}</label>
                                        <input type="email" name="contact_email" class="form-control" id="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="{{ __('contact@example.com') }}">
                                        <small class="form-text text-muted">{{ __('Email address for contact inquiries') }}</small>
                                    </div>
                                </div>

                                <!-- Contact Phone -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="contact_phone" class="form-label">{{ __('Contact Phone') }}</label>
                                        <input type="text" name="contact_phone" class="form-control" id="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="{{ __('+1 234 567 8900') }}">
                                        <small class="form-text text-muted">{{ __('Phone number for contact inquiries') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Other Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">

                                <!-- Countries -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="col-sm-12 form-label" for="currency-code">{{ __('Currency Name') }}</label>
                                    <select id="currency-code" class="form-select form-control-sm select2" name="currency_code" required data-parsley-required-message="{{ __('Currency Name is required') }}">
                                        <option value="">{{ __('Select Currency') }}</option> @if(!empty($listOfCurrencies))
                                            @foreach ($listOfCurrencies as $data) <option value="{{ $data['currency_code'] }}" {{ isset($settings['currency_code']) && $settings['currency_code'] == $data['currency_code'] ? 'selected' : '' }}>{{ $data['currency_name'] }}</option> @endforeach
                                        @endif </select>
                                    <input type="hidden" id="url-for-currency-symbol" value="{{ route('get-currency-symbol') }}">
                                </div>

                                <!-- Currency Symbol -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="col-sm-12 form-label " for="curreny-symbol">{{ __('Currency Symbol') }}</label>
                                    <input name="currency_symbol" type="text" id="currency-symbol" class="form-control" placeholder="{{ __('Currency Symbol') }}" required maxlength="5" value="{{ isset($settings['currency_symbol']) && $settings['currency_symbol'] != '' ? $settings['currency_symbol'] : '' }}" data-parsley-required-message="{{ __('Currency Symbol is required') }}">
                                </div>

                                <!-- System Color -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="col-sm-12 mt-2" class="form-label" for="system_color">{{ __('System Color') }}</label>
                                    <input name="system_color" class="form-control jscolor" data-jscolor="{hash:true, alphaChannel:true}"
                                    value="{{ isset($settings['system_color']) && $settings['system_color'] != '' ? $settings['system_color'] : '#E48D18FF' }}">
                                </div>

                                <!-- System Light Color -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="col-sm-12 mt-2" class="form-label" for="system_light_color">{{ __('System Light Color') }}</label>
                                    <input name="system_light_color" class="form-control jscolor" data-jscolor="{hash:true, alphaChannel:true}"
                                    value="{{ isset($settings['system_light_color']) && $settings['system_light_color'] != '' ? $settings['system_light_color'] : '#E48D18FF' }}">
                                </div>

                                <!-- Hover Color -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <label class="col-sm-12 mt-2" class="form-label" for="hover_color">{{ __('Hover Color') }}</label>
                                    <input name="hover_color" class="form-control jscolor" data-jscolor="{hash:true, alphaChannel:true}"
                                    value="{{ isset($settings['hover_color']) && $settings['hover_color'] != '' ? $settings['hover_color'] : '#4A4B9AFF' }}">
                                </div>

                                <!-- Footer Description -->
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label for="footer_description" class="form-label">{{ __('Footer Description') }}</label>
                                        <textarea name="footer_description" class="form-control" id="footer_description" rows="3" placeholder="{{ __('Enter footer description') }}">{{ $settings['footer_description'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Schema -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="schema" class="form-label">{{ __('Schema') }}</label>
                                        <input type="text" name="schema" class="form-control" id="schema" value="{{ $settings['schema'] ?? '' }}" placeholder="{{ __('Enter schema (lowercase letters only)') }}" pattern="[a-z]*" oninput="this.value = this.value.toLowerCase().replace(/[^a-z]/g, '')">
                                        <small class="form-text text-muted">{{ __('Only lowercase letters [a-z] are allowed') }}</small>
                                    </div>
                                </div>

                                <!-- Maximum Video Upload Size -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="max_video_upload_size" class="form-label">{{ __('Maximum Upload Size for Videos (MB)') }}</label>
                                        <input type="number" name="max_video_upload_size" class="form-control" id="max_video_upload_size" value="{{ $settings['max_video_upload_size'] ?? '10' }}" placeholder="{{ __('Enter maximum size in MB') }}" min="1" step="1">
                                        <small class="form-text text-muted">{{ __('Maximum file size allowed for course video uploads (in MB)') }}</small>
                                    </div>
                                </div>

                                <!-- Maintenance Mode -->
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <div class="control-label">{{ __('Maintenance Mode') }}</div>
                                        <div class="custom-switches-stacked mt-2">
                                            <label class="custom-switch">
                                                <input type="checkbox" name="maintaince_mode" value="1" class="custom-switch-input" id="maintaince-mode">
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">{{ __('Enable') }}</span>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">{{ __('Enable maintenance mode to temporarily disable the system') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Tax Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">

                                <!-- Tax -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="form-label d-block">{{ __('Tax Type') }}</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tax_type" id="inclusive" value="inclusive" 
                                            {{ isset($settings['tax_type']) && $settings['tax_type'] == 'inclusive' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="inclusive">{{ __('Inclusive') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tax_type" id="exclusive" value="exclusive" 
                                            {{ isset($settings['tax_type']) && $settings['tax_type'] == 'exclusive' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exclusive">{{ __('Exclusive') }}</label>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    <h4 class="card-title">{{ __('Commission Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                <!-- Individual Trainer Admin Commission -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="form-label" for="individual_admin_commission">{{ __('Individual Trainer Admin Commission (%)') }}</label>
                                    <input 
                                        name="individual_admin_commission" 
                                        type="number" 
                                        id="individual_admin_commission" 
                                        class="form-control" 
                                        placeholder="{{ __('Enter admin commission for individual trainers') }}" 
                                        required 
                                        min="0" 
                                        max="100"
                                        value="{{ isset($settings['individual_admin_commission']) ? $settings['individual_admin_commission'] : '5' }}"
                                        oninput="updateCommissions()"
                                    >
                                </div>
                                
                                <!-- Team Trainer Admin Commission -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="form-label" for="team_admin_commission">{{ __('Team Trainer Admin Commission (%)') }}</label>
                                    <input 
                                        name="team_admin_commission" 
                                        type="number" 
                                        id="team_admin_commission" 
                                        class="form-control" 
                                        placeholder="{{ __('Enter admin commission for team trainers') }}" 
                                        required 
                                        min="0" 
                                        max="100"
                                        value="{{ isset($settings['team_admin_commission']) ? $settings['team_admin_commission'] : '10' }}"
                                        oninput="updateCommissions()"
                                    >
                                </div>
                                
                                <!-- Individual Trainer Commission (auto-calculated) -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group">
                                    <label class="form-label" for="individual_trainer_commission">{{ __('Individual Trainer Commission (%)') }}</label>
                                    <input 
                                        name="individual_trainer_commission" 
                                        type="number" 
                                        id="individual_trainer_commission" 
                                        class="form-control" 
                                        placeholder="{{ __('Individual trainer commission percentage') }}" 
                                        readonly
                                        value="{{ isset($settings['individual_admin_commission']) ? (100 - $settings['individual_admin_commission']) : '95' }}"
                                    >
                                </div>
                                
                                <!-- Team Trainer Commission (auto-calculated) -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group">
                                    <label class="form-label" for="team_trainer_commission">{{ __('Team Trainer Commission (%)') }}</label>
                                    <input 
                                        name="team_trainer_commission" 
                                        type="number" 
                                        id="team_trainer_commission" 
                                        class="form-control" 
                                        placeholder="{{ __('Team trainer commission percentage') }}" 
                                        readonly
                                        value="{{ isset($settings['team_admin_commission']) ? (100 - $settings['team_admin_commission']) : '90' }}"
                                    >
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>{{ __('Commission Distribution Methodology:') }}</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>{{ __('Admin commission (5% for individual trainers, 10% for team trainers) applied to discounted course prices') }}</li>
                                            <li>{{ __('Individual trainers receive 95% commission, Team trainers receive 90% commission') }}</li>
                                            <li>{{ __('Coupon discount allocated proportionally based on each course\'s contribution to original cart total') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ __('The admin commission varies based on trainer type. Individual trainers have 5% admin commission, while team trainers have 10% admin commission. Trainer commissions are automatically calculated as the remainder.') }}
                            </small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Trainer Mode Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                <!-- Trainer Mode -->
                                <div class="col-sm-12 col-md-6 col-lg-4 mt-2 form-group mandatory">
                                    <label class="form-label" for="trainer_mode">{{ __('Trainer Mode') }}</label>
                                    <select name="trainer_mode" id="trainer_mode" class="form-control" required>
                                        <option value="single" {{ (isset($settings['trainer_mode']) && $settings['trainer_mode'] == 'single') ? 'selected' : '' }}>
                                            {{ __('Single Trainer (Admin as Trainer)') }}
                                        </option>
                                        <option value="multi" {{ (isset($settings['trainer_mode']) && $settings['trainer_mode'] == 'multi') ? 'selected' : '' }}>
                                            {{ __('Multi Trainer System') }}
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        {{ __('Single: Admin acts as the only trainer. Multi: Separate trainer accounts allowed.') }}
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>{{ __('Note:') }}</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>{{ __('Single Trainer Mode: Admin will have trainer permissions and capabilities. Trainer lists and filters will be hidden.') }}</li>
                                            <li>{{ __('Multi Trainer Mode: Separate trainer accounts can be created and managed. Full trainer management features available.') }}</li>
                                            <li>{{ __('Changing this setting will affect how the system handles trainer-related functionality.') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Social Media') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">

                                <!-- Social Media -->
                                <div class="form-group mandatory col-12">
                                    <div class="social-media-section">
                                        <div data-repeater-list="social_media_data">
                                            <div class="row learning-section d-flex align-items-center mb-2" data-repeater-item>
                                                <input type="hidden" name="id" class="id">
                                                {{-- Name --}}
                                                <div class="form-group mandatory col-md-12 col-lg-4">
                                                    <label class="form-label">{{ __('Name') }} - <span class="sr-number"> {{ __('0') }} </span></label>
                                                    <input type="text" name="name" class="form-control" placeholder="{{ __('Enter a name') }}" required data-parsley-required="true">
                                                </div>
                                                {{-- Icon --}}
                                                <div class="form-group mandatory col-md-12 col-lg-3">
                                                    <label class="form-label">{{ __('Icon') }} - <span class="sr-number"> {{ __('0') }} </span></label>
                                                    <input type="file" name="icon" class="form-control social-media-icon-input" placeholder="{{ __('Enter a icon') }}" required data-parsley-required="true" accept="image/png, image/jpeg, image/jpg">
                                                    {{-- File Preview --}}
                                                    <div class="mt-2 social-media-icon-preview" style="display: none;">
                                                        <img src="" alt="Social Media Icon" class="img-thumbnail social-media-icon" style="max-height: 50px;">
                                                    </div>
                                                </div>
                                                {{-- Link/URL --}}
                                                <div class="form-group mandatory col-md-12 col-lg-4">
                                                    <label class="form-label">{{ __('Link') }} - <span class="sr-number"> {{ __('0') }} </span></label>
                                                    <input type="url" name="url" class="form-control" placeholder="{{ __('https://example.com') }}" required data-parsley-required="true" data-parsley-type="url">
                                                    <small class="form-text text-muted">{{ __('Social media profile URL') }}</small>
                                                </div>
                                                {{-- Remove Social Media --}}
                                                <div class="form-group col-md-12 col-lg-1 mt-4">
                                                    <button data-repeater-delete type="button" class="btn btn-danger remove-social-media" title="{{ __('remove') }}">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Add New Social Media --}}
                                        <button type="button" class="btn btn-success mt-1 add-new-social-media" data-repeater-create title="{{ __('Add New Social Media') }}">
                                            <i class="fa fa-plus"></i> {{ __('Add New Social Media') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="divider">
                                <div class="divider-text">
                                    {{-- Title --}}
                                    <h4 class="card-title">{{ __('Refund Settings') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                <!-- Refund Status -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group mandatory">
                                        <label for="refund_enabled" class="form-label">{{ __('Enable Refunds') }}</label>
                                        <select name="refund_enabled" id="refund_enabled" class="form-control" required>
                                            <option value="1" {{ isset($settings['refund_enabled']) && $settings['refund_enabled'] == 1 ? 'selected' : '' }}>{{ __('Enabled') }}</option>
                                            <option value="0" {{ isset($settings['refund_enabled']) && $settings['refund_enabled'] == 0 ? 'selected' : '' }}>{{ __('Disabled') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Refund Days -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="form-group mandatory">
                                        <label for="refund_period_days" class="form-label">{{ __('Refund Period (Days)') }}</label>
                                        <input type="number" name="refund_period_days" class="form-control" id="refund_period_days" value="{{ $settings['refund_period_days'] ?? 7 }}" min="0" placeholder="{{ __('Number of days allowed for refund') }}" required>
                                        <small class="form-text text-muted">{{ __('Number of days after purchase when refunds are allowed') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Refund Policy -->
                                <div class="col-12">
                                    <div class="form-group mandatory">
                                        <label for="refund_policy" class="form-label">{{ __('Refund Policy') }}</label>
                                        <textarea name="refund_policy" class="form-control tinymce-editor" required>{{ $settings['refund_policy'] ?? '' }}</textarea>
                                        <small class="form-text text-muted">{{ __('Describe your refund policy terms and conditions') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12 text-right">
                            <button class="btn btn-primary" id="save-btn">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> @endsection

@push('scripts') 
<script>
        // Pre-submit function to handle maintenance mode checkbox
        window.prepareMaintenanceMode = function() {
            // Remove any existing hidden input first
            $('#maintaince-mode-hidden').remove();
            
            // If checkbox is unchecked, add hidden input with value 0
            if (!$('#maintaince-mode').is(':checked')) {
                // Create hidden input and add it to form
                var hiddenInput = $('<input>').attr({
                    type: 'hidden',
                    name: 'maintaince_mode',
                    value: '0',
                    id: 'maintaince-mode-hidden'
                });
                // Insert it right after the checkbox
                $('#maintaince-mode').after(hiddenInput);
            }
            return true; // Allow form submission to proceed
        };

        $(document).ready(function() {
            // Fix Select2 with Parsley validation
            // Initialize select2 first (if not already initialized)
            if (!$('#currency-code').hasClass('select2-hidden-accessible')) {
                $('#currency-code').select2();
            }
            
            // Make sure parsley doesn't exclude the select element
            $('#currency-code').attr('data-parsley-excluded', 'false');
            
            // Trigger validation on select2 events
            $('#currency-code').on('select2:select select2:unselect', function(e) {
                // Clear any previous errors
                $(this).parsley().reset();
                // Validate the field
                $(this).parsley().validate();
            });
            
            // Ensure select2 change also triggers validation
            $('#currency-code').on('change', function() {
                var value = $(this).val();
                if (value) {
                    // If value exists, clear error
                    $(this).parsley().reset();
                } else {
                    // If no value, validate to show error
                    $(this).parsley().validate();
                }
            });
            
            // Override Parsley's default excluded option to include hidden select elements
            window.Parsley.options.excluded = 'input[type=button], input[type=submit], input[type=reset], input[type=hidden]';
            
            // Maintenance Mode
            let maintainceMode = {{ $settings['maintaince_mode'] ?? 0 }};
            if(maintainceMode == 1 || maintainceMode == '1') {
                $('#maintaince-mode').prop('checked', true).trigger('change');
            } else {
                $('#maintaince-mode').prop('checked', false);
            }

            // Social Media Repeater
            @if(collect($socialMedias)->isNotEmpty())
                socialMediaRepeater.setList([
                    @foreach($socialMedias as $socialMedia)
                        {
                            "id": {{ $socialMedia->id }},
                            "name": "{{ $socialMedia->name }}",
                            "url": "{{ $socialMedia->url ?? '' }}"
                        },
                    @endforeach
                ]);

                @foreach($socialMedias as $key =>$socialMedia)
                    @if($socialMedia->icon)
                        $('#social-media-icon-input-{{ $key + 1 }}').removeAttr('required').removeAttr('data-parsley-required');
                        $('#social-media-icon-preview-{{ $key + 1 }}').find('.social-media-icon').attr('src', '{{ $socialMedia->icon }}');
                        $('#social-media-icon-preview-{{ $key + 1 }}').show();
                    @else
                        $('#social-media-icon-preview-{{ $key + 1 }}').attr('required', true).attr('data-parsley-required', true);
                    @endif
                @endforeach
            @endif
        });
        function formSuccessFunction() {
            location.reload();
        }
    
        function updateCommissions() {
            // Update Individual Trainer Commission
            var individualAdminCommission = parseFloat(document.getElementById('individual_admin_commission').value) || 0;
            var individualTrainerCommission = 100 - individualAdminCommission;
            if(individualTrainerCommission < 0) individualTrainerCommission = 0;
            document.getElementById('individual_trainer_commission').value = individualTrainerCommission;
            
            // Update Team Trainer Commission
            var teamAdminCommission = parseFloat(document.getElementById('team_admin_commission').value) || 0;
            var teamTrainerCommission = 100 - teamAdminCommission;
            if(teamTrainerCommission < 0) teamTrainerCommission = 0;
            document.getElementById('team_trainer_commission').value = teamTrainerCommission;
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCommissions();
        });
    
        // Prevent admin commissions from exceeding 100
        document.addEventListener('DOMContentLoaded', function() {
            var individualAdminInput = document.getElementById('individual_admin_commission');
            var teamAdminInput = document.getElementById('team_admin_commission');
            
            individualAdminInput.addEventListener('input', function() {
                if (parseFloat(this.value) > 100) {
                    this.value = 100;
                }
                updateCommissions();
            });
            
            teamAdminInput.addEventListener('input', function() {
                if (parseFloat(this.value) > 100) {
                    this.value = 100;
                }
                updateCommissions();
            });
        });
    </script>
@endpush


