<x-admin::layouts.anonymous>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.users.sessions.title')
    </x-slot>

    <!-- Custom Luxury Style Overrides -->
    @push('styles')
    <style>
        body {
            background-color: #FAF9F6 !important; /* Luxury Warm Off-white */
            font-family: 'Poppins', sans-serif;
        }
        .dark body {
            background-color: #0F0E0C !important; /* Dark rich charcoal */
        }
        /* Custom Gold/Bronze Accents */
        .btn-luxury {
            background-color: #8C7853 !important;
            border-color: #8C7853 !important;
            color: #FFFFFF !important;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .btn-luxury:hover {
            background-color: #766444 !important;
            border-color: #766444 !important;
        }
        .link-luxury {
            color: #8C7853 !important;
            transition: color 0.3s ease;
        }
        .link-luxury:hover {
            color: #766444 !important;
            text-decoration: underline;
        }
        /* Override default input focus border and ring colors */
        input:focus {
            border-color: #8C7853 !important;
            --tw-ring-color: #8C7853 !important;
            box-shadow: 0 0 0 1px #8C7853 !important;
        }
        .luxury-card {
            border: 1px solid #E5E0D8;
            background-color: #FFFFFF;
        }
        .dark .luxury-card {
            border: 1px solid #2D2A26;
            background-color: #171614;
        }
    </style>
    @endpush

    <div class="flex h-[100vh] items-center justify-center bg-[#FAF9F6] dark:bg-[#0F0E0C]">
        <div class="flex flex-col items-center gap-8 w-full max-w-[420px] px-6">
            
            <!-- Logo Section with Luxury Spacing -->
            <div class="flex flex-col items-center gap-2">
                @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                    <img
                        class="h-16 w-auto object-contain"
                        src="{{ Storage::url($logo) }}"
                        alt="{{ config('app.name') }}"
                    />
                @else
                    <!-- Light theme logo -->
                    <img
                        class="w-56 h-auto dark:hidden" 
                        src="{{ bagisto_asset('images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                    />
                    <!-- Dark theme logo -->
                    <img
                        class="w-56 h-auto hidden dark:block" 
                        src="{{ bagisto_asset('images/dark-logo.png') }}"
                        alt="{{ config('app.name') }}"
                    />
                @endif
                <span class="text-[10px] tracking-[0.25em] text-[#8C7853] uppercase font-semibold mt-3">Internal Portal</span>
            </div>

            <!-- Login Card -->
            <div class="luxury-card w-full rounded-none p-8 shadow-sm">
                <x-admin::form :action="route('admin.session.store')">
                    <h1 class="text-2xl font-serif text-gray-900 dark:text-amber-50 mb-6 tracking-wide text-center">
                        @lang('admin::app.users.sessions.title')
                    </h1>

                    <div class="flex flex-col gap-5">
                        <!-- Email -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                @lang('admin::app.users.sessions.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control 
                                type="email" 
                                class="w-full rounded-none border-[#E5E0D8] dark:border-[#2D2A26] bg-transparent text-gray-900 dark:text-white py-3 px-4" 
                                id="email"
                                name="email" 
                                rules="required|email" 
                                :label="trans('admin::app.users.sessions.email')"
                                :placeholder="trans('admin::app.users.sessions.email')"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <!-- Password -->
                        <x-admin::form.control-group class="relative w-full">
                            <x-admin::form.control-group.label class="required text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                @lang('admin::app.users.sessions.password')
                            </x-admin::form.control-group.label>
                    
                            <x-admin::form.control-group.control 
                                type="password" 
                                class="w-full rounded-none border-[#E5E0D8] dark:border-[#2D2A26] bg-transparent text-gray-900 dark:text-white py-3 px-4 ltr:pr-10 rtl:pl-10" 
                                id="password"
                                name="password" 
                                rules="required|min:6" 
                                :label="trans('admin::app.users.sessions.password')"
                                :placeholder="trans('admin::app.users.sessions.password')"
                            />
                    
                            <span 
                                class="icon-view absolute top-[44px] -translate-y-2/4 cursor-pointer text-2xl ltr:right-3 rtl:left-3 text-gray-400"
                                onclick="switchVisibility()"
                                id="visibilityIcon"
                                role="presentation"
                                tabindex="0"
                            >
                            </span>
                    
                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="flex items-center justify-between mt-8">
                        <!-- Forgot Password Link -->
                        <a 
                            class="cursor-pointer text-xs font-semibold leading-6 link-luxury"
                            href="{{ route('admin.forget_password.create') }}"
                        >
                            @lang('admin::app.users.sessions.forget-password-link')
                        </a>

                        <!-- Submit Button -->
                        <button
                            class="cursor-pointer rounded-none btn-luxury px-6 py-2.5 text-xs font-medium"
                            aria-label="{{ trans('admin::app.users.sessions.submit-btn')}}"
                        >
                            @lang('admin::app.users.sessions.submit-btn')
                        </button>
                    </div>
                </x-admin::form>
            </div>

            <!-- Minimal Footer -->
            <div class="text-[10px] tracking-wider text-gray-400 dark:text-gray-500 uppercase text-center mt-4">
                &copy; {{ date('Y') }} Lady Fauzia Co. All Rights Reserved.
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");
                let visibilityIcon = document.getElementById("visibilityIcon");

                passwordField.type = passwordField.type === "password" ? "text" : "password";
                visibilityIcon.classList.toggle("icon-view-close");
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>