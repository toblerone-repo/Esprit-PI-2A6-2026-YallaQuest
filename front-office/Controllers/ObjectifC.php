<?php
/**
 * Contrôleur ObjectifC - YallaQuest (Architecture MVC)
 * Conforme au cours ESPRIT (Chapitre 6 : PHP PDO - CRUD)
 */
require_once __DIR__ . '/../../back-office/src/config.php';
require_once __DIR__ . '/../Models/Objectif.php';
require_once __DIR__ . '/../Models/EtapeObjectif.php';

class ObjectifC
{
    /**
     * Récupère la liste de tous les objectifs
     */
    public function afficherObjectifs()
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return []; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $this->initialiserDonneesSiVide();
            $liste = $db->query('SELECT * FROM objectif ORDER BY id DESC');
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un objectif par son identifiant
     */
    public function recupererObjectif($id)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return false; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('SELECT * FROM objectif WHERE id = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Ajoute un nouvel objectif dans la base de données
     */
    public function ajouterObjectif(Objectif $objectif)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return 0; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('
                INSERT INTO objectif (titre, description, note, categorie, date_debut, date_fin, date_limite, statut, xp_recompense, date_creation)
                VALUES (:titre, :description, :note, :categorie, :date_debut, :date_fin, :date_limite, :statut, :xp, NOW())
            ');

            $dateDebut = $objectif->getDateDebut();
            $dateFin = $objectif->getDateFin();
            $dateLimite = !empty($dateFin) ? substr($dateFin, 0, 10) : date('Y-m-d', strtotime('+7 days'));

            $query->execute([
                'titre' => $objectif->getTitre(),
                'description' => $objectif->getNote(),
                'note' => $objectif->getNote(),
                'categorie' => $objectif->getCategorie() ?? 'Activité physique',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'date_limite' => $dateLimite,
                'statut' => $objectif->getStatut() ?? 'en_cours',
                'xp' => $objectif->getXpRecompense() ?? 50
            ]);

            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un objectif et ses étapes
     */
    public function supprimerObjectif($id)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            // Supprimer d'abord les étapes
            $qEtapes = $db->prepare('DELETE FROM etape_objectif WHERE id_objectif = :id');
            $qEtapes->execute(['id' => $id]);

            // Supprimer l'objectif
            $query = $db->prepare('DELETE FROM objectif WHERE id = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Modifie un objectif existant
     */
    public function modifierObjectif(Objectif $objectif, $id)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('
                UPDATE objectif
                SET titre = :titre, note = :note, description = :description, categorie = :categorie,
                    date_debut = :date_debut, date_fin = :date_fin, statut = :statut
                WHERE id = :id
            ');
            $query->execute([
                'id' => $id,
                'titre' => $objectif->getTitre(),
                'note' => $objectif->getNote(),
                'description' => $objectif->getNote(),
                'categorie' => $objectif->getCategorie(),
                'date_debut' => $objectif->getDateDebut(),
                'date_fin' => $objectif->getDateFin(),
                'statut' => $objectif->getStatut()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Bascule le statut d'un objectif entre 'en_cours' et 'atteint'
     */
    public function basculerStatut($id)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $obj = $this->recupererObjectif($id);
            if ($obj) {
                $nouveau = ($obj['statut'] === 'atteint') ? 'en_cours' : 'atteint';
                $query = $db->prepare('UPDATE objectif SET statut = :s WHERE id = :id');
                $query->execute(['s' => $nouveau, 'id' => $id]);
            }
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les étapes d'un objectif donné
     */
    public function recupererEtapes($idObjectif)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return []; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('SELECT * FROM etape_objectif WHERE id_objectif = :id ORDER BY IdEO ASC');
            $query->execute(['id' => $idObjectif]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Ajoute une étape pour un objectif
     */
    public function ajouterEtape(EtapeObjectif $etape)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return 0; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('
                INSERT INTO etape_objectif (titre, est_validee, date_validation, id_objectif)
                VALUES (:titre, :val, :dval, :idobj)
            ');
            $query->execute([
                'titre' => $etape->getTitre(),
                'val' => $etape->getEstValidee() ? 1 : 0,
                'dval' => $etape->getDateValidation(),
                'idobj' => $etape->getIdObjectif()
            ]);
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprime une étape
     */
    public function supprimerEtape($idEO)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $query = $db->prepare('DELETE FROM etape_objectif WHERE IdEO = :id');
            $query->execute(['id' => $idEO]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Valide ou invalide une étape.
     * Règle métier : quand toutes les étapes d'un objectif sont validées, l'objectif passe automatiquement à 'atteint' !
     */
    public function validerEtape($idEO, $estValidee = true)
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $dateVal = $estValidee ? date('Y-m-d H:i:s') : null;
            $query = $db->prepare('UPDATE etape_objectif SET est_validee = :val, date_validation = :dval WHERE IdEO = :id');
            $query->execute([
                'val' => $estValidee ? 1 : 0,
                'dval' => $dateVal,
                'id' => $idEO
            ]);

            // Trouver l'objectif parent
            $qParent = $db->prepare('SELECT id_objectif FROM etape_objectif WHERE IdEO = :id');
            $qParent->execute(['id' => $idEO]);
            $parent = $qParent->fetch();

            if ($parent && !empty($parent['id_objectif'])) {
                $idObj = $parent['id_objectif'];
                
                // Compter les étapes non validées
                $qCount = $db->prepare('SELECT COUNT(*) as nb_restantes FROM etape_objectif WHERE id_objectif = :idObj AND est_validee = 0');
                $qCount->execute(['idObj' => $idObj]);
                $count = $qCount->fetch();

                if ($count && $count['nb_restantes'] == 0) {
                    // Toutes les étapes sont terminées -> Objectif accompli !
                    $qUp = $db->prepare("UPDATE objectif SET statut = 'atteint' WHERE id = :id");
                    $qUp->execute(['id' => $idObj]);
                } else {
                    // Sinon, si l'objectif était atteint mais qu'on a décoché une étape, il repasse en_cours
                    $qUp = $db->prepare("UPDATE objectif SET statut = 'en_cours' WHERE id = :id AND statut = 'atteint'");
                    $qUp->execute(['id' => $idObj]);
                }
            }
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Initialise 2 objectifs de test avec étapes si la table est vide
     */
    private function initialiserDonneesSiVide()
    {
        $db = Config::getConnexion();
        if ($db === null) {
            return; // base indisponible (voir back-office/src/config.php)
        }
        try {
            $count = $db->query('SELECT COUNT(*) FROM objectif')->fetchColumn();
            if ($count == 0) {
                // Créer objectif 1
                $obj1 = new Objectif('Courir 5 km sans interruption', 'Prendre mes chaussures de running et faire le tour du parc', date('Y-m-d 08:00'), date('Y-m-d 20:00', strtotime('+3 days')), 'en_cours', null, 'Activité physique', 60);
                $id1 = $this->ajouterObjectif($obj1);
                $this->ajouterEtape(new EtapeObjectif('Échauffement dynamique 10 min', $id1, true, date('Y-m-d 08:15')));
                $this->ajouterEtape(new EtapeObjectif('Courir 3 km à allure modérée', $id1, false));
                $this->ajouterEtape(new EtapeObjectif('Sprint final 500 m', $id1, false));

                // Créer objectif 2
                $obj2 = new Objectif('Boire 2 litres d\'eau par jour', 'Garder une gourde sur mon bureau pour rester hydraté', date('Y-m-d 09:00'), date('Y-m-d 19:00', strtotime('+5 days')), 'en_cours', null, 'Nutrition & Santé', 40);
                $id2 = $this->ajouterObjectif($obj2);
                $this->ajouterEtape(new EtapeObjectif('Boire 1 grand verre au réveil', $id2, true, date('Y-m-d 07:30')));
                $this->ajouterEtape(new EtapeObjectif('Finir la première gourde à midi', $id2, false));
                $this->ajouterEtape(new EtapeObjectif('Finir la deuxième gourde à 18h', $id2, false));
            }
        } catch (Exception $e) {
            // Ignorer silencieusement si la table n'est pas encore prête
        }
    }
}
?>