<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\SalleValidator;

final class CreerSalleDTOBuilder
{
    private ?string $nom = null;
    private ?string $batiment = null;
    private ?int $capacite = null;
    private ?string $type = null;
    private bool $active = true;


    public function __construct(
        private ?SalleValidator $salleValidator = null
    ) {}

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

    public function fromArray(array $data): self
    {
        if (isset($data['nom'])) {
            $this->setNom((string) $data['nom']);
        }
        if (isset($data['batiment'])) {
            $this->setBatiment((string) $data['batiment']);
        }
        if (isset($data['capacite'])) {
            $this->setCapacite((int) $data['capacite']);
        }
        if (isset($data['type'])) {
            $this->setType((string) $data['type']);
        }
        if (isset($data['active'])) {
            $this->setActive((bool) $data['active']);
        }

        return $this;
    }

    public function reset(): self
    {
        $this->nom = null;
        $this->batiment = null;
        $this->capacite = null;
        $this->type = null;
        $this->active = true;

        return $this;
    }

    public function build(): CreerSalleDTO
    {
        $data = [
            'nom' => $this->nom,
            'batiment' => $this->batiment,
            'capacite' => $this->capacite,
            'type' => $this->type,
            'active' => $this->active,
        ];

        $dto = CreerSalleDTO::fromArray(
            $data,
            $this->salleValidator
        );

        $this->reset();

        return $dto;
    }
}
