/**
 * Contrôleur Authentification & Inscription - YallaQuest (MVC)
 */
const AuthController = {
    // Valide les données d'inscription
    validateRegister(pseudo, email, mdp, confMdp) {
        const errors = [];
        if (!pseudo || pseudo.trim().length < 3) {
            errors.push("Le nom d'aventurier doit comporter au moins 3 caractères.");
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email.trim())) {
            errors.push("Veuillez renseigner une adresse email valide.");
        }
        if (!mdp || mdp.length < 6) {
            errors.push("Le mot de passe doit comporter au moins 6 caractères.");
        }
        if (mdp !== confMdp) {
            errors.push("Les deux mots de passe ne correspondent pas.");
        }
        return errors;
    },

    // Enregistrement d'un nouvel aventurier (0 totem, 0 XP, Niv 1)
    register(pseudo, email, mdp, prenom = '', nom = '') {
        const user = new Utilisateur(pseudo, email, mdp, prenom, nom, null, 0, 1);
        localStorage.setItem('yq_current_user', JSON.stringify(user));
        return user;
    },

    // Connexion
    login(identifiant, mdp) {
        if (!identifiant || !mdp) {
            return { success: false, error: "Veuillez renseigner tous les champs." };
        }
        // Simulation session
        const sessionUser = {
            pseudo: identifiant.includes('@') ? identifiant.split('@')[0] : identifiant,
            email: identifiant.includes('@') ? identifiant : identifiant + '@yallaquest.tn',
            niveau: 1,
            expU: 50,
            totem: null
        };
        localStorage.setItem('yq_current_user', JSON.stringify(sessionUser));
        return { success: true, user: sessionUser };
    }
};
