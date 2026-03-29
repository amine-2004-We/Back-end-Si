<?php

namespace App;

enum UnitStatusEnum: string
{
    case Active = 'Active';
    case Fermee = 'Fermée';
    case EnPause = 'En pause';
    case Archivee = 'Archivée';
}