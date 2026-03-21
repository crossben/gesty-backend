<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Grade $grade): bool
    {
        return $this->viewUpdateDelete($user, $grade);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Grade $grade): bool
    {
        return $this->viewUpdateDelete($user, $grade);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->viewUpdateDelete($user, $grade);
    }
}
