<?php

namespace App;

enum UnitTypeEnum: string
{
    case Ecole = 'École';
    case PetiteSection = 'Petite section';
    case Prescolaire = 'Préscolaire';
    case Vides = '(Vides)';
}