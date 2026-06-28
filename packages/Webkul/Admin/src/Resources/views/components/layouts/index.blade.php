<!DOCTYPE html>

<html
    class="{{ request()->cookie('dark_mode') ?? 0 ? 'dark' : '' }}"
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>

<head>
    {!! view_render_event('bagisto.admin.layout.head.before') !!}
 
    <title>{{ ($title ?? '') ? $title . ' - Lady Fauzia Co. Admin' : 'Lady Fauzia Co. Admin' }}</title>
 
    <meta charset="UTF-8">
 
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        http-equiv="content-language"
        content="{{ app()->getLocale() }}"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="base-url"
        content="{{ url()->to('/') }}"
    >
    <meta
        name="currency"
        content="{{ core()->getBaseCurrency()->toJson() }}"
    >
    <meta 
        name="generator" 
        content="Bagisto"
    >
 
    @stack('meta')
 
    @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])
 
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />
 
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"
        rel="stylesheet"
    />
 
    <link
        rel="preload"
        as="image"
        href="{{ bagisto_asset('images/logo.png') }}"
    >
 
    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon'))
        <link
            type="image/x-icon"
            href="{{ Storage::url($favicon) }}"
            rel="shortcut icon"
            sizes="16x16"
        >
    @else
        <link
            type="image/x-icon"
            href="{{ bagisto_asset('images/favicon.ico') }}"
            rel="shortcut icon"
            sizes="16x16"
        />
    @endif
 
    @stack('styles')
 
    <style>
        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        
        /* Custom Luxury Theme Overrides for Lady Fauzia */
        body {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Selection Background */
        ::selection {
            background-color: rgba(140, 120, 83, 0.2) !important;
        }

        /* Primary & Secondary Buttons override to Gold/Bronze */
        .primary-button {
            background-color: #8C7853 !important;
            border-color: #766444 !important;
            color: #FFFFFF !important;
            transition: all 0.3s ease;
        }
        .primary-button:hover, .primary-button:focus, .primary-button:active {
            background-color: #766444 !important;
            border-color: #5E5036 !important;
            opacity: 1 !important;
        }
        
        .secondary-button {
            border-color: #8C7853 !important;
            color: #8C7853 !important;
            background-color: #FFFFFF !important;
            transition: all 0.3s ease;
        }
        .secondary-button:hover, .secondary-button:focus, .secondary-button:active {
            background-color: #FAF9F6 !important;
            color: #766444 !important;
            border-color: #766444 !important;
        }
        .dark .secondary-button {
            border-color: #D1B894 !important;
            color: #D1B894 !important;
            background-color: #171614 !important;
        }
        .dark .secondary-button:hover {
            background-color: #24221F !important;
            color: #FAF9F6 !important;
        }

        /* Active Navigation States & Badges */
        .bg-blue-600 {
            background-color: #8C7853 !important; /* Gold/Bronze active capsule */
        }
        .text-blue-600 {
            color: #8C7853 !important;
        }
        .hover\:text-blue-600:hover {
            color: #766444 !important;
        }
        .border-blue-600 {
            border-color: #8C7853 !important;
        }
        .border-b-2.border-blue-600 {
            border-bottom-color: #8C7853 !important;
        }

        /* Profile buttons & fallback icons */
        .bg-blue-400 {
            background-color: #8C7853 !important;
        }
        .hover\:bg-blue-500:hover {
            background-color: #766444 !important;
        }
        .focus\:bg-blue-500:focus {
            background-color: #766444 !important;
        }

        /* Input Elements and Focus states */
        input:focus, select:focus, textarea:focus {
            border-color: #8C7853 !important;
            --tw-ring-color: #8C7853 !important;
            box-shadow: 0 0 0 1px #8C7853 !important;
        }

        /* Submenu Active Styles */
        .text-blue-600.dark\:text-blue-300 {
            color: #8C7853 !important;
        }
        .dark .text-blue-600.dark\:text-blue-300 {
            color: #D1B894 !important;
        }

        /* TinyMCE override */
        .tox .tox-toolbar__group:last-child button {
            background: #FAF9F6 !important;
            color: #8C7853 !important;
        }
        .tox .tox-toolbar__group:last-child button:hover {
            background: #E5E0D8 !important;
        }
    </style>
 
    {!! view_render_event('bagisto.admin.layout.head.after') !!}
</head>

<body class="h-full dark:bg-gray-950">
    {!! view_render_event('bagisto.admin.layout.body.before') !!}

    <!-- Built With Bagisto -->
    <div
        id="app"
        class="h-full"
    >
        <!-- Flash Message Blade Component -->
        <x-admin::flash-group />

        <!-- Confirm Modal Blade Component -->
        <x-admin::modal.confirm />

        {!! view_render_event('bagisto.admin.layout.content.before') !!}

        <!-- Page Header Blade Component -->
        <x-admin::layouts.header />

        <div
            class="group/container {{ request()->cookie('sidebar_collapsed') ?? 0 ? 'sidebar-collapsed' : 'sidebar-not-collapsed' }} flex flex-col lg:flex-row gap-0 lg:gap-4"
            ref="appLayout"
        >
            <!-- Page Sidebar Blade Component -->
            <div class="lg:fixed lg:top-[62px] lg:left-0 rtl:lg:right-0 rtl:lg:left-auto lg:z-10 w-full lg:w-auto">
                <x-admin::layouts.sidebar />
            </div>

            <div class="flex min-h-[calc(100vh-62px)] max-w-full flex-1 flex-col bg-white transition-all duration-300 dark:bg-gray-950 pt-3 px-2 sm:px-4 lg:pt-3 lg:px-4 lg:ltr:pl-[286px] lg:group-[.sidebar-collapsed]/container:ltr:pl-[85px] lg:rtl:pr-[286px] lg:group-[.sidebar-collapsed]/container:rtl:pr-[85px]">
                <!-- Added dynamic tabs for third level menus  -->
                <div class="pb-4 lg:pb-6">
                    <!-- Todo @suraj-webkul need to optimize below statement. -->
                    @if (! request()->routeIs('admin.configuration.index'))
                        <div class="overflow-x-auto">
                            <x-admin::layouts.tabs />
                        </div>
                    @endif

                    <!-- Page Content Blade Component -->
                    <div class="w-full overflow-x-hidden">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Powered By -->
                <div class="mt-auto">
                    <div class="border-t bg-white py-2 text-center text-xs sm:text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        @lang('admin::app.components.layouts.powered-by.description', [
                            'bagisto' => '<a class="text-blue-600 hover:underline dark:text-darkBlue" href="https://bagisto.com/en/">Bagisto</a>',
                            'webkul' => '<a class="text-blue-600 hover:underline dark:text-darkBlue" href="https://webkul.com/">Webkul</a>',
                        ])
                    </div>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.layout.content.after') !!}
    </div>

    {!! view_render_event('bagisto.admin.layout.body.after') !!}

    @stack('scripts')

    {!! view_render_event('bagisto.admin.layout.vue-app-mount.before') !!}

    <script>
        /**
         * Load event, the purpose of using the event is to mount the application
         * after all of our `Vue` components which is present in blade file have
         * been registered in the app. No matter what `app.mount()` should be
         * called in the last.
         */
        window.addEventListener("load", function(event) {
            app.mount("#app");
        });
    </script>

    {!! view_render_event('bagisto.admin.layout.vue-app-mount.after') !!}
</body>

</html>
