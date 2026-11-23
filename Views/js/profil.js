

document.getElementById("formulaire-modification").addEventListener("submit", async function (event) {
    event.preventDefault();
    let modificationValidee = true;

    const ancienMpInput = document.querySelector("#formulaire-modification #ancien-mot-de-passe");

    const prenomInput = document.querySelector("#formulaire-modification #prenom");
    if (prenomInput.validity.valueMissing || !YQRegex.tester("nom", prenomInput.value)) {
        prenomInput.nextElementSibling.textContent = "Le nom est obligatoire.";
        prenomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const nomInput = document.querySelector("#formulaire-modification #nom");
    if (nomInput.validity.valueMissing || !YQRegex.tester("nom", nomInput.value)) {
        nomInput.nextElementSibling.textContent = "Le nom est obligatoire.";
        nomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/;
    const emailInput = document.querySelector("#formulaire-modification #email");
    if (emailInput.validity.valueMissing || emailPattern.test(emailInput.value.trim()) === false) {
        emailInput.nextElementSibling.textContent = "L'adresse e-mail n'est pas valide. ";
        emailInput.classList.add("warning-border");
        modificationValidee = false;
    };


    const mpInput = document.querySelector("#formulaire-modification #nouveau-mot-de-passe");
    const mpConfirmInput = document.querySelector("#formulaire-modification #confirmer-mot-de-passe");
    const changerMotDePasse = [ancienMpInput, mpInput, mpConfirmInput].some(input => input.value !== "");

    if (changerMotDePasse) {
        if (ancienMpInput.value === "") {
            ancienMpInput.nextElementSibling.textContent = "L'ancien mot de passe est obligatoire.";
            ancienMpInput.classList.add("warning-border");
            modificationValidee = false;
        };

        if (mpInput.validity.valueMissing || !YQRegex.tester("motDePasse", mpInput.value)) {
            mpInput.nextElementSibling.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
            mpInput.classList.add("warning-border");
            modificationValidee = false;
        };

        if (mpConfirmInput.value === "" || mpConfirmInput.value !== mpInput.value) {
            mpConfirmInput.nextElementSibling.textContent = "Les mots de passe ne correspondent pas.";
            mpConfirmInput.classList.add("warning-border");
            modificationValidee = false;
        };
    };

    if (modificationValidee) {
        const form = event.currentTarget;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = "Enregistrement...";
        let redirectionEnCours = false;

        try {
            let donnees = new FormData(form);
            let response = await fetch("modifier-profil.php", {
                method: "POST",
                body: donnees
            });

            let resultat = await response.json();

            if (response.ok && resultat === true) {
                window.location.assign("page-profil.php");
                redirectionEnCours = true;
                return;
            }

            if (typeof resultat === 'string') {
                let erreurServeur = form.querySelector('[data-erreur-serveur]');
                if (!erreurServeur) {
                    erreurServeur = document.createElement('p');
                    erreurServeur.dataset.erreurServeur = 'true';
                    erreurServeur.setAttribute('role', 'alert');
                    form.prepend(erreurServeur);
                }
                erreurServeur.textContent = resultat;
            }
            if (resultat === false) {
                ancienMpInput.nextElementSibling.textContent = "L'ancien mot de passe est incorrect.";
                ancienMpInput.classList.add("warning-border");
                modificationValidee = false;
            };
        } catch (err) {
            console.log(err);
        } finally {
            if (!redirectionEnCours) {
                submitBtn.disabled = false;
                submitBtn.textContent = "Enregistrer";
            }
        };


    }



});


const inputs = document.getElementsByTagName("input");

for (let i = 0; i < inputs.length; i++) {
    inputs[i].addEventListener("click", () => {
        if (inputs[i].classList.contains("warning-border")) {
            inputs[i].nextElementSibling.textContent = "";
            inputs[i].classList.remove("warning-border");
        }
    })
};
