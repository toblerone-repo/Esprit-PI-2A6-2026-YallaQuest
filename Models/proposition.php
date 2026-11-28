<?php
// Guildes : modèle d'une proposition de quête (table proposition).

class Proposition
{
    private ?int $id;
    private string $dateProposition;   // AAAA-MM-JJ
    private string $dateLimiteVote;    // AAAA-MM-JJ, dernier jour de vote inclus
    private int $idGuilde;
    private int $idQuete;

    public function __construct(int $idGuilde = 0, int $idQuete = 0, string $dateProposition = '', string $dateLimiteVote = '', ?int $id = null)
    {
        $this->id = $id;
        $this->idGuilde = $idGuilde;
        $this->idQuete = $idQuete;
        $this->dateProposition = $dateProposition;
        $this->dateLimiteVote = $dateLimiteVote;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getDateProposition(): string { return $this->dateProposition; }
    public function setDateProposition(string $date): void { $this->dateProposition = $date; }

    public function getDateLimiteVote(): string { return $this->dateLimiteVote; }
    public function setDateLimiteVote(string $date): void { $this->dateLimiteVote = $date; }

    public function getIdGuilde(): int { return $this->idGuilde; }
    public function setIdGuilde(int $id): void { $this->idGuilde = $id; }

    public function getIdQuete(): int { return $this->idQuete; }
    public function setIdQuete(int $id): void { $this->idQuete = $id; }
}
