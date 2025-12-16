<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'columnWidth']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status', 'columnWidth']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="<?php echo e($columnWidth); ?> h-full flex flex-col">
    <?php echo $__env->make(static::$headerView, ['status' => $status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div
        data-status-id="<?php echo e($status['id']); ?>"
        class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-3 flex flex-col gap-3"
        style="min-height: 200px;"
    >
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $status['records']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make(static::$recordView, ['record' => $record], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /home/u530748807/domains/app.laboratorioclinicomerino.com/resources/views/vendor/filament-kanban/kanban-status.blade.php ENDPATH**/ ?>