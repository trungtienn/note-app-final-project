<section>
    <header>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ __('User Preferences') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Customize your note application appearance, default colors, and note typography.') }}
    </p>
</header>

    @php
        $preferences = auth()->user()->preferences ?? ['theme' => 'light', 'font_size' => 'base', 'default_color' => '#ffffff'];
    @endphp

    <form method="post" action="{{ route('preferences.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Theme Selection -->
        <div>
            <x-input-label for="theme" :value="__('Theme Mode')" />
            <select id="theme" name="theme" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="light" {{ ($preferences['theme'] ?? '') === 'light' ? 'selected' : '' }}>Light Mode (Sáng)</option>
                <option value="dark" {{ ($preferences['theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark Mode (Tối)</option>
            </select>
        </div>

        <!-- Note Font Size -->
        <div>
            <x-input-label for="font_size" :value="__('Note Content Font Size')" />
            <select id="font_size" name="font_size" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="small" {{ ($preferences['font_size'] ?? '') === 'small' ? 'selected' : '' }}>Nhỏ (Small - 14px)</option>
                <option value="base" {{ ($preferences['font_size'] ?? '') === 'base' ? 'selected' : '' }}>Tiêu chuẩn (Medium - 16px)</option>
                <option value="large" {{ ($preferences['font_size'] ?? '') === 'large' ? 'selected' : '' }}>Lớn (Large - 18px)</option>
            </select>
        </div>

        <!-- Default Note Color -->
        <div>
            <x-input-label for="default_color" :value="__('Default Note Background Color')" />
            <div class="flex items-center gap-3 mt-1">
                <input id="default_color" name="default_color" type="color" value="{{ $preferences['default_color'] ?? '#ffffff' }}" class="h-10 w-20 border border-gray-300 rounded cursor-pointer" />
                <span class="text-sm text-gray-500">Màu mặc định cho các ghi chú tạo mới</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Preferences') }}</x-primary-button>

            @if (session('status') === 'preferences-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm text-green-600 font-medium">
                    {{ __('Preferences saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>