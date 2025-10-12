<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorReferencia extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function reactivo(): BelongsTo
    {
        return $this->belongsTo(Reactivo::class);
    }

    public function grupoEtario(): BelongsTo
    {
        return $this->belongsTo(GrupoEtario::class);
    }
}