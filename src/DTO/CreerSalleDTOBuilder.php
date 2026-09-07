<?php

namespace App\DTO;

final class CreersALLEDTOBuilder
{
    private ?string $nom = null;
    private ?string $batiment = null;
    private ?int $capacite = null;
    private ?string $type = null;
    private bool $active = true;

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function setBatiment(string $batiment): self
    {
        $this->batiment = $batiment;
        return $this;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function build(): CreerSalleDTO
    {
        return new CreerSalleDTO(
            nom: $this->nom,
            batiment: $this->batiment,
            capacite: $this->capacite,
            type: $this->type,
            active: $this->active
        );
    }
}