<h3 class="mb-2 px-4 py-2 flex items-center justify-between font-semibold text-md text-gray-900 dark:text-gray-100 bg-primary-200 dark:bg-gray-900 rounded-lg shadow-lg dark:shadow-[0_4px_6px_rgba(255,255,255,0.1)] dark:border dark:border-gray-600">
    <div class="flex items-center">
        <span class="text-primary-600 dark:text-primary-400 mr-2">❖</span> 
        <span><?php echo e($status['title']); ?></span>
        <span class="text-xs font-medium ml-3 bg-primary-100 dark:bg-primary-800 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full shadow-sm dark:shadow-[0_2px_4px_rgba(255,255,255,0.1)]">
            <?php echo e(count($status['records'] ?? [])); ?>

        </span>
    </div>

    <!-- Botón imprimir grupo -->
    <button
        wire:click="printGroup('<?php echo e($status['id']); ?>')"
        type="button"
        <?php if(count($status['records'] ?? []) === 0): ?> disabled class="opacity-50 cursor-not-allowed" <?php endif; ?>
        class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
        title="Imprimir todas las etiquetas de este grupo"
    >
        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-printer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
    </button>
</h3><?php /**PATH /home/u530748807/domains/app.laboratorioclinicomerino.com/resources/views/vendor/filament-kanban/kanban-header.blade.php ENDPATH**/ ?>