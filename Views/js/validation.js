/**
 * Vue - formulaires « Rejoindre la guilde » (inscription) et « Connexion ».
 * Les formats (e-mail, nom, pseudo, mot de passe) viennent de regex.js (YQRegex), à charger avant ce fichier.
 * Les erreurs s'affichent dans la page, la réussite dans un toast (ui.js).
 */
document.addEventListener('DOMContentLoaded', function () {
    // Inscription (join_now.php)
    // [INSCRIPTION] Si tout est bon, le formulaire part vers join_now.php (PHP),
    // qui crée le compte puis redirige vers login.php avec le message de réussite.
    const joinForm = document.getElementById('joinForm');
    if (joinForm) {
        joinForm.addEventListener('submit', function (e) {
            const pseudo = document.getElementById('pseudo').value.trim();
            const email = document.getElementById('email').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const nom = document.getElementById('nom').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const confMdp = document.getElementById('confirmer_mot_de_passe').value;
            const joinError = document.getElementById('joinError');

            // Chaque champ vide ou mal formé ajoute un message ; le premier champ faux reçoit le focus.
            const errors = [];
            let premierChamp = null;
            function verifier(id, valeur, motif, libelle) {
                let erreur = '';
                if (valeur === '') erreur = libelle + ' est obligatoire.';
                // Le message du motif « nom » ne cite pas le champ (prénom ou nom) : on le précise.
                else if (motif && !YQRegex.tester(motif, valeur)) erreur = (motif === 'nom' ? libelle + ' : ' : '') + YQRegex.message(motif);
                if (erreur) {
                    errors.push(erreur);
                    if (!premierChamp) premierChamp = id;
                }
            }
            verifier('prenom', prenom, 'nom', 'Le prénom');
            verifier('nom', nom, 'nom', 'Le nom');
            verifier('pseudo', pseudo, 'pseudo', 'Le pseudo');
            verifier('email', email, 'email', "L'e-mail");
            verifier('mot_de_passe', mdp, 'motDePasse', 'Le mot de passe');
            if (confMdp === '') verifier('confirmer_mot_de_passe', '', null, 'La confirmation du mot de passe');
            else if (confMdp !== mdp) {
                errors.push('Les mots de passe ne correspondent pas.');
                if (!premierChamp) premierChamp = 'confirmer_mot_de_passe';
            }

            if (errors.length > 0) {
                e.preventDefault();
                const ul = document.createElement('ul');
                errors.forEach((err) => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    ul.appendChild(li);
                });
                joinError.replaceChildren(ul);
                joinError.hidden = false;
                document.getElementById(premierChamp).focus();
                return;
            }

            joinError.hidden = true;
        });
    }

    // Connexion (index.php?action=login, vue login.php)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        // Contrôle côté navigateur ; si tout est bon, le formulaire part vers index.php?action=login
        // (AuthController::login vérifie l'e-mail et le mot de passe dans la base).
        loginForm.addEventListener('submit', function (e) {
            const email = document.getElementById('email').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const loginError = document.getElementById('loginError');

            let erreur = '';
            let champ = null;
            if (email === '') {
                erreur = 'Saisis ton e-mail.';
                champ = 'email';
            } else if (!YQRegex.tester('email', email)) {
                erreur = YQRegex.message('email');
                champ = 'email';
            } else if (mdp === '') {
                erreur = 'Saisis ton mot de passe.';
                champ = 'mot_de_passe';
            }

            if (erreur !== '') {
                e.preventDefault();
                loginError.textContent = erreur;
                loginError.hidden = false;
                document.getElementById(champ).focus();
                return;
            }
            loginError.hidden = true;
        });
    }
});
