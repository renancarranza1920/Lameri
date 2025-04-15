<div x-data="{ darkMode: false }"
     x-init="
        darkMode = document.documentElement.classList.contains('dark');
        new MutationObserver(() => {
            darkMode = document.documentElement.classList.contains('dark');
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
     ">
    
    <template x-if="darkMode">
        @include('vendor.filament-panels.components.logo-claro')
    </template>

    <template x-if="!darkMode">
        @include('vendor.filament-panels.components.logo-oscuro')
    </template>

</div>
