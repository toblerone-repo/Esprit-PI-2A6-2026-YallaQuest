/**
 * Vue - formulaires « Rejoindre la guilde » (inscription) et « Connexion ».
 * Contrôle rapide côté navigateur ; la vraie validation est refaite en PHP
 * (UtilisateurC::register() et login.php). Les erreurs s'affichent dans la page.
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

            const errors = [];
            if (prenom === '' || nom === '' || pseudo === '' || email === '' || mdp === '') {
                errors.push('Tous les champs sont obligatoires.');
            }
            if (!/^[a-zA-Z0-9_\-]{3,50}$/.test(pseudo)) {
                errors.push('Le pseudo doit contenir de 3 à 50 caractères (lettres, chiffres, _ ou -).');
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push("L'adresse e-mail n'est pas valide.");
            }
            if (mdp.length < 8) {
                errors.push('Le mot de passe doit contenir au moins 8 caractères.');
            }
            if (mdp !== confMdp) {
                errors.push('Les mots de passe ne correspondent pas.');
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
                return;
            }

            joinError.hidden = true;
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
