<?php

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
