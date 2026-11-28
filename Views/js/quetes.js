// Équipe : template quêtes. Houssem : échappement HTML des options dynamiques.
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    var str = text.toString();
    var out = '';
    for (var i = 0; i < str.length; i++) {
        var c = str.charAt(i);
        out += map[c] !== undefined ? map[c] : c;
    }
    return out;
}

let nbInputChamps = 1;



$(".quete-carte").each(function (index) {

    const direction = index % 2 === 0 ? -1 : 1;
    const angle = direction * (Math.random() * 1.5 + 1);
    const x = Math.random() * 6 - 3;
    const y = Math.random() * 4 - 2;
    const layer = Math.floor(Math.random() * 5) + 1;

    this.style.setProperty("--angle", `${angle}deg`);
    this.style.setProperty("--shift-x", `${x}px`);
    this.style.setProperty("--shift-y", `${y}px`);
    this.style.setProperty("--layer", layer);

    console.log("Card angle:", angle);

    $(this).css("opacity", 0);
    $(this).parent().css({
        position: "relative",
        left: "-8px"
    });

    const delai = index * 150;

    $(this).delay(delai).fadeTo(500, 1);
    $(this).delay(delai).animate({ left: "0px" }, 500);

})




document.getElementById("ajouter-quete")?.addEventListener("click", function () {
    if (nbInputChamps === 5) {
        this.disabled = true;
        return;
    };

    nbInputChamps++;
    const queteSection = document.getElementById("quete-section");
    const queteInputChamp = document.createElement("fieldset");
    queteInputChamp.classList.add("quete-section");
    const numQuete = nbInputChamps < 10 ? "0" + nbInputChamps : String(nbInputChamps);
    queteInputChamp.innerHTML += `<legend class='quete-numero'>Quête ${numQuete}</legend><hr>`

    const queteCompetenceOptions = document.createElement("fieldset");
    queteCompetenceOptions.classList.add("form-group");
    let html = "";
    competences.forEach((competence, index) => {
        const checked = index === 0 ? 'checked' : '';
        html += `<input type='radio' class='competence-radio' name='competence-${nbInputChamps}' id='competence-${nbInputChamps}-${escapeHtml(competence.id_competence)}' value='${escapeHtml(competence.id_competence)}' ${checked}>`
            +
            `<label class='competence-pill' for='competence-${nbInputChamps}-${escapeHtml(competence.id_competence)}'><i class='fa-solid fa-check'></i> ${escapeHtml(competence.nom)}</label>`;
    });

    queteCompetenceOptions.innerHTML =
        `<legend class="quest-radio-label">Compétence</legend>
        <div class="quete-competence-options"> ${html}</div>
        `;

    html = "";

    const queteDifficulteOptions = document.createElement("fieldset");
    queteDifficulteOptions.classList.add("form-group");
    difficultes.forEach((difficulte, index) => {
        const checked = index === 0 ? 'checked' : '';
        html += `<input type='radio' class='difficulte-radio' name='difficulte-${nbInputChamps}' id='difficulte-${nbInputChamps}-${escapeHtml(difficulte)}' value='${escapeHtml(difficulte)}' ${checked}>`
            +
            `<label class='difficulte-pill' for='difficulte-${nbInputChamps}-${escapeHtml(difficulte)}'><i class='fa-solid fa-check'></i> ${escapeHtml(difficulte)}</label>`;
    });

    queteDifficulteOptions.innerHTML =
        `<legend class="quest-radio-label">Difficulté</legend>
        <div class="quete-difficulte-options"> ${html}</div>
        `;

    queteInputChamp.append(queteCompetenceOptions, queteDifficulteOptions);
    queteSection.append(queteInputChamp);

});




$("#ajout-quete-btn-bo").click(function () {
    $("#form-ajout-quete").slideToggle("slow");
});


let $carteSelectionnee = null;

$(".bouton-modifier").click(function () {
    const $carteCliquee = $(this).closest(".quete-carte");
    if ($carteCliquee.length) {
        $carteSelectionnee = $carteCliquee;
    }

    $("#modifier-queteprog-form").slideToggle("slow");
});


$("#charger-1quete-btn").click(function () {

    $("#ajout-1quete-form").slideToggle("slow");
});

$(".quete-carte-statut").click(function () {
    const $carte = $(this).closest(".quete-carte");
    $(this).prop("disabled", true)
        .attr("aria-label", "Quête sélectionnée")
        .attr("title", "Quête sélectionnée")
        .find("i")
        .removeClass("fa-regular fa-circle")
        .addClass("fa-solid fa-circle-check");
    $carte.find(".bouton-supprimer, .bouton-modifier").hide();
});

$(".bouton-supprimer").click(function () {

    $(this).closest(".quete-carte-div").remove();
});


