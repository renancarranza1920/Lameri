<div
    id="<?php echo e($record->getKey()); ?>"
    wire:click="recordClicked('<?php echo e($record->getKey()); ?>', <?php echo e(@json_encode($record)); ?>)"
    class="record bg-white dark:bg-gray-700 rounded-lg px-3 py-2 cursor-grab font-medium text-sm text-gray-700 dark:text-gray-200 shadow-sm border border-gray-200 dark:border-gray-600"
    <?php if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}, true) < 3): ?>
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    <?php endif; ?>
>
    <?php echo e($record->{static::$recordTitleAttribute}); ?>


    <!-- Botón imprimir individual usando Livewire -->
    <button
        wire:click.stop="printSingle(<?php echo e($record->getKey()); ?>)"
        type="button"
        class="ml-2 inline-flex items-center gap-2 px-2 py-1 text-xs font-medium text-primary-700 bg-primary-100 rounded hover:bg-primary-200 dark:bg-primary-800 dark:text-primary-200"
        title="Imprimir etiqueta"
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
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
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
</div><?php /**PATH /home/u530748807/domains/app.laboratorioclinicomerino.com/resources/views/vendor/filament-kanban/kanban-record.blade.php ENDPATH**/ ?>