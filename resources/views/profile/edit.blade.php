<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl leading-tight title-gradient">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="page-wrap py-8 sm:py-10">
        <div class="space-y-6">
            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
