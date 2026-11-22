/**
 * Vue - formulaires « Rejoindre la guilde » (inscription) et « Connexion ».
 * La validation reste dans AuthController. Les erreurs s'affichent dans la page,
 * la réussite dans un toast (ui.js).
 */
document.addEventListener('DOMContentLoaded', function () {
    // Inscription (join_now.php)
    const joinForm = document.getElementById('joinForm');
    if (joinForm) {
        joinForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const pseudo = document.getElementById('pseudo').value.trim();
            const email = document.getElementById('email').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const nom = document.getElementById('nom').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const confMdp = document.getElementById('confirmer_mot_de_passe').value;
            const joinError = document.getElementById('joinError');

            const errors = AuthController.validateRegister(pseudo, email, mdp, confMdp);
            if (errors.length > 0) {
                const ul = document.createElement('ul');
                errors.forEach((err) => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    ul.appendChild(li);
                });
                joinError.replaceChildren(ul);
                joinError.hidden = false;
                return;
            }

            joinError.hidden = true;
            AuthController.register(pseudo, email, mdp, prenom, nom);
            joinForm.querySelector('[type="submit"]').disabled = true;
            YQ.toast('Bienvenue ' + pseudo + ' ! Ton compte est créé.');
            setTimeout(() => YQ.go('login.php'), 1200);
        });
    }

    // Connexion (login.php)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        // Contrôle côté navigateur ; si tout est bon, le formulaire part vers login.php
        // qui vérifie l'e-mail et le mot de passe dans la base.
        loginForm.addEventListener('submit', function (e) {
            const email = document.getElementById('email').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const loginError = document.getElementById('loginError');

            let erreur = '';
            let champ = null;
            if (email === '') {
                erreur = 'Saisis ton e-mail.';
                champ = 'email';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                erreur = "L'adresse e-mail n'est pas valide.";
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
