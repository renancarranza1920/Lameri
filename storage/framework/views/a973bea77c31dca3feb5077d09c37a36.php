<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div 
        x-data 
        wire:ignore.self 
        class="w-full pb-4"
    >
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4  px-3">
         
             <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="relative">
        <!-- Botón para imprimir toda la columna -->
     
       
        
        <div>


        <?php echo $__env->make(static::$statusView, [
            'columnWidth' => $this->getColumnWidth() ?? 'w-full'

        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
           
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        

        <div wire:ignore>
            <?php echo $__env->make(static::$scriptsView, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if (! ($disableEditModal)): ?>
        <?php if (isset($component)) { $__componentOriginal5dfada62f0dcd3dca2cae76033b65a8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5dfada62f0dcd3dca2cae76033b65a8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-kanban::components.edit-record-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-kanban::edit-record-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5dfada62f0dcd3dca2cae76033b65a8b)): ?>
<?php $attributes = $__attributesOriginal5dfada62f0dcd3dca2cae76033b65a8b; ?>
<?php unset($__attributesOriginal5dfada62f0dcd3dca2cae76033b65a8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5dfada62f0dcd3dca2cae76033b65a8b)): ?>
<?php $component = $__componentOriginal5dfada62f0dcd3dca2cae76033b65a8b; ?>
<?php unset($__componentOriginal5dfada62f0dcd3dca2cae76033b65a8b); ?>
<?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?><?php /**PATH /home/u530748807/domains/app.laboratorioclinicomerino.com/resources/views/vendor/filament-kanban/kanban-board.blade.php ENDPATH**/ ?>