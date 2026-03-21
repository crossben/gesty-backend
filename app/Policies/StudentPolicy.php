<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Student $student): bool
    {
        return $this->viewUpdateDelete($user, $student);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Student $student): bool
    {
        return $this->viewUpdateDelete($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->viewUpdateDelete($user, $student);
    }
}
