<?php
// app/Enums/RecipienteEnum.php

namespace App\Enums;

use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;

enum RecipienteEnum: string
{
    use IsKanbanStatus;

    case rojo = 'rojo';
    case lila = 'lila';
    case celeste = 'celeste';
    case amarillo = 'amarillo';
    case verde = 'verde';
    case azul = 'azul';
   
    
   public function getTitle(): string
   {
    return $this->name;
   }
}