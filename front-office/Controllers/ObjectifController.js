/**
 * Contrôleur Objectifs / Buts - YallaQuest Front-Office (Architecture MVC)
 * Gère les objectifs et leurs étapes associées (EtapeObjectif).
 * Règle métier : quand l'utilisateur valide toutes les étapes, l'objectif se termine automatiquement.
 */
const ObjectifController = {
    // Buts par défaut intégrant des étapes conformément au diagramme UML
    defaultQuests: [
        new Objectif(
            "Préparer une salade méchouia",
            "Nutrition & Santé",
            "Poivrons et tomates grillés, sans friture. Une photo de l'assiette suffit.",
            "2026-11-18T10:00",
            "2026-11-25T23:59",
            50,
            "a_venir",
            1,
            [
                new EtapeObjectif("Griller poivrons, tomates et piments au four", false, null, "eo_1_1", 1),
                new EtapeObjectif("Peler et hacher menu avec de l'ail et du carvi", false, null, "eo_1_2", 1),
                new EtapeObjectif("Dresser avec huile d'olive, thon et quartiers d'œufs durs", false, null, "eo_1_3", 1)
            ]
        ),
        new Objectif(
            "Courir 5 km avant midi. Sans écouteurs.",
            "Activité physique",
            "Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet habituel.",
            "2026-11-15T08:00",
            "2026-11-22T12:00",
            60,
            "en_cours",
            2,
            [
                new EtapeObjectif("S'échauffer 5 minutes et définir son itinéraire", true, "2026-11-15T08:05:00", "eo_2_1", 2),
                new EtapeObjectif("Parcourir les premiers 2.5 km à allure constante", true, "2026-11-15T08:22:00", "eo_2_2", 2),
                new EtapeObjectif("Boucler les 5 km et réaliser 5 minutes d'étirements", false, null, "eo_2_3", 2)
            ]
        ),
        new Objectif(
            "10 minutes de respiration chaque soir",
            "Santé mentale",
            "Respiration ventrale et étirements doux pour relâcher la pression avant de dormir.",
            "2026-11-10T21:00",
            "2026-11-17T22:00",
            50,
            "atteint",
            3,
            [
                new EtapeObjectif("Éteindre les écrans 30 minutes avant de se coucher", true, "2026-11-10T21:30:00", "eo_3_1", 3),
                new EtapeObjectif("Effectuer 10 minutes de cohérence cardiaque (5s inspire / 5s expire)", true, "2026-11-10T21:45:00", "eo_3_2", 3),
                new EtapeObjectif("Noter trois gratitudes de la journée dans son carnet", true, "2026-11-10T21:55:00", "eo_3_3", 3)
            ]
        ),
        new Objectif(
            "30 jours de gainage matinal",
            "Activité physique",
            "3 séries de gainage de 45 secondes chaque matin avant le petit-déjeuner.",
            "2026-12-01T07:00",
            "2026-12-30T21:00",
            100,
            "a_venir",
            4,
            [
                new EtapeObjectif("Semaine 1 : 3 x 30 secondes de planche classique", false, null, "eo_4_1", 4),
                new EtapeObjectif("Semaine 2 : 3 x 45 secondes avec gainage latéral", false, null, "eo_4_2", 4),
                new EtapeObjectif("Semaine 3 : 3 x 60 secondes avec levée alternée", false, null, "eo_4_3", 4),
                new EtapeObjectif("Semaine 4 : Défi final 2 minutes en planche continue", false, null, "eo_4_4", 4)
            ]
        )
    ],

    getQuests() {
        const STORAGE_KEY = 'yq_user_quests_v3';
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            try {
                const raw = JSON.parse(stored);
                if (Array.isArray(raw)) {
                    return raw.map(q => new Objectif(
                        q.titre,
                        q.categorie,
                        q.note || q.description,
                        q.dateDebut || q.date_debut,
                        q.dateFin || q.date_fin,
                        q.xpRecompense,
                        q.statut,
                        q.id || q.IdO,
                        (q.etapes || []).map(e => new EtapeObjectif(e.titre, e.est_validee, e.date_validation, e.id || e.IdEO, q.id || q.IdO))
                    ));
                }
            } catch(e) {}
        }
        return this.defaultQuests;
    },

    saveQuests(quests) {
        const STORAGE_KEY = 'yq_user_quests_v3';
        localStorage.setItem(STORAGE_KEY, JSON.stringify(quests));
    },

    addQuest(titre, categorie, note, dateDebut, dateFin, xp, etapesTitres = []) {
        const list = this.getQuests();
        const now = new Date();
        const start = new Date(dateDebut);

        let initialStatus = 'en_cours';
        if (start > now) {
            initialStatus = 'a_venir';
        }

        const newId = Date.now();
        const etapesObj = (etapesTitres || []).filter(t => t && t.trim()).map((t, idx) => {
            return new EtapeObjectif(t.trim(), false, null, 'eo_' + newId + '_' + (idx + 1), newId);
        });

        const newObj = new Objectif(
            titre, 
            categorie, 
            note, 
            dateDebut, 
            dateFin, 
            xp, 
            initialStatus,
            newId,
            etapesObj
        );

        list.unshift(newObj);
        this.saveQuests(list);
        return newObj;
    },

    /**
     * Valide / invalide une étape individuelle.
     * Si toutes les étapes sont validées, termine automatiquement l'objectif (statut = 'atteint').
     */
    toggleEtape(idObjectif, idEtape) {
        const list = this.getQuests();
        const obj = list.find(q => q.id == idObjectif || q.IdO == idObjectif);
        if (!obj) return null;

        const etape = (obj.etapes || []).find(e => e.id == idEtape || e.IdEO == idEtape);
        if (!etape) return null;

        if (etape.est_validee) {
            etape.invalider();
        } else {
            etape.valider();
        }

        // Vérification de la complétion globale
        const previousStatus = obj.statut;
        obj.verifierProgression();
        const allDone = (obj.statut === 'atteint' && previousStatus !== 'atteint');

        this.saveQuests(list);
        return {
            objectif: obj,
            etape: etape,
            allDone: allDone,
            statusChanged: previousStatus !== obj.statut
        };
    },

    toggleQuest(id) {
        const list = this.getQuests();
        const obj = list.find(q => q.id == id || q.IdO == id);
        if (obj) {
            if (obj.statut === 'atteint') {
                const now = new Date();
                const start = new Date(obj.dateDebut);
                obj.statut = (start > now) ? 'a_venir' : 'en_cours';
                // Décocher les étapes
                (obj.etapes || []).forEach(e => e.invalider());
            } else {
                obj.statut = 'atteint';
                // Valider toutes les étapes
                (obj.etapes || []).forEach(e => e.valider());
            }
            this.saveQuests(list);
            return obj;
        }
        return null;
    },

    deleteQuest(id) {
        let list = this.getQuests();
        list = list.filter(q => q.id != id && q.IdO != id);
        this.saveQuests(list);
        return list;
    }
};
