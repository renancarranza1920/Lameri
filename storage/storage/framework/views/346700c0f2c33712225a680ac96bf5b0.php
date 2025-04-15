<div x-data="{ darkMode: false }"
     x-init="
        darkMode = document.documentElement.classList.contains('dark');
        new MutationObserver(() => {
            darkMode = document.documentElement.classList.contains('dark');
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
     ">
    
    <template x-if="darkMode">
        <?php echo $__env->make('vendor.filament-panels.components.logo-claro', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </template>

    <template x-if="!darkMode">
        <?php echo $__env->make('vendor.filament-panels.components.logo-oscuro', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </template>

</div>
<?php /**PATH C:\xampp\htdocs\lameri\resources\views/vendor/filament-panels/components/logo.blade.php ENDPATH**/ ?>