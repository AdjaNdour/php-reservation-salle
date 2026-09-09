<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_RESPONSABLE = 'responsable';
    public const ROLE_ENSEIGNANT = 'enseignant';

    public const ROLES_AUTORISES = [
        self::ROLE_ADMIN,
        self::ROLE_RESPONSABLE,
        self::ROLE_ENSEIGNANT,
    ];

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function verifierMotDePasse(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    public function aRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function estAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function estResponsable(): bool
    {
        return in_array($this->role, [self::ROLE_RESPONSABLE, self::ROLE_ADMIN, self::ROLE_ENSEIGNANT], true);
    }
}
