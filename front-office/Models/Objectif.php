<?php
/**
 * Modèle Objectif - YallaQuest (Architecture MVC)
 * Conforme au diagramme UML du projet :
 * - IdO (int)
 * - titre (string)
 * - note (string)
 * - date_debut (string/datetime)
 * - date_fin (string/datetime)
 * - statut (string) : 'a_venir', 'en_cours', 'atteint'
 */
class Objectif
{
    private ?int $IdO;
    private ?string $titre;
    private ?string $note;
    private ?string $date_debut;
    private ?string $date_fin;
    private ?string $statut;
    private ?string $categorie;
    private ?int $xpRecompense;

    public function __construct(
        ?string $titre = null,
        ?string $note = null,
        ?string $date_debut = null,
        ?string $date_fin = null,
        ?string $statut = 'en_cours',
        ?int $IdO = null,
        ?string $categorie = 'Activité physique',
        ?int $xpRecompense = 50
    ) {
        $this->IdO = $IdO;
        $this->titre = $titre;
        $this->note = $note;
        $this->date_debut = $date_debut ?? date('Y-m-d H:i');
        $this->date_fin = $date_fin ?? date('Y-m-d H:i', strtotime('+7 days'));
        $this->statut = $statut ?? 'en_cours';
        $this->categorie = $categorie ?? 'Activité physique';
        $this->xpRecompense = $xpRecompense ?? 50;
    }

    // Getters
    public function getIdO(): ?int { return $this->IdO; }
    public function getId(): ?int { return $this->IdO; }
    public function getTitre(): ?string { return $this->titre; }
    public function getNote(): ?string { return $this->note; }
    public function getDateDebut(): ?string { return $this->date_debut; }
    public function getDateFin(): ?string { return $this->date_fin; }
    public function getStatut(): ?string { return $this->statut; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function getXpRecompense(): ?int { return $this->xpRecompense; }

    // Setters
    public function setIdO(?int $IdO): void { $this->IdO = $IdO; }
    public function setId(?int $id): void { $this->IdO = $id; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }
    public function setNote(?string $note): void { $this->note = $note; }
    public function setDateDebut(?string $date_debut): void { $this->date_debut = $date_debut; }
    public function setDateFin(?string $date_fin): void { $this->date_fin = $date_fin; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
    public function setCategorie(?string $categorie): void { $this->categorie = $categorie; }
    public function setXpRecompense(?int $xp): void { $this->xpRecompense = $xp; }
}
?>