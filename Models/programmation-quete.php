<?php

class ProgrammationQuete
{
    private ?int $id_prog;
    private int $id_quete;
    private int $id_utilisateur;
    private DateTimeImmutable $dateDebut;
    private DateTimeImmutable $dateFin;
    private string $statut;

    function __construct(int $id_quete, int $id_utilisateur, int $id_prog = NULL)
    {
        $this->id_prog = $id_prog;
        $this->id_quete = $id_quete;
        $this->id_utilisateur = $id_utilisateur;
        $this->dateDebut = new DateTimeImmutable("today");
        $this->dateFin = $this->dateDebut->add(new DateInterval("P2D"));
        $this->statut = "programmee";
    }

    function getIdProg()
    {
        return $this->id_prog;
    }

    function getIdQuete()
    {
        return $this->id_quete;
    }



    function getIdUtilisateur()
    {
        return $this->id_utilisateur;
    }

    function getDateDebut()
    {
        return $this->dateDebut;
    }

    function getDateFin()
    {
        return $this->dateFin;
    }

    function setDateFin(DateTimeImmutable $dateFin)
    {
        $this->dateFin = $dateFin;
    }

    function getStatut()
    {
        return $this->statut;
    }

    function setStatut(string $statut)
    {
        $this->statut = $statut;
    }
}
