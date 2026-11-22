<?php

class Utilisateur
{

    private ?int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $mp;
    private int $xp;
    private int $niveau;
    private string $img_profil;
    private DateTimeImmutable $derniere_prolongation_at;
    private int $id_totem;
    private DateTimeImmutable $date_obtention_totem;
    private string $pseudo = ''; // [INSCRIPTION] colonne pseudo (nom d'utilisateur du workshop)

    function __construct(string $nom, string $prenom, string $email, string $mp, DateTimeImmutable $derniere_prolongation_at, string $img_profil, int $id = NULL)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mp = $mp;
        $this->xp = 0;
        $this->niveau = 0;
        $this->img_profil = $img_profil;
        $this->derniere_prolongation_at = $derniere_prolongation_at;
        $this->id_totem = 1;
        $this->date_obtention_totem = new DateTimeImmutable("today");
    }

    function getId()
    {
        return $this->id;
    }

    function getNom()
    {
        return $this->nom;
    }

    function setNom(string $nom)
    {
        $this->nom = $nom;
    }

    function getPrenom()
    {
        return $this->prenom;
    }

    function setPrenom(string $prenom)
    {
        $this->prenom = $prenom;
    }

    function getEmail()
    {
        return $this->email;
    }

    function setEmail(string $email)
    {
        $this->email = $email;
    }

    function getMp()
    {
        return $this->mp;
    }

    function setMp(string $mp)
    {
        $this->mp = $mp;
    }

    function getXp()
    {
        return $this->xp;
    }

    function setXp(int $xp)
    {
        $this->xp = $xp;
    }

    function getNiveau()
    {
        return $this->niveau;
    }

    function setNiveau(int $niveau)
    {
        $this->niveau = $niveau;
    }

    function getDerniereProlongationAt()
    {
        return $this->xp;
    }

    function setDerniereProlongationAt(DateTimeImmutable $derniere_prolongation_at)
    {
        $this->derniere_prolongation_at = $derniere_prolongation_at;
    }

    function getDateObtentionTotem()
    {
        return $this->xp;
    }

    function setDateObtentionTotem(DateTimeImmutable $date_obtention_totem)
    {
        $this->date_obtention_totem = $date_obtention_totem;
    }

    function getIdTotem()
    {
        return $this->niveau;
    }

    function setIdTotem(int $id_totem)
    {
        $this->id_totem = $id_totem;
    }

    function getImgProfil() {
        return $this->img_profil; 
    }

    function setImgProfil(string $img_profil) {
        $this->img_profil = $img_profil;
    }

    // [INSCRIPTION] pseudo
    function getPseudo()
    {
        return $this->pseudo;
    }

    function setPseudo(string $pseudo)
    {
        $this->pseudo = $pseudo;
    }
}
