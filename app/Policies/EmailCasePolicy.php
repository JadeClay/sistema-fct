<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailCase;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EmailCasePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmailCase');
    }

    public function view(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('View:EmailCase');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmailCase');
    }

    public function update(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('Update:EmailCase');
    }

    public function delete(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('Delete:EmailCase');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmailCase');
    }

    public function restore(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('Restore:EmailCase');
    }

    public function forceDelete(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('ForceDelete:EmailCase');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmailCase');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmailCase');
    }

    public function replicate(AuthUser $authUser, EmailCase $emailCase): bool
    {
        return $authUser->can('Replicate:EmailCase');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmailCase');
    }
}
