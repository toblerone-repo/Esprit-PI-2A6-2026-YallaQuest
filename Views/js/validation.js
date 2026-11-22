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
            YQ.toast('Bienvenue ' + res.user.pseudo + ' !');
        });
    }
});
