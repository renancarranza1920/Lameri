<?php
// app/Enums/RecipienteEnum.php

namespace App\Enums;

use Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;

enum RecipienteEnum: string
{
    use IsKanbanStatus;

    case rojo = 'rojo';
    case celeste = 'celeste';
    case morado = 'morado';
    case orina = 'orina';
    case heces = 'heces';
    case hisopado = 'hisopado';
    case extra = 'extra';
   
    
   public function getTitle(): string
   {
    return $this->name;
   }
}