$("#extension-quete-form").on("submit", function (event) {
    event.preventDefault();
    if ($carteSelectionnee) {
        $carteSelectionnee.find(".quete-carte-echeance").remove();
    }
    $("#modifier-queteprog-form").slideUp("slow");
    $(".btn.bouton-modifier").hide();
});

$(".annuler-extention").click(function () {
    $("#modifier-queteprog-form").slideUp("slow");
});

//
$("#demo-toggle-programmation").click(function () {
    $("#liste-quetes-cartes").toggleClass("sans-programmation");
    $("#ajout-1quete-form, #modifier-queteprog-form").hide();
});

$("#ouvrir-formulaire-vide").click(function () {
    $("#quete-prog-formulaire").fadeIn("slow");
});


document.getElementById("quest-ajout")?.addEventListener("submit", function (event) {
    event.preventDefault();
    $("#add_quete_btn").prop("disabled", true).text("Enregistrement...");
    let submitValidee = true;

    const titreChamp = $("#quest-ajout input[name='titre']");
    const titre = titreChamp.val();
    const xpChamp = $("#quest-ajout input[name='xp']");
    const xp = xpChamp.val();

    console.log(titreChamp);
    if (titre.trim().length === 0) {
        $(titreChamp).next().text("Le champ de titre est obligatoire.");
        submitValidee = false;
    } else if (titre.length > 60) {
        $(titreChamp).next().text("Le titre doit être au maximum 60 caractères.");
        submitValidee = false;
    };
    if (xp.trim() === "") {
        $(xpChamp).next().text("Le champ d'xp est obligatoire.");
        submitValidee = false;
    } else if (Number.isInteger(Number(xp))) {
        $(xpChamp).next().text("L'xp doit être un nombre entier.");
        submitValidee = false;
    } else if (xp <= 0) {
        $(xpChamp).next().text("La valeur d'xp doit être strictement supérieure à 0.");
        submitValidee = false;
    };


    if (submitValidee) {
        this.submit();
    } else {
        $("#add_quete_btn").prop("disabled", false).text("Enregistrer");
    };


});

$(".action-btn.mod-btn").click(function () {

    const id = $(this).data("id");
    $.getJSON("quetes-listes.php", { id_quete: id })
        .done(function (quete) {
            const form = $("#quest-modifier");

            form.find("[name='id_quete']").val(quete.id_quete);
            form.find("[name='titre']").val(quete.titre);
            form.find("[name='difficulte']").val([quete.difficulte]);
            form.find("[name='xp']").val(quete.xp);
            form.find("[name='id_competence']").val([quete.id_competence]);
            form.find("[name='id_categorie']").val([quete.id_categorie]);

            $("#form-modifier-quete").slideToggle("slow");
        })
        .fail(function () {
            alert("Impossible de charger la quête.");
        });


});

$(".action-btn.supp-btn").click(function (event) {
    event.preventDefault();
    const bouton = $(this);
    const id = bouton.data("id");
    $.post("quetes-listes.php",
        {
            id_quete: id,
            supprimer: true,
            csrf_token: $("meta[name='csrf-token']").attr("content")

        })
        .done(function () {
            bouton.closest("tr").remove();
        })
        .fail(function () {
            alert("Impossible de supprimer la quête.");
        });


});

if (nbQuetesProgrammees >= 7) {
    $("#charger-1quete-btn").prop("disabled", true);
    $(".surprise-btn").prop("disabled", true);
};

$("#programmation-quetes-form").submit(function (event) {
    $("#add_quete_btn").prop("disabled", true).text("Enregistrement...");
    $(this).find('input[name="nb-inputs"]').val(nbInputChamps);
    $("#programmation-quetes-form button[name='submit-btn']").prop("disabled", false).text("Enregistrer");

});

$(".bouton-supprimer").click(function (event) {
    event.preventDefault();
    const bouton = $(this);
    const id = bouton.data("id");
    $.post("quetes-programmation.php",
        {
            id_prog: id,
            supprimer: true,
            csrf_token: $("meta[name='csrf-token']").attr("content")

        })
        .done(function () {
            bouton.closest(".quete-carte-div").remove();

            if ($("#quetes-grid .quete-carte-div").length === 0) {
                $("#liste-quetes-cartes").addClass("sans-programmation");
            }
        })
        .fail(function () {
            alert("Impossible de supprimer la programmation.");
        });
});

$(".bouton-modifier").click(function (event) {
    event.preventDefault();
    const bouton = $(this);
    const id = bouton.data("id");
    $.post("quetes-programmation.php",
        {
            id_prog: id,
            modifier: true,
            csrf_token: $("meta[name='csrf-token']").attr("content")

        })
        .fail(function () {
            alert("Impossible de modifier la programmation.");
        });
})




