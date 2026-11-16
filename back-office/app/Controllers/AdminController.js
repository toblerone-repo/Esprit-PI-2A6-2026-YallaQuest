/**
 * Contrôleur Administration - Back-Office YallaQuest (Architecture MVC)
 */
const AdminController = {
    // Actions Utilisateurs
    getUsers() {
        return AdminModel.getUsers();
    },

    addUser(pseudo, email, prenom = '', nom = '', role = 'joueur', statut = 'actif') {
        const users = AdminModel.getUsers();
        const newUser = {
            id: Date.now(),
            pseudo,
            email,
            prenom,
            nom,
            niveau: 1,
            expU: 0,
            role,
            statut,
            dateInscription: new Date().toISOString().split('T')[0]
        };
        users.unshift(newUser);
        AdminModel.saveUsers(users);
        return newUser;
    },

    toggleUserStatus(id) {
        const users = AdminModel.getUsers();
        const u = users.find(x => x.id == id);
        if (u) {
            u.statut = (u.statut === 'actif') ? 'suspendu' : 'actif';
            AdminModel.saveUsers(users);
            return u;
        }
        return null;
    },

    toggleUserRole(id) {
        const users = AdminModel.getUsers();
        const u = users.find(x => x.id == id);
        if (u) {
            u.role = (u.role === 'admin') ? 'joueur' : 'admin';
            AdminModel.saveUsers(users);
            return u;
        }
        return null;
    },

    deleteUser(id) {
        let users = AdminModel.getUsers();
        users = users.filter(x => x.id != id);
        AdminModel.saveUsers(users);
        return users;
    },

    // Actions Quêtes / Objectifs
    getQuests() {
        return AdminModel.getQuests();
    },

    addQuest(idUser, titre, categorie, note, dateDebut, dateFin, xp, etapesTitres = []) {
        const quests = AdminModel.getQuests();
        const users = AdminModel.getUsers();
        const user = users.find(u => u.id == idUser) || { pseudo: 'Aventurier' };
        const newId = Date.now();

        const etapes = (etapesTitres || []).filter(t => t && t.trim()).map((t, idx) => ({
            id: 'eo_' + newId + '_' + (idx + 1),
            IdEO: 'eo_' + newId + '_' + (idx + 1),
            titre: t.trim(),
            est_validee: false,
            date_validation: null,
            id_objectif: newId,
            IdO: newId
        }));

        const newQuest = {
            id: newId,
            IdO: newId,
            idUtilisateur: idUser,
            pseudo: user.pseudo,
            titre,
            categorie,
            note: note || '',
            dateDebut: dateDebut || new Date().toISOString().slice(0, 16),
            dateFin: dateFin || new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 16),
            statut: 'a_venir',
            xpRecompense: parseInt(xp, 10) || 50,
            etapes: etapes
        };
        quests.unshift(newQuest);
        AdminModel.saveQuests(quests);
        return newQuest;
    },

    toggleQuestStatus(id) {
        const quests = AdminModel.getQuests();
        const q = quests.find(x => x.id == id || x.IdO == id);
        if (q) {
            if (q.statut === 'atteint') {
                q.statut = 'en_cours';
                (q.etapes || []).forEach(e => { e.est_validee = false; e.date_validation = null; });
            } else {
                q.statut = 'atteint';
                (q.etapes || []).forEach(e => { e.est_validee = true; e.date_validation = new Date().toISOString(); });
            }
            AdminModel.saveQuests(quests);
            return q;
        }
        return null;
    },

    deleteQuest(id) {
        let quests = AdminModel.getQuests();
        quests = quests.filter(x => x.id != id && x.IdO != id);
        AdminModel.saveQuests(quests);
        return quests;
    }
};
