<?php
// Guildes : modèle d'une guilde (table guilde).

class Guilde
{
    private ?int $id;
    private string $nom;
    private string $description;
    private string $dateCreation;      // AAAA-MM-JJ
    private int $idCreateur;

    public function __construct(string $nom = '', string $description = '', int $idCreateur = 0, string $dateCreation = '', ?int $id = null)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->idCreateur = $idCreateur;
        $this->dateCreation = $dateCreation;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getDateCreation(): string { return $this->dateCreation; }
    public function setDateCreation(string $date): void { $this->dateCreation = $date; }

    public function getIdCreateur(): int { return $this->idCreateur; }
    public function setIdCreateur(int $id): void { $this->idCreateur = $id; }
}
