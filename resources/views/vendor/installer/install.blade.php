<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Installation - {{ config('app.name', 'Laravel') }}</title>
    @php
        // Default to config values
        $backgroundImage = config('installer.background', '/img/background.jpeg');
        $appLogo = config('installer.logo', '/img/logo.png');
        
        // Try to get system settings if app is installed and database is available
        try {
            $isInstalled = file_exists(storage_path('installed'));
            if ($isInstalled && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Services\HelperService::systemSettings(['login_banner_image', 'vertical_logo']);
                if (!empty($settings['login_banner_image'])) {
                    $backgroundImage = $settings['login_banner_image'];
                }
                if (!empty($settings['vertical_logo'])) {
                    $appLogo = $settings['vertical_logo'];
                }
            }
        } catch (\Exception $e) {
            // Use config values if system settings are not available
        }
        
        // Ensure paths are correct - convert to full URLs
        // Remove leading slash if present and use asset() helper
        if (!empty($backgroundImage)) {
            $backgroundImage = ltrim($backgroundImage, '/');
            if (!filter_var($backgroundImage, FILTER_VALIDATE_URL) && !str_starts_with($backgroundImage, 'http')) {
                $backgroundImage = asset($backgroundImage);
            }
        } else {
            $backgroundImage = asset('img/background.jpeg');
        }
        
        if (!empty($appLogo)) {
            $appLogo = ltrim($appLogo, '/');
            if (!filter_var($appLogo, FILTER_VALIDATE_URL) && !str_starts_with($appLogo, 'http')) {
                $appLogo = asset($appLogo);
            }
        } else {
            $appLogo = asset('img/logo.png');
        }
    @endphp
    <link rel="shortcut icon" href="{{ $appLogo }}">
    <link href="{{ asset('vendor/wizard-installer/styles.css') }}" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ $backgroundImage }}') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
        }
    </style>
</head>

<body class="min-h-screen h-full w-full bg-cover bg-no-repeat bg-center flex">
    <div class="p-12 h-full m-auto">
        <div class="mx-auto w-full max-w-5xl w-[64rem]">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-8 border-b border-gray-200 sm:px-6">
                    <div class="flex justify-center items-center">
                        @if(!empty($appLogo))
                            <img alt="App logo" class="h-12" src="{{ $appLogo }}" onerror="this.style.display='none'">
                        @endif
                        <h2 class="pl-6 uppercase font-medium text-2xl text-gray-800">{{ config('app.name', 'Laravel') }} Installation</h2>
                    </div>
                </div>
                <div class="px-4 py-5 sm:px-6 w-full">
                    @yield('step')
                </div>
            </div>
        </div>
    </div>
</body>

</html>
