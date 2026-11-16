/**
 * Modèle EtapeObjectif - YallaQuest Front-Office (Architecture MVC)
 * Conforme au diagramme UML :
 * - IdEO : Identifiant unique de l'étape
 * - titre : Libellé de l'étape
 * - est_validee : Booléen (étape accomplie ou non)
 * - date_validation : Date/heure de validation (ou null)
 * - id_objectif (IdO) : Identifiant de l'objectif parent (relation contenir 1-1)
 */
class EtapeObjectif {
    constructor(titre, est_validee = false, date_validation = null, id = null, id_objectif = null) {
        this.id = id || ('eo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6));
        this.IdEO = this.id;
        this.titre = titre;
        this.est_validee = Boolean(est_validee);
        this.date_validation = date_validation || null;
        this.id_objectif = id_objectif || null;
        this.IdO = this.id_objectif;
    }

    valider() {
        this.est_validee = true;
        this.date_validation = new Date().toISOString();
    }

    invalider() {
        this.est_validee = false;
        this.date_validation = null;
    }
}
