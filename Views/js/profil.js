

document.getElementById("formulaire-modification").addEventListener("submit", async function (event) {
    event.preventDefault();
    let modificationValidee = true;

    const ancienMpInput = document.querySelector("#formulaire-modification #ancien-mot-de-passe");

    // Les formats (nom, e-mail, mot de passe) viennent de regex.js (YQRegex), chargé avant ce fichier.
    const prenomInput = document.querySelector("#formulaire-modification #prenom");
    if (prenomInput.value.trim().length === 0) {
        prenomInput.nextElementSibling.textContent = "Le prénom est obligatoire.";
        prenomInput.classList.add("warning-border");
        modificationValidee = false;
    } else if (!YQRegex.tester("nom", prenomInput.value)) {
        prenomInput.nextElementSibling.textContent = YQRegex.message("nom");
        prenomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const nomInput = document.querySelector("#formulaire-modification #nom");
    if (nomInput.value.trim().length === 0) {
        nomInput.nextElementSibling.textContent = "Le nom est obligatoire.";
        nomInput.classList.add("warning-border");
        modificationValidee = false;
    } else if (!YQRegex.tester("nom", nomInput.value)) {
        nomInput.nextElementSibling.textContent = YQRegex.message("nom");
        nomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const emailInput = document.querySelector("#formulaire-modification #email");
    if (!YQRegex.tester("email", emailInput.value)) {
        emailInput.nextElementSibling.textContent = YQRegex.message("email");
        emailInput.classList.add("warning-border");
        modificationValidee = false;
    };


    const mpInput = document.querySelector("#formulaire-modification #nouveau-mot-de-passe");
    if (!YQRegex.tester("motDePasse", mpInput.value)) {
        mpInput.nextElementSibling.textContent = YQRegex.message("motDePasse");
        mpInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const mpConfirmInput = document.querySelector("#formulaire-modification #confirmer-mot-de-passe");
    if (mpConfirmInput.validity.valueMissing || mpInput.validity.valueMissing || mpConfirmInput.value !== mpInput.value) {
        mpConfirmInput.nextElementSibling.textContent = "Les mots de passe ne correspondent pas.";
        mpConfirmInput.classList.add("warning-border");
        modificationValidee = false;
    };

    if (modificationValidee) {
        const form = event.currentTarget;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = "Enregistrement...";

        try {
            let donnees = new FormData(form);
            let response = await fetch("modifier-profil.php", {
                method: "POST",
                body: donnees
            });

            let resultat = await response.json();

            if (response.ok && resultat === true) {
                window.location.assign("page-profil.php");
                return;
            }

            if (resultat === false) {
                ancienMpInput.nextElementSibling.textContent = "L'ancien mot de passe est incorrect.";
                ancienMpInput.classList.add("warning-border");
                modificationValidee = false;
            };
        } catch (err) {
            console.log(err);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Enregistrer";
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
