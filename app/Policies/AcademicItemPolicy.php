<?php

namespace App\Policies;

use App\Models\AcademicItem;
use App\Models\User;

class AcademicItemPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AcademicItem $academicItem): bool
    {
        return $this->viewUpdateDelete($user, $academicItem);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AcademicItem $academicItem): bool
    {
        return $this->viewUpdateDelete($user, $academicItem);
    }

    public function delete(User $user, AcademicItem $academicItem): bool
    {
        return $this->viewUpdateDelete($user, $academicItem);
    }
}
