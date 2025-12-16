<?php $__env->startSection('title', __('Prohibido')); ?>
<?php $__env->startSection('code', '403'); ?>
<?php $__env->startSection('message'); ?>
    <div style="text-align: center; font-family: sans-serif;">
        <h1 style="font-size: 24px; color: #333;">Acceso Denegado</h1>
        <p style="font-size: 16px; color: #666;">
            No tienes los permisos necesarios para acceder a esta página.
        </p>
        <a href="<?php echo e(app('router')->has('filament.admin.pages.dashboard') ? route('filament.admin.pages.dashboard') : url('/')); ?>"
           style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #1E73BE; color: white; text-decoration: none; border-radius: 5px;"
        >
            Volver al Inicio
        </a>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('errors::minimal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u530748807/domains/app.laboratorioclinicomerino.com/resources/views/errors/403.blade.php ENDPATH**/ ?>