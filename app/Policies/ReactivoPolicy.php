<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reactivo;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReactivoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_reactivo');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reactivo $reactivo): bool
    {
        return $user->can('view_reactivo');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_reactivo');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reactivo $reactivo): bool
    {
        return $user->can('update_reactivo');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reactivo $reactivo): bool
    {
        return $user->can('delete_reactivo');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_reactivo');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Reactivo $reactivo): bool
    {
        return $user->can('force_delete_reactivo');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_reactivo');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Reactivo $reactivo): bool
    {
        return $user->can('restore_reactivo');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_reactivo');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Reactivo $reactivo): bool
    {
        return $user->can('replicate_reactivo');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_reactivo');
    }
}
