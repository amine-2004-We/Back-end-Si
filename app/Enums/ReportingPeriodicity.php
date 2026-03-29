<?php

namespace App\Enums;

enum ReportingPeriodicity: string
{
    case Quarterly = 'Trimestriel';
    case SemiAnnual = 'Semestriel';
    case Annual = 'Annuel';
}
