<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Models/programmation-quete.php');
include_once(__DIR__ . '/../Models/quete.php');

class ProgrammationQueteC
{

    function readProgrammation(int $id_utilisateur)
    {
        $sql = "SELECT p.id_programmation, p.id_utilisateur, p.id_quete,
                       p.date_debut, p.date_fin, p.statut,
                       q.titre, q.difficulte, q.xp, co.nom AS competence
                FROM programmation AS p
                JOIN quete AS q ON p.id_quete = q.id_quete
                JOIN competence AS co ON q.id_competence = co.id_competence
                WHERE p.id_utilisateur = :id_utilisateur
                  AND p.date_fin >= CURDATE()";

        $db = config::getConnexion();

        try {
            $liste = $db->prepare($sql);
            $liste->execute([
                ':id_utilisateur' => $id_utilisateur
            ]);
            return $liste->fetchAll();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible d'accèder les programmations.",
                0,
                $e
            );
        };
    }

    function createProgrammationMagique(int $id_utilisateur, array $quetes_programmees)
    {
        $id_quetes_programmees = [];

        foreach ($quetes_programmees as $p) {
            $id_quetes_programmees[] = $p['id_quete'];
        };


        $db = config::getConnexion();



        try {
            $quetes = new QueteC();
            $liste = $quetes->listquetes();


            if (count($liste) !== 0) {

                foreach ($liste as $index => $quete) {
                    if (in_array($quete['id_quete'], $id_quetes_programmees)) {
                        unset($liste[$index]);
                    };
                };
                $liste = array_values($liste);
                if (count($liste) !== 0) {
                    $index = random_int(0, count($liste) - 1);
                    $prog_cree = new ProgrammationQuete($liste[$index]['id_quete'], $id_utilisateur);
                    $sql = "INSERT INTO programmation (id_quete, id_utilisateur, date_debut, date_fin, statut)
                            VALUES (:id_quete, :id_utilisateur, :dateDebut, :dateFin, :statut)";

                    try {
                        $query = $db->prepare($sql);
                        $query->execute([
                            'id_quete' => $prog_cree->getIdQuete(),
                            'id_utilisateur' => $id_utilisateur,
                            'dateDebut' => $prog_cree->getDateDebut()->format("Y-m-d"),
                            'dateFin' => $prog_cree->getDateFin()->format("Y-m-d"),
                            'statut' => $prog_cree->getStatut()
                        ]);
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        throw new RuntimeException(
                            "Impossible d'enregistrer la programmation.",
                            0,
                            $e
                        );
                    };
                };
            };
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible de sélectionner les quêtes.",
                0,
                $e
            );
        };
    }

    function checkQueteProgUnicite(int $id_quete, int $id_utilisateur, string $date_debut)
    {
        $sql = "SELECT * 
                FROM programmation AS p
                WHERE p.id_utilisateur = :id_utilisateur
                AND p.id_quete = :id_quete
                AND p.date_debut = :date_debut
                  ";

        $db = config::getConnexion();

        try {
            $liste = $db->prepare($sql);
            $liste->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':id_quete' => $id_quete,
                ':date_debut' => $date_debut
            ]);
            return count($liste->fetchAll()) === 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible d'accèder les programmations.",
                0,
                $e
            );
        };
    }

    function createUneProgrammationQuete(array $donnees, array $quetes_programmees, int $id_utilisateur)
    {
        $id_quetes_programmees = [];
        foreach ($quetes_programmees as $p) {
            $id_quetes_programmees[] = $p['id_quete'];
        };

        $db = config::getConnexion();


        $sql = "SELECT q.id_quete FROM quete AS q
                    WHERE q.id_competence = :id_comp AND q.difficulte = :difficulte";


        try {

            $candidats = $db->prepare($sql);

            $candidats->execute([
                ':id_comp' => $donnees["competence"],
                'difficulte' => $donnees["difficulte"]
            ]);
            $liste = $candidats->fetchAll();

            if (count($liste) !== 0) {

                foreach ($liste as $index => $l) {

                    if (in_array($l['id_quete'], $id_quetes_programmees)) {
                        unset($liste[$index]);
                    };
                };
                $liste = array_values($liste);

                if (count($liste) !== 0) {
                    $index = random_int(0, count($liste) - 1);
                    $prog_cree = new ProgrammationQuete($liste[$index]['id_quete'], $id_utilisateur);
                    $sql = "INSERT INTO programmation (id_quete, id_utilisateur, date_debut, date_fin, statut)
                                        VALUES (:id_quete, :id_utilisateur, :dateDebut, :dateFin, :statut)";

                    try {
                        $query = $db->prepare($sql);
                        $query->execute([
                            'id_quete' => $prog_cree->getIdQuete(),
                            'id_utilisateur' => $id_utilisateur,
                            'dateDebut' => $prog_cree->getDateDebut()->format("Y-m-d"),
                            'dateFin' => $prog_cree->getDateFin()->format("Y-m-d"),
                            'statut' => $prog_cree->getStatut()
                        ]);

                        return $query->fetchAll();
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        throw new RuntimeException(
                            "Impossible d'enregistrer la programmation.",
                            0,
                            $e
                        );
                    };
                };
            };
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible de sélectionner les quêtes.",
                0,
                $e
            );
        };


    }

    function createProgrammationQuete(array $donnees, int $id_utilisateur)
    {
        $programmation = [];
        $id_quetes_programmees = [];


        $db = config::getConnexion();

        for ($i = 1; $i < (count($donnees) / 2) + 1; $i++) {
            $sql = "SELECT q.id_quete FROM quete AS q
                    WHERE q.id_competence = :id_comp AND q.difficulte = :difficulte";


            try {

                $candidats = $db->prepare($sql);

                $candidats->execute([
                    ':id_comp' => $donnees["competence-$i"],
                    'difficulte' => $donnees["difficulte-$i"]
                ]);
                $liste = $candidats->fetchAll();

                if (count($liste) !== 0) {

                    foreach ($liste as $index => $l) {

                        if (in_array($l['id_quete'], $id_quetes_programmees)) {
                            unset($liste[$index]);
                        };
                    };
                    $liste = array_values($liste);
                    $essai = 0;
                    $prog_cree_check = false;
                    if (count($liste) !== 0) {
                        while (!$prog_cree_check && $essai < 3 && count($liste) > 0) {
                            $index = random_int(0, count($liste) - 1);
                            $prog_cree = new ProgrammationQuete($liste[$index]['id_quete'], $id_utilisateur);
                            if ($this->checkQueteProgUnicite($liste[$index]['id_quete'], $id_utilisateur, (new DateTimeImmutable("today"))->format("Y-m-d"))) {
                                $sql = "INSERT INTO programmation (id_quete, id_utilisateur, date_debut, date_fin, statut)
                                        VALUES (:id_quete, :id_utilisateur, :dateDebut, :dateFin, :statut)";

                                try {
                                    $query = $db->prepare($sql);
                                    $query->execute([
                                        'id_quete' => $prog_cree->getIdQuete(),
                                        'id_utilisateur' => $id_utilisateur,
                                        'dateDebut' => $prog_cree->getDateDebut()->format("Y-m-d"),
                                        'dateFin' => $prog_cree->getDateFin()->format("Y-m-d"),
                                        'statut' => $prog_cree->getStatut()
                                    ]);

                                    $id_quetes_programmees[] = $prog_cree->getIdQuete();
                                    $programmation[] = $prog_cree;
                                    $prog_cree_check = true;
                                } catch (Exception $e) {
                                    error_log($e->getMessage());
                                    throw new RuntimeException(
                                        "Impossible d'enregistrer la programmation.",
                                        0,
                                        $e
                                    );
                                };
                            } else {
                                $essai++;
                                unset($liste[$index]);
                                $liste = array_values($liste);
                            }
                        };
                    }
                };
            } catch (Exception $e) {
                error_log($e->getMessage());
                throw new RuntimeException(
                    "Impossible de sélectionner les quêtes.",
                    0,
                    $e
                );
            };
        };

        return $programmation;
    }



    function modifierProgrammation(int $id_programmation, int $id_utilisateur)
    {
        $sql = "UPDATE programmation
                SET date_fin = DATE_ADD(date_fin, INTERVAL 1 DAY)
                WHERE id_programmation = :id_programmation
                  AND id_utilisateur = :id_utilisateur";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_programmation' => $id_programmation,
                'id_utilisateur' => $id_utilisateur
            ]);
            echo $query->rowCount() . " records UPDATED";
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible de modifier la programmation.",
                0,
                $e
            );
        }
    }


    function deleteProgrammationQuete(int $id)
    {

        $sql = "DELETE FROM programmation WHERE id_programmation = :id";
        $db = config::getConnexion();
        try {

            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            echo $query->rowCount() . " record(s) DELETED";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new RuntimeException(
                "Impossible de supprimer la programmation.",
                0,
                $e
            );
        }
    }
}
