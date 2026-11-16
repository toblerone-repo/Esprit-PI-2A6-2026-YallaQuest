/**
 * Modèle Objectif / But - YallaQuest Front-Office (Architecture MVC)
 * Conforme au diagramme UML :
 * - IdO : Identifiant unique de l'objectif
 * - titre : Titre de l'objectif
 * - note : Note pense-bête pour le futur
 * - date_debut : Date & heure de début
 * - date_fin : Date & heure de fin
 * - statut : 'a_venir', 'en_cours', 'atteint'
 * - etapes : Liste d'instances EtapeObjectif (relation 0..n)
 */
class Objectif {
    constructor(titre, categorie = 'Activité physique', note = '', dateDebut = null, dateFin = null, xpRecompense = 50, statut = 'a_venir', id = null, etapes = []) {
        this.id = id || Date.now();
        this.IdO = this.id;
        this.titre = titre;
        this.categorie = categorie;
        this.note = note;
        this.dateDebut = dateDebut || new Date().toISOString().slice(0, 16);
        this.date_debut = this.dateDebut;
        this.dateFin = dateFin || new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 16);
        this.date_fin = this.dateFin;
        this.xpRecompense = parseInt(xpRecompense, 10) || 50;
        this.statut = statut;
        this.dateCreation = new Date().toISOString();

        // Relation 0..n EtapeObjectif
        this.etapes = Array.isArray(etapes)
            ? etapes.map(e => (e instanceof EtapeObjectif ? e : new EtapeObjectif(e.titre, e.est_validee, e.date_validation, e.id || e.IdEO, this.id)))
            : [];
    }

    ajouterEtape(titre) {
        if (!titre || !titre.trim()) return null;
        const etape = new EtapeObjectif(titre.trim(), false, null, null, this.id);
        this.etapes.push(etape);
        this.verifierProgression();
        return etape;
    }

    supprimerEtape(idEtape) {
        this.etapes = this.etapes.filter(e => e.id != idEtape && e.IdEO != idEtape);
        this.verifierProgression();
    }

    /**
     * Règle métier : quand l'utilisateur termine toutes les étapes, l'objectif se termine (statut = 'atteint')
     */
    verifierProgression() {
        if (!this.etapes || this.etapes.length === 0) return this.statut;
        const toutesValidees = this.etapes.every(e => e.est_validee);
        if (toutesValidees) {
            this.statut = 'atteint';
        } else if (this.statut === 'atteint') {
            const now = new Date();
            const start = new Date(this.dateDebut);
            this.statut = (start > now) ? 'a_venir' : 'en_cours';
        }
        return this.statut;
    }

    getNbEtapesValidees() {
        return (this.etapes || []).filter(e => e.est_validee).length;
    }

    getPourcentageProgression() {
        if (!this.etapes || !this.etapes.length) return this.statut === 'atteint' ? 100 : 0;
        return Math.round((this.getNbEtapesValidees() / this.etapes.length) * 100);
    }
}
