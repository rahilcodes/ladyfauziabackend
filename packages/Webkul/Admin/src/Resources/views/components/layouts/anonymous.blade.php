<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>

<head>
    <title>{{ $title ?? '' }}</title>

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

    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon'))
        <link
            type="image/x-icon"
            href="{{ Storage::url($favicon) }}"
            rel="shortcut icon"
            sizes="16x16"
        />
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
            background-color: #FAF9F6 !important;
        }
        .dark body {
            background-color: #0F0E0C !important;
        }

        /* Selection Background */
        ::selection {
            background-color: rgba(140, 120, 83, 0.2) !important;
        }

        /* Primary & Secondary Buttons override to Gold/Bronze */
        .btn-luxury, .primary-button {
            background-color: #8C7853 !important;
            border-color: #766444 !important;
            color: #FFFFFF !important;
            transition: all 0.3s ease;
        }
        .btn-luxury:hover, .primary-button:hover {
            background-color: #766444 !important;
            border-color: #5E5036 !important;
        }
        
        .link-luxury, .text-blue-600 {
            color: #8C7853 !important;
        }
        .link-luxury:hover, .text-blue-600:hover {
            color: #766444 !important;
        }

        /* Input Elements and Focus states */
        input:focus, select:focus, textarea:focus {
            border-color: #8C7853 !important;
            --tw-ring-color: #8C7853 !important;
            box-shadow: 0 0 0 1px #8C7853 !important;
        }
    </style>

    {!! view_render_event('bagisto.admin.layout.head') !!}
</head>

<body>
    {!! view_render_event('bagisto.admin.layout.body.before') !!}

    <!-- Built With Bagisto -->
    <div id="app">
        <!-- Flash Message Blade Component -->
        <x-admin::flash-group />

        {!! view_render_event('bagisto.admin.layout.content.before') !!}

        <!-- Page Content Blade Component -->
        {{ $slot }}

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

    <script type="text/javascript">
        {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
    </script>
</body>

</html>
