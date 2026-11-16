/**
 * Vue - formulaires « Rejoins la quête » (inscription) et « Connexion ».
 * La validation reste dans AuthController (inchangé). Plus d'alert() : les erreurs s'affichent dans la page,
 * la réussite dans un toast (front-office/js/ui.js).
 */
document.addEventListener('DOMContentLoaded', function () {
    // Inscription
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const pseudo = document.getElementById('pseudo').value.trim();
            const email = document.getElementById('email').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const nom = document.getElementById('nom').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const confMdp = document.getElementById('confirmer_mot_de_passe').value;

            const errorContainer = document.getElementById('registerErrors');
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = '';

            const errors = AuthController.validateRegister(pseudo, email, mdp, confMdp);
            if (errors.length > 0) {
                errors.forEach((err) => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    errorList.appendChild(li);
                });
                errorContainer.hidden = false;
                errorContainer.focus();
                return;
            }

            errorContainer.hidden = true;
            AuthController.register(pseudo, email, mdp, prenom, nom);
            registerForm.querySelector('[type="submit"]').disabled = true;
            YQ.toast('Bienvenue ' + pseudo + ' ! Ton compte est créé.');
            setTimeout(() => YQ.go('login.php'), 1200);
        });
    }

    // Connexion
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const identifiant = document.getElementById('identifiant').value.trim();
            const mdp = document.getElementById('mot_de_passe').value;
            const loginError = document.getElementById('loginError');

            const res = AuthController.login(identifiant, mdp);
            if (!res.success) {
                loginError.textContent = res.error;
                loginError.hidden = false;
                document.getElementById(identifiant ? 'mot_de_passe' : 'identifiant').focus();
                return;
            }
            loginError.hidden = true;
            YQ.go('index.php?page=objectifs');
        });
    }
});
