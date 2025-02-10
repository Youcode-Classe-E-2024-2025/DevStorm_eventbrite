<?php

namespace App\enums;

enum Role: string
{
    case ADMIN = 'admin';
    case ORGANISATEUR = 'organisateur';
    case PARTICIPANT = 'participant';
}