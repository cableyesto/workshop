<?php

declare(strict_types=1);

namespace App\Enum;

enum InterventionType: string
{
    case Diagnostic = 'Diagnostic';
    case Repair = 'Repair';
}
