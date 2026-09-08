<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function verifierMotDePasse(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }
}
