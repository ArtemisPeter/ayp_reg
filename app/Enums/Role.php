<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Secretary = 'secretary';
    case CircuitPresident = 'circuit_president';
    case Security = 'security';
    case Tabulator = 'tabulator';
}
