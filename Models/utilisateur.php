<?php
/* =====================================================================
   [DARINE · MVC UTILISATEUR] Modèle Utilisateur (table `utilisateur`)
   ---------------------------------------------------------------------
   Règle du workshop (1.3) : le Modèle représente UNE donnée.
   Il ne contient QUE des attributs privés + getters/setters + fromArray().
   Aucune requête SQL, aucune logique métier : tout ça est dans
   Controllers/utilisateurC.php.

   Corrections apportées à la version précédente :
   - getDerniereProlongationAt(), getDateObtentionTotem() et getIdTotem()
     renvoyaient $xp / $niveau au lieu du bon attribut.
   - Ajout des colonnes manquantes de la table : role et statut.
   - niveau démarre à 1 (valeur par défaut de la table), pas à 0.
   - id_totem, img_profil et les dates peuvent être NULL en base
     (types "?"), et id_totem n'est plus forcé à 1 (clé étrangère).
   - "int $id = NULL" remplacé par "?int $id = null" (PHP 8.4).
   - Ajout de fromArray() : transforme une ligne SQL en objet (workshop 3.1).

   ---------------------------------------------------------------------
   [DARINE · GUIDE] COMMENT UTILISER CE MODÈLE
   ---------------------------------------------------------------------
   - Transformer une ligne SQL en objet (dans le contrôleur) :
       $row = $stmt->fetch();
       $u   = Utilisateur::fromArray($row);
   - Créer un objet pour un INSERT / UPDATE (style workshop) :
       $u = new Utilisateur();
       $u->setNom($_POST['nom']);
       $u->setPrenom($_POST['prenom']);
       $u->setEmail($_POST['mail']);
       $u->setMp(password_hash(...));      // le hash, jamais le mot de passe
       $u->setRole('user');
   - Correspondance attribut -> colonne de la table :
       id -> id_utilisateur, email -> mail, mp -> mot_de_passe_hash,
       xp -> total_xp ; les autres ont le même nom.
   - Valeurs possibles : role = 'user' | 'admin', statut = 'actif' | 'bloque'
   ===================================================================== */
class Utilisateur
{
    // ------------------------------------------------------------
    // ATTRIBUTES (mêmes noms que les colonnes de la table)
    // ------------------------------------------------------------
    private ?int $id;                                    // id_utilisateur
    private string $nom;
    private string $prenom;
    private string $email;                               // mail
    private string $mp;                                  // mot_de_passe_hash
    private string $role = 'user';                       // 'user' ou 'admin'
    private string $statut = 'actif';                    // 'actif' ou 'bloque'
    private int $xp = 0;                                 // total_xp
    private int $niveau = 1;
    private ?DateTimeImmutable $derniere_prolongation_at = null;
    private ?int $id_totem = null;
    private ?DateTimeImmutable $date_obtention_totem = null;
    private ?string $img_profil = 'renard';
    private ?int $id_guilde = null;

    // ------------------------------------------------------------
    // CONSTRUCTOR
    // Tous les paramètres sont facultatifs :
    // - new Utilisateur() puis setters / fromArray()  (style workshop)
    // - l'ancien appel de modifier-profil.php continue de fonctionner :
    //   new Utilisateur($nom, $prenom, $email, $hash, $date, "", $id)
    // ------------------------------------------------------------
    public function __construct(
        string $nom = '',
        string $prenom = '',
        string $email = '',
        string $mp = '',
        ?DateTimeImmutable $derniere_prolongation_at = null,
        ?string $img_profil = 'renard',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mp = $mp;
        $this->derniere_prolongation_at = $derniere_prolongation_at;
        $this->img_profil = ($img_profil === '' || $img_profil === null) ? 'renard' : $img_profil;
    }

    // ------------------------------------------------------------
    // GETTERS
    // ------------------------------------------------------------
    public function getId(): ?int
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

    public function getMail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        $fullName = trim($this->prenom . ' ' . $this->nom);
        return $fullName !== '' ? $fullName : $this->email;
    }

    public function getMp(): string
    {
        return $this->mp;
    }

    public function getPassword(): string
    {
        return $this->mp;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function getNiveau(): int
    {
        return $this->niveau;
    }

    public function getDerniereProlongationAt(): ?DateTimeImmutable
    {
        return $this->derniere_prolongation_at;
    }

    public function getIdTotem(): ?int
    {
        return $this->id_totem;
    }

    public function getDateObtentionTotem(): ?DateTimeImmutable
    {
        return $this->date_obtention_totem;
    }

    public function getImgProfil(): ?string
    {
        return $this->img_profil ?? 'renard';
    }

    public function getIdGuilde(): ?int
    {
        return $this->id_guilde;
    }

    // ------------------------------------------------------------
    // SETTERS
    // ------------------------------------------------------------
    public function setId(?int $id): void
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

    public function setMp(string $mp): void
    {
        $this->mp = $mp;
    }

    public function setPassword(string $password): void
    {
        $this->mp = $password;
    }

    public function setMail(string $mail): void
    {
        $this->email = $mail;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    public function setNiveau(int $niveau): void
    {
        $this->niveau = $niveau;
    }

    public function setDerniereProlongationAt(?DateTimeImmutable $derniere_prolongation_at): void
    {
        $this->derniere_prolongation_at = $derniere_prolongation_at;
    }

    public function setIdTotem(?int $id_totem): void
    {
        $this->id_totem = $id_totem;
    }

    public function setDateObtentionTotem(?DateTimeImmutable $date_obtention_totem): void
    {
        $this->date_obtention_totem = $date_obtention_totem;
    }

    public function setImgProfil(?string $img_profil): void
    {
        $this->img_profil = ($img_profil === '' || $img_profil === null) ? 'renard' : $img_profil;
    }

    public function setIdGuilde(?int $id_guilde): void
    {
        $this->id_guilde = $id_guilde;
    }

    // ------------------------------------------------------------
    // CONVERT ARRAY TO OBJECT (workshop 3.1)
    // $row = une ligne renvoyée par PDO (colonnes de la table utilisateur)
    // ------------------------------------------------------------
    public static function fromArray(array $row): self
    {
        $utilisateur = new self();
        $utilisateur->setId(isset($row['id_utilisateur']) ? (int) $row['id_utilisateur'] : null);
        $utilisateur->setNom((string) ($row['nom'] ?? ''));
        $utilisateur->setPrenom((string) ($row['prenom'] ?? ''));
        $utilisateur->setEmail((string) ($row['mail'] ?? ''));
        $utilisateur->setMp((string) ($row['mot_de_passe_hash'] ?? ''));
        $utilisateur->setRole((string) ($row['role'] ?? 'user'));
        $utilisateur->setStatut((string) ($row['statut'] ?? 'actif'));
        $utilisateur->setXp((int) ($row['total_xp'] ?? 0));
        $utilisateur->setNiveau((int) ($row['niveau'] ?? 1));
        $utilisateur->setDerniereProlongationAt(
            !empty($row['derniere_prolongation_at']) ? new DateTimeImmutable($row['derniere_prolongation_at']) : null
        );
        $utilisateur->setIdTotem(isset($row['id_totem']) ? (int) $row['id_totem'] : null);
        $utilisateur->setDateObtentionTotem(
            !empty($row['date_obtention_totem']) ? new DateTimeImmutable($row['date_obtention_totem']) : null
        );
        $utilisateur->setImgProfil(isset($row['img_profil']) ? (string) $row['img_profil'] : null);
        $utilisateur->setIdGuilde(isset($row['id_guilde']) ? (int) $row['id_guilde'] : null);
        return $utilisateur;
    }
}
