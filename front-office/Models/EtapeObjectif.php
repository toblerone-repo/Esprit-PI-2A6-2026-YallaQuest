<?php
/**
 * Modèle EtapeObjectif - YallaQuest (Architecture MVC)
 * Conforme au diagramme UML du projet :
 * - IdEO (int)
 * - titre (string)
 * - est_validee (bool)
 * - date_validation (datetime / string)
 * - id_objectif (int) : clé étrangère vers objectif (IdO)
 */
class EtapeObjectif
{
    private ?int $IdEO;
    private ?string $titre;
    private bool $est_validee;
    private ?string $date_validation;
    private ?int $id_objectif;

    public function __construct(
        ?string $titre = null,
        ?int $id_objectif = null,
        bool $est_validee = false,
        ?string $date_validation = null,
        ?int $IdEO = null
    ) {
        $this->IdEO = $IdEO;
        $this->titre = $titre;
        $this->id_objectif = $id_objectif;
        $this->est_validee = $est_validee;
        $this->date_validation = $date_validation;
    }

    // Getters
    public function getIdEO(): ?int { return $this->IdEO; }
    public function getId(): ?int { return $this->IdEO; }
    public function getTitre(): ?string { return $this->titre; }
    public function getEstValidee(): bool { return $this->est_validee; }
    public function getDateValidation(): ?string { return $this->date_validation; }
    public function getIdObjectif(): ?int { return $this->id_objectif; }

    // Setters
    public function setIdEO(?int $IdEO): void { $this->IdEO = $IdEO; }
    public function setId(?int $id): void { $this->IdEO = $id; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }
    public function setEstValidee(bool $val): void { 
        $this->est_validee = $val;
        if ($val && empty($this->date_validation)) {
            $this->date_validation = date('Y-m-d H:i:s');
        } elseif (!$val) {
            $this->date_validation = null;
        }
    }
    public function setDateValidation(?string $date): void { $this->date_validation = $date; }
    public function setIdObjectif(?int $id_objectif): void { $this->id_objectif = $id_objectif; }
}
?>