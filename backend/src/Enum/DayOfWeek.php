<?php

declare(strict_types=1);

namespace App\Enum;

enum DayOfWeek: string
{
    case Lundi = 'Lundi';
    case Mardi = 'Mardi';
    case Mercredi = 'Mercredi';
    case Jeudi = 'Jeudi';
    case Vendredi = 'Vendredi';
    case Samedi = 'Samedi';
    case Dimanche = 'Dimanche';
}
