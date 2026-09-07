<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Reservation extends Model
{
    public const STATUT_CONFIRMEE = 'confirmée';
    public const STATUT_ANNULEE = 'annulée';

    public const STATUTS_AUTORISES = [
        self::STATUT_CONFIRMEE,
        self::STATUT_ANNULEE,
    ];

    protected $table = 'reservations';

    protected $fillable = [
        'salle_id',
        'responsable',
        'email',
        'motif',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'salle_id'   => 'integer',
        'date_debut' => 'datetime',
        'date_fin'   => 'datetime',
    ];

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'salle_id');
    }

    public function estConfirmee(): bool
    {
        return $this->statut === self::STATUT_CONFIRMEE;
    }

    public function estAnnulee(): bool
    {
        return $this->statut === self::STATUT_ANNULEE;
    }
}
