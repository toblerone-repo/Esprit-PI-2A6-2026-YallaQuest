<?php
/**
 * Modèle Utilisateur - YallaQuest (Architecture MVC)
 */
class Utilisateur
{
    private ?int $id;
    private ?string $pseudo;
    private ?string $nom;
    private ?string $prenom;
    private ?string $email;
    private ?string $mot_de_passe;
    private ?string $totem;
    private int $ExpU;
    private int $Niveau;
    private string $role;
    private string $statut;

    public function __construct(
        ?string $pseudo = null,
        ?string $email = null,
        ?string $mot_de_passe = null,
        ?string $nom = '',
        ?string $prenom = '',
        ?string $totem = 'Félin Géométrique',
        int $ExpU = 50,
        int $Niveau = 1,
        string $role = 'joueur',
        string $statut = 'actif',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->totem = $totem;
        $this->ExpU = $ExpU;
        $this->Niveau = $Niveau;
        $this->role = $role;
        $this->statut = $statut;
    }

    public function getId(): ?int { return $this->id; }
    public function getPseudo(): ?string { return $this->pseudo; }
    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function getEmail(): ?string { return $this->email; }
    public function getMotDePasse(): ?string { return $this->mot_de_passe; }
    public function getTotem(): ?string { return $this->totem; }
    public function getExpU(): int { return $this->ExpU; }
    public function getNiveau(): int { return $this->Niveau; }
    public function getRole(): string { return $this->role; }
    public function getStatut(): string { return $this->statut; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setPseudo(?string $pseudo): void { $this->pseudo = $pseudo; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function setMotDePasse(?string $mdp): void { $this->mot_de_passe = $mdp; }
    public function setTotem(?string $totem): void { $this->totem = $totem; }
    public function setExpU(int $xp): void { $this->ExpU = $xp; }
    public function setNiveau(int $niv): void { $this->Niveau = $niv; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
}
?>