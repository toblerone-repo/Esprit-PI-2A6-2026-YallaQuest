<?php
/*
 * GUIDE D'INTEGRATION - SECURITE DES REQUETES SQL ET DES MOTS DE PASSE
 *
 * Ce controleur utilise Models/UserModel.php, dont la classe s'appelle Utilisateur,
 * et la connexion PDO fournie par config::getConnexion(). Reprenez les principes
 * ci-dessous dans les autres controleurs lors de l'integration de vos modules.
 *
 * 1. REQUETES SQL PREPAREES
 *    login() et updateUtilisateur() utilisent prepare(), bindValue() et execute().
 *    Les valeurs variables passent par des parametres (:mail, :id, :nom, etc.) :
 *    ne concatenez pas directement une valeur de formulaire dans la requete SQL.
 *    Les identifiants sont lies avec PDO::PARAM_INT et les textes avec PARAM_STR.
 *    Conservez les parametres lies lorsque vous ajoutez des champs ou des requetes.
 *    La validation des champs reste necessaire avant l'appel de ces methodes.
 *
 * 2. HACHAGE AVANT L'ECRITURE EN BASE
 *    Pour updateUtilisateur(), le modele recoit le NOUVEAU mot de passe en clair.
 *    Le controleur calcule lui-meme :
 *    $passwordHash = password_hash($utilisateur->getMp(), PASSWORD_DEFAULT);
 *    Seul ce resultat est lie a :mp et stocke dans mot_de_passe_hash.
 *    Ne hachez pas le mot de passe avant de le transmettre a cette methode :
 *    cela provoquerait un double hachage et empecherait la connexion.
 *    Pour une future inscription, appliquez egalement password_hash() avant
 *    l'INSERT. Aucun traitement d'inscription n'est ajoute dans ce fichier.
 *
 * 3. VERIFICATION DES MOTS DE PASSE
 *    login($mail, $mp) attend le mot de passe saisi en clair et utilise
 *    password_verify($mp, $hashEnBase). Ne comparez pas deux password_hash() :
 *    leurs resultats peuvent etre differents pour un meme mot de passe.
 *    login() renvoie null si la connexion echoue. Si elle reussit, il renvoie
 *    les informations de l'utilisateur apres avoir retire mot_de_passe_hash.
 *    Ne remettez pas ce hash dans la session ou dans une reponse au navigateur.
 *
 * 4. MODIFICATION DU PROFIL
 *    updateUtilisateur($utilisateur, $id, $ancienMotDePasse) verifie d'abord
 *    l'ancien mot de passe avec password_verify(), puis hache le nouveau.
 *    L'id transmis doit venir de $_SESSION['utilisateur']['id'] pour le profil
 *    personnel, comme dans modifier-profil.php, et non d'un champ du formulaire.
 *    Le nouveau mot de passe doit avoir au moins 8 octets ; les champs obligatoires,
 *    l'e-mail et la confirmation du mot de passe sont verifies dans la page appelante.
 *    La condition :previous_hash dans l'UPDATE evite d'ecraser une modification
 *    du mot de passe intervenue entre sa lecture et son enregistrement.
 *    La methode renvoie true si une ligne a ete modifiee, sinon false.
 *
 * 5. CSRF, XSS ET ERREURS LORS DE L'INTEGRATION
 *    Avant d'appeler une methode qui modifie des donnees, verifiez le jeton CSRF
 *    avec AuthController dans la page ou le point d'entree. Ces methodes SQL
 *    ne verifient pas elles-memes le jeton ni l'utilisateur de la session.
 *    Pour afficher leurs resultats en HTML, utilisez htmlspecialchars() dans
 *    la vue avec ENT_QUOTES | ENT_SUBSTITUTE et UTF-8, pas avant le stockage.
 *    Les exceptions PDO sont journalisees avec error_log() ; le navigateur
 *    recoit un message generique et un statut 500. Conservez cette separation
 *    pour ne pas afficher les details SQL ou les informations de connexion.
 */
include_once __DIR__ . '/../Models/UserModel.php';
include_once __DIR__ . '/../config.php';

class UtilisateurC
{

    // Le modèle fournit le nouveau mot de passe en clair.
    function updateUtilisateur(Utilisateur $utilisateur, int $id, string $ancienMotDePasse): bool
    {
        if ($id < 1 || strlen($utilisateur->getMp()) < 8) {
            return false;
        }

        $db = config::getConnexion();

        try {
            $read = $db->prepare(
                'SELECT mot_de_passe_hash FROM utilisateur WHERE id_utilisateur = :id LIMIT 1'
            );
            $read->bindValue(':id', $id, PDO::PARAM_INT);
            $read->execute();
            $storedHash = $read->fetchColumn();

            if (!is_string($storedHash) || !password_verify($ancienMotDePasse, $storedHash)) {
                return false;
            }

            // Le hachage est calculé ici, avant toute écriture en base.
            $passwordHash = password_hash($utilisateur->getMp(), PASSWORD_DEFAULT);

            $query = $db->prepare(
                'UPDATE utilisateur SET
                    nom = :nom,
                    prenom = :prenom,
                    mail = :mail,
                    mot_de_passe_hash = :mp
                 WHERE id_utilisateur = :id AND mot_de_passe_hash = :previous_hash'
            );
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':nom', $utilisateur->getNom(), PDO::PARAM_STR);
            $query->bindValue(':prenom', $utilisateur->getPrenom(), PDO::PARAM_STR);
            $query->bindValue(':mail', $utilisateur->getEmail(), PDO::PARAM_STR);
            $query->bindValue(':mp', $passwordHash, PDO::PARAM_STR);
            $query->bindValue(':previous_hash', $storedHash, PDO::PARAM_STR);
            $query->execute();

            return $query->rowCount() === 1;
        } catch (PDOException $e) {
            error_log('Requête SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }
    }

    // Renvoie l'utilisateur (sans le hash) si l'e-mail et le mot de passe sont corrects, sinon null.
    function login(string $mail, string $mp): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT id_utilisateur AS id, nom, prenom, mail, mot_de_passe_hash FROM utilisateur WHERE mail = :mail LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->bindValue(':mail', $mail, PDO::PARAM_STR);
            $query->execute();
            $utilisateur = $query->fetch();
        } catch (PDOException $e) {
            error_log('Requête SQL utilisateur : ' . $e->getMessage());
            http_response_code(500);
            die('Une erreur est survenue lors du traitement des données.');
        }

        if (!$utilisateur || !password_verify($mp, $utilisateur['mot_de_passe_hash'])) {
            return null;
        }

        unset($utilisateur['mot_de_passe_hash']);
        return $utilisateur;
    }
};
