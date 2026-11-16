/**
 * Modèle de données Admin - Back-Office YallaQuest (Architecture MVC)
 * Conforme au diagramme UML (objectif 0..n EtapeObjectif).
 */
const AdminModel = {
    defaultUsers: [
        { id: 1, pseudo: "AventurierYalla", prenom: "Yassine", nom: "Ben Salem", email: "yassine@yallaquest.tn", niveau: 2, expU: 180, role: "joueur", statut: "actif", dateInscription: "2026-02-10" },
        { id: 2, pseudo: "InesQuest", prenom: "Ines", nom: "Trabelsi", email: "ines@yallaquest.tn", niveau: 3, expU: 320, role: "joueur", statut: "actif", dateInscription: "2026-03-15" },
        { id: 3, pseudo: "AdminYalla", prenom: "Super", nom: "Admin", email: "admin@yallaquest.tn", niveau: 5, expU: 500, role: "admin", statut: "actif", dateInscription: "2026-01-01" },
        { id: 4, pseudo: "KarimRun", prenom: "Karim", nom: "Bouazizi", email: "karim@yallaquest.tn", niveau: 1, expU: 30, role: "joueur", statut: "suspendu", dateInscription: "2026-04-05" }
    ],

    defaultQuests: [
        { 
            id: 1, 
            IdO: 1,
            idUtilisateur: 1, 
            pseudo: "AventurierYalla", 
            titre: "Préparer une salade méchouia", 
            categorie: "Nutrition & Santé", 
            note: "Poivrons et tomates grillés, sans friture. Une photo de l'assiette suffit.", 
            dateDebut: "2026-11-18T10:00", 
            dateFin: "2026-11-25T20:00", 
            statut: "a_venir", 
            xpRecompense: 50,
            etapes: [
                { id: "eo_1_1", IdEO: "eo_1_1", titre: "Griller poivrons, tomates et piments au four", est_validee: false, date_validation: null },
                { id: "eo_1_2", IdEO: "eo_1_2", titre: "Peler et hacher menu avec de l'ail et du carvi", est_validee: false, date_validation: null },
                { id: "eo_1_3", IdEO: "eo_1_3", titre: "Dresser avec huile d'olive, thon et œufs", est_validee: false, date_validation: null }
            ]
        },
        { 
            id: 2, 
            IdO: 2,
            idUtilisateur: 1, 
            pseudo: "AventurierYalla", 
            titre: "Courir 5 km avant midi", 
            categorie: "Activité physique", 
            note: "Juste votre souffle et vos pas. Remarquez trois choses sur votre trajet.", 
            dateDebut: "2026-11-15T08:00", 
            dateFin: "2026-11-22T12:00", 
            statut: "en_cours", 
            xpRecompense: 60,
            etapes: [
                { id: "eo_2_1", IdEO: "eo_2_1", titre: "Échauffement articulaire 5 min", est_validee: true, date_validation: "2026-11-15T08:05:00" },
                { id: "eo_2_2", IdEO: "eo_2_2", titre: "Courir 2.5 km à allure constante", est_validee: true, date_validation: "2026-11-15T08:22:00" },
                { id: "eo_2_3", IdEO: "eo_2_3", titre: "Terminer les 5 km et s'étirer", est_validee: false, date_validation: null }
            ]
        },
        { 
            id: 3, 
            IdO: 3,
            idUtilisateur: 2, 
            pseudo: "InesQuest", 
            titre: "10 min de respiration chaque soir", 
            categorie: "Santé mentale", 
            note: "Respiration ventrale et étirements doux pour favoriser l'endormissement.", 
            dateDebut: "2026-11-10T21:00", 
            dateFin: "2026-11-17T22:00", 
            statut: "atteint", 
            xpRecompense: 50,
            etapes: [
                { id: "eo_3_1", IdEO: "eo_3_1", titre: "Couper les écrans à 21h30", est_validee: true, date_validation: "2026-11-10T21:30:00" },
                { id: "eo_3_2", IdEO: "eo_3_2", titre: "10 min de cohérence cardiaque", est_validee: true, date_validation: "2026-11-10T21:45:00" },
                { id: "eo_3_3", IdEO: "eo_3_3", titre: "Noter 3 gratitudes", est_validee: true, date_validation: "2026-11-10T21:55:00" }
            ]
        },
        { 
            id: 4, 
            IdO: 4,
            idUtilisateur: 4, 
            pseudo: "KarimRun", 
            titre: "30 jours de gainage", 
            categorie: "Activité physique", 
            note: "Défi quotidien de 3 minutes de planche pour renforcer le gainage abdominal.", 
            dateDebut: "2026-12-01T07:00", 
            dateFin: "2026-12-30T21:00", 
            statut: "a_venir", 
            xpRecompense: 100,
            etapes: [
                { id: "eo_4_1", IdEO: "eo_4_1", titre: "Semaine 1 : 3 x 30s planche", est_validee: false, date_validation: null },
                { id: "eo_4_2", IdEO: "eo_4_2", titre: "Semaine 2 : 3 x 45s latéral", est_validee: false, date_validation: null },
                { id: "eo_4_3", IdEO: "eo_4_3", titre: "Semaine 3 : 3 x 60s levée", est_validee: false, date_validation: null },
                { id: "eo_4_4", IdEO: "eo_4_4", titre: "Semaine 4 : 2 min continues", est_validee: false, date_validation: null }
            ]
        }
    ],

    getUsers() {
        const stored = localStorage.getItem('yq_admin_users');
        if (stored) {
            try { return JSON.parse(stored); } catch(e) {}
        }
        return this.defaultUsers;
    },

    saveUsers(users) {
        localStorage.setItem('yq_admin_users', JSON.stringify(users));
    },

    getQuests() {
        const stored = localStorage.getItem('yq_admin_quests_v3');
        if (stored) {
            try { return JSON.parse(stored); } catch(e) {}
        }
        return this.defaultQuests;
    },

    saveQuests(quests) {
        localStorage.setItem('yq_admin_quests_v3', JSON.stringify(quests));
    }
};
