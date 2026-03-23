<?php

namespace App\Services;

use App\Models\School;

class TenantManager
{
    protected ?School $school = null;

    public function setSchool(School $school): void
    {
        $this->school = $school;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function getSchoolId(): ?string
    {
        return $this->school?->id;
    }
}
