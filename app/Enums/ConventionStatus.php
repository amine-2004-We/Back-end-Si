<?php

namespace App\Enums;

enum ConventionStatus: string
{
    case Draft = 'Brouillon';
    case Validated = 'Validée';
    case Signed = 'Signée';
    case InProgress = 'En cours';
    case Closed = 'Clôturée';

}
