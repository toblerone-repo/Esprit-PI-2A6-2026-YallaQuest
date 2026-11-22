

document.getElementById("formulaire-modification").addEventListener("submit", async function (event) {
    event.preventDefault();
    let modificationValidee = true;

    const ancienMpInput = document.querySelector("#formulaire-modification #ancien-mot-de-passe");

    const prenomInput = document.querySelector("#formulaire-modification #prenom");
    if (prenomInput.validity.valueMissing || prenomInput.value.trim().length === 0) {
        prenomInput.nextElementSibling.textContent = "Le nom est obligatoire.";
        prenomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const nomInput = document.querySelector("#formulaire-modification #nom");
    if (nomInput.validity.valueMissing || nomInput.value.trim().length === 0) {
        nomInput.nextElementSibling.textContent = "Le nom est obligatoire.";
        nomInput.classList.add("warning-border");
        modificationValidee = false;
    };

    const emailPattern = /^[a-zA-Z0-9._]+@(gmail\.com|outlook\.com|icloud\.com)$/;
    const emailInput = document.querySelector("#formulaire-modification #email");
    if (emailInput.validity.valueMissing || emailPattern.test(emailInput.value.trim()) === false) {
        emailInput.nextElementSibling.textContent = "L'adresse e-mail n'est pas valide. ";
        emailInput.classList.add("warning-border");
        modificationValidee = false;
    };


    const mpInput = document.querySelector("#formulaire-modification #nouveau-mot-de-passe");
    if (mpInput.validity.valueMissing || mpInput.value.trim().length < 8) {
        mpInput.nextElementSibling.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
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
