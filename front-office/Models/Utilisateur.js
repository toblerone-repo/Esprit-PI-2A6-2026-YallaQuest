/**
 * Modèle Utilisateur - YallaQuest Front-Office (MVC)
 */
class Utilisateur {
    constructor(pseudo, email, motDePasse, prenom = '', nom = '', totem = null, expU = 0, niveau = 1, role = 'joueur', statut = 'actif') {
        this.id = Date.now();
        this.pseudo = pseudo;
        this.email = email;
        this.motDePasse = motDePasse;
        this.prenom = prenom;
        this.nom = nom;
        this.totem = totem; // 0 totem au départ
        this.expU = expU;   // 0 XP au départ
        this.niveau = niveau;
        this.role = role;
        this.statut = statut;
        this.dateInscription = new Date().toISOString();
    }
}
