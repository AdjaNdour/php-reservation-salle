<?php

namespace Database;

use App\Model\Salle;
use App\Model\Utilisateur;

class Seed
{
    public function run(): void
    {
        echo "Initialisation des données de base (Seeding)...\n";

        $sallesInitiales = [
            [
                'nom'      => 'Amphithéâtre A',
                'batiment' => 'Bâtiment Principal',
                'capacite' => 250,
                'type'     => 'amphitheatre',
                'active'   => true,
            ],
            [
                'nom'      => 'Salle B12',
                'batiment' => 'Bâtiment B',
                'capacite' => 40,
                'type'     => 'cours',
                'active'   => true,
            ],
            [
                'nom'      => 'Laboratoire Chimie',
                'batiment' => 'Bâtiment Sciences',
                'capacite' => 24,
                'type'     => 'laboratoire',
                'active'   => true,
            ],
            [
                'nom'      => 'Salle Informatique 1',
                'batiment' => 'Bâtiment Informatique',
                'capacite' => 30,
                'type'     => 'informatique',
                'active'   => true,
            ],
            [
                'nom'      => 'Salle de réunion',
                'batiment' => 'Bâtiment Administratif',
                'capacite' => 12,
                'type'     => 'reunion',
                'active'   => true,
            ],
        ];

        foreach ($sallesInitiales as $data) {
            $salle = Salle::updateOrCreate(['nom' => $data['nom']], $data);

            echo sprintf(
                " Salle '%s' ID: %d inserée.\n",
                $salle->nom,
                $salle->id
            );
        }

        echo "\nInitialisation des comptes utilisateurs de test...\n";

        $utilisateursInitiaux = [
            [
                'nom'      => 'Adja Ndour',
                'email'    => 'adja@univ.sn',
                'password' => password_hash('passer123', PASSWORD_BCRYPT),
                'role'     => Utilisateur::ROLE_ENSEIGNANT,
            ],
            [
                'nom'      => 'Dr. Mariama Diop',
                'email'    => 'mariama@univ.sn',
                'password' => password_hash('passer123', PASSWORD_BCRYPT),
                'role'     => Utilisateur::ROLE_RESPONSABLE,
            ],
            [
                'nom'      => 'Administrateur',
                'email'    => 'admin@univ.sn',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'role'     => Utilisateur::ROLE_ADMIN,
            ],
        ];

        foreach ($utilisateursInitiaux as $userData) {
            $user = Utilisateur::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            echo sprintf(
                " Utilisateur '%s' (%s) [Rôle: %s] ID: %d initialisé.\n",
                $user->nom,
                $user->email,
                $user->role,
                $user->id
            );
        }

        echo "\nDonnées initiales insérées avec succès sans doublons !\n";
    }
}
