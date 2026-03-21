<?php

namespace App\Policies;

use App\Models\AIReport;
use App\Models\User;

class AIReportPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AIReport $aIReport): bool
    {
        return $this->viewUpdateDelete($user, $aIReport);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AIReport $aIReport): bool
    {
        return $this->viewUpdateDelete($user, $aIReport);
    }

    public function delete(User $user, AIReport $aIReport): bool
    {
        return $this->viewUpdateDelete($user, $aIReport);
    }
}
