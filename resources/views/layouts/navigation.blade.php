<nav x-data="{ open: false }" class="static top-3 z-40 mx-3 mt-3 rounded-2xl border border-indigo-100/80 bg-gradient-to-r from-indigo-50/95 via-violet-50/95 to-sky-50/95 backdrop-blur shadow-sm shadow-indigo-100/60">
<!-- <nav x-data="{ open: false }" class="sticky top-3 z-40 mx-3 mt-3 rounded-2xl border border-indigo-100/80 bg-gradient-to-r from-indigo-50/95 via-violet-50/95 to-sky-50/95 backdrop-blur shadow-sm shadow-indigo-100/60"> -->
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                    <!-- <span class="hidden text-sm font-semibold text-slate-700 xl:inline">Agriculture Tractor</span> -->
                    <span class="hidden text-sm font-semibold text-blue-700 xl:inline">Agriculture Tractor</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 xl:-my-px xl:ms-8 xl:flex xl:flex-wrap xl:items-center">
                    @can('dashboard.view')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </x-nav-link>
                    @endcan
                    <x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">
                        <i class="bi bi-camera-reels"></i> Attendance
                    </x-nav-link>
                    @can('categories.manage')
                        <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                            <i class="bi bi-tags"></i> Categories
                        </x-nav-link>
                    @endcan
                    @can('products.manage')
                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                            <i class="bi bi-box-seam"></i> Products
                        </x-nav-link>
                    @endcan
                    @can('purchases.manage')
                        <x-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*') || request()->routeIs('suppliers.*')">
                            <i class="bi bi-bag-plus"></i> Purchases
                        </x-nav-link>
                    @endcan
                    @can('sales.manage')
                        <x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*') || request()->routeIs('customers.*')">
                            <i class="bi bi-receipt"></i> Sales
                        </x-nav-link>
                    @endcan
                    @can('credits.manage')
                        <x-nav-link :href="route('credit-payments.index')" :active="request()->routeIs('credit-payments.*')">
                            <i class="bi bi-wallet2"></i> Credit
                        </x-nav-link>
                    @endcan
                    @can('reports.view')
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                            <i class="bi bi-bar-chart"></i> Reports
                        </x-nav-link>
                    @endcan
                    @can('users.manage')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            <i class="bi bi-people"></i> Users
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden xl:ms-6 xl:flex xl:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-700 shadow-sm transition duration-150 ease-in-out hover:bg-slate-50 hover:text-slate-900 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center xl:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-700 focus:bg-slate-100 focus:text-slate-700 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden overflow-hidden rounded-b-2xl border-t border-indigo-100/80 bg-gradient-to-b from-white/95 to-indigo-50/90 xl:hidden">
        <div class="space-y-1 px-3 py-3">
            @can('dashboard.view')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </x-responsive-nav-link>
            @endcan
            <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">
                <i class="bi bi-camera-reels me-2"></i> Attendance
            </x-responsive-nav-link>
            @can('categories.manage')
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    <i class="bi bi-tags me-2"></i> Categories
                </x-responsive-nav-link>
            @endcan
            @can('products.manage')
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    <i class="bi bi-box-seam me-2"></i> Products
                </x-responsive-nav-link>
            @endcan
            @can('purchases.manage')
                <x-responsive-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*') || request()->routeIs('suppliers.*')">
                    <i class="bi bi-bag-plus me-2"></i> Purchases
                </x-responsive-nav-link>
            @endcan
            @can('sales.manage')
                <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*') || request()->routeIs('customers.*')">
                    <i class="bi bi-receipt me-2"></i> Sales
                </x-responsive-nav-link>
            @endcan
            @can('credits.manage')
                <x-responsive-nav-link :href="route('credit-payments.index')" :active="request()->routeIs('credit-payments.*')">
                    <i class="bi bi-wallet2 me-2"></i> Credit
                </x-responsive-nav-link>
            @endcan
            @can('reports.view')
                <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                    <i class="bi bi-bar-chart me-2"></i> Reports
                </x-responsive-nav-link>
            @endcan
            @can('users.manage')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <i class="bi bi-people me-2"></i> Users
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-indigo-100/80 bg-white/70 px-3 pb-3 pt-4">
            <div class="px-4">
                <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
