<?php

namespace App\Enums;

enum ServiceProvisionTypeEnum: string
{
    case HOTEL_RESERVATION = 'Réservation Hôtel';
    case INSURANCE = 'Assurance';
    case TRAINING_CONTRACT = 'Contrat de formation';
    case SUPPORT = 'Accompagnement';
    case TELEPHONE = 'Téléphone';
    case OTHER = 'Autre';
}