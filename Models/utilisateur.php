<?php
/* =====================================================================
   [DARINE · MVC UTILISATEUR] Modèle Utilisateur (table `utilisateur`)
   Même forme que UserModel du workshop (partie 3.1) :
   attributs privés + getters/setters + fromArray(). Aucune requête SQL.

   ---------------------------------------------------------------------
   [DARINE · GUIDE] COMMENT UTILISER CE MODÈLE
   ---------------------------------------------------------------------
   - Ligne SQL -> objet (dans le contrôleur) :
       $u = Utilisateur::fromArray($row);
   - Créer un objet pour un INSERT / UPDATE (comme register() du workshop) :
       $u = new Utilisateur();
       $u->setNom($nom);
       $u->setPrenom($prenom);
       $u->setEmail($email);
       $u->setPassword(password_hash($motDePasse, PASSWORD_DEFAULT));
       $u->setRole('user');
   - Attribut -> colonne : id -> id_utilisateur, email -> mail,
     password -> mot_de_passe_hash, totalXp -> total_xp, ...
   - Valeurs possibles : role = 'user' | 'admin', statut = 'actif' | 'bloque'

   [INTEGRATION] Le constructeur vide de Darine et les anciens appels
   positionnels de Houssem, Amine et Dorra restent acceptés.
   getPassword()/getMp() et getTotalXp()/getXp() lisent les mêmes données.
   ===================================================================== */
class Utilisateur
{
    // ------------------------------------------------------------
    // ATTRIBUTES
    // ------------------------------------------------------------
    private int $id = 0;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $password = '';
    private string $imgProfil = '';
    private string $role = 'user';
    private string $statut = 'actif';
    private int $totalXp = 0;
    private int $niveau = 1;
    private string $derniereProlongationAt = '';
    private int $idTotem = 0;
    private string $dateObtentionTotem = '';

    // ------------------------------------------------------------
    // CONSTRUCTOR
    // ------------------------------------------------------------
    public function __construct(string $nom = '', string $prenom = '', string $email = '', string $mp = '', string|DateTimeImmutable $imageOuDate = '', string|int|null $imageOuId = null, ?int $id = null)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->password = $mp;
        if ($imageOuDate instanceof DateTimeImmutable) {
            $this->derniereProlongationAt = $imageOuDate->format('Y-m-d H:i:s');
            $this->imgProfil = (string) ($imageOuId ?? '');
            $this->id = $id ?? 0;
        } else {
            $this->imgProfil = $imageOuDate;
            $this->id = is_numeric($imageOuId) ? (int) $imageOuId : ($id ?? 0);
        }
    }

    // ------------------------------------------------------------
    // GETTERS
    // ------------------------------------------------------------
    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getTotalXp(): int
    {
        return $this->totalXp;
    }

    public function getNiveau(): int
    {
        return $this->niveau;
    }

    public function getDerniereProlongationAt(): string
    {
        return $this->derniereProlongationAt;
    }

    public function getIdTotem(): int
    {
        return $this->idTotem;
    }

    public function getDateObtentionTotem(): string
    {
        return $this->dateObtentionTotem;
    }

    // ------------------------------------------------------------
    // SETTERS
    // ------------------------------------------------------------
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function setTotalXp(int $totalXp): void
    {
        $this->totalXp = $totalXp;
    }

    public function setNiveau(int $niveau): void
    {
        $this->niveau = $niveau;
    }

    public function setDerniereProlongationAt(string|DateTimeImmutable $derniereProlongationAt): void
    {
        $this->derniereProlongationAt = $derniereProlongationAt instanceof DateTimeImmutable ? $derniereProlongationAt->format('Y-m-d H:i:s') : $derniereProlongationAt;
    }

    public function setIdTotem(int $idTotem): void
    {
        $this->idTotem = $idTotem;
    }

    public function setDateObtentionTotem(string|DateTimeImmutable $dateObtentionTotem): void
    {
        $this->dateObtentionTotem = $dateObtentionTotem instanceof DateTimeImmutable ? $dateObtentionTotem->format('Y-m-d') : $dateObtentionTotem;
    }

    // ------------------------------------------------------------
    // CONVERT ARRAY TO OBJECT
    // ------------------------------------------------------------
    public static function fromArray(array $row): self
    {
        $utilisateur = new self();
        $utilisateur->setId((int) ($row['id_utilisateur'] ?? 0));
        $utilisateur->setNom((string) ($row['nom'] ?? ''));
        $utilisateur->setPrenom((string) ($row['prenom'] ?? ''));
        $utilisateur->setEmail((string) ($row['mail'] ?? ''));
        $utilisateur->setPassword((string) ($row['mot_de_passe_hash'] ?? ''));
        $utilisateur->setRole((string) ($row['role'] ?? 'user'));
        $utilisateur->setStatut((string) ($row['statut'] ?? 'actif'));
        $utilisateur->setTotalXp((int) ($row['total_xp'] ?? 0));
        $utilisateur->setNiveau((int) ($row['niveau'] ?? 1));
        $utilisateur->setDerniereProlongationAt((string) ($row['derniere_prolongation_at'] ?? ''));
        $utilisateur->setIdTotem((int) ($row['id_totem'] ?? 0));
        $utilisateur->setDateObtentionTotem((string) ($row['date_obtention_totem'] ?? ''));
        $utilisateur->setImgProfil((string) ($row['img_profil'] ?? ''));
        return $utilisateur;
    }
    // Compatibilité des getters/setters utilisés par les différentes contributions.
    public function getMp(): string { return $this->getPassword(); }
    public function setMp(string $mp): void { $this->setPassword($mp); }
    public function getXp(): int { return $this->getTotalXp(); }
    public function setXp(int $xp): void { $this->setTotalXp($xp); }
    public function getImgProfil(): string { return $this->imgProfil; }
    public function setImgProfil(string $imgProfil): void { $this->imgProfil = $imgProfil; }

}
