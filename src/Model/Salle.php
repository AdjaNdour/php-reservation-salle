<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salle extends Model
{
    public const TYPES_AUTORISES = [
        'cours',
        'informatique',
        'laboratoire',
        'amphitheatre',
        'reunion',
    ];

    protected $table = 'salles';

    protected $fillable = [
        'nom',
        'batiment',
        'capacite',
        'type',
        'active',
    ];

    protected $casts = [
        'capacite' => 'integer',
        'active' => 'boolean',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'salle_id');
    }

    public function estActive(): bool
    {
        return (bool) $this->active;
    }
}