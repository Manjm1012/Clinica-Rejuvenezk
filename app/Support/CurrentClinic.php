<?php

namespace App\Support;

use App\Models\Clinic;

class CurrentClinic
{
    public function __construct(
        private ?Clinic $clinic = null
    ) {
    }

    public function clinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function id(): ?int
    {
        return $this->clinic?->id;
    }
}
