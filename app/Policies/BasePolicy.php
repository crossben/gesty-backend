<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user belongs to the same school as the model.
     */
    protected function belongsToUserSchool(User $user, $model): bool
    {
        return $user->school_id === $model->school_id;
    }

    /**
     * Common check for any view/update/delete action.
     */
    public function viewUpdateDelete(User $user, $model): bool
    {
        return $this->belongsToUserSchool($user, $model);
    }
}
