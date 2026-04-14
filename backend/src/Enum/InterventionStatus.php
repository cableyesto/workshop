<?php

declare(strict_types=1);

namespace App\Enum;

enum InterventionStatus: string
{
    case Assigned = 'Assigned';
    case InProgress = 'In Progress';
    case Paused = 'Paused';
    case Stopped = 'Stopped';
}
