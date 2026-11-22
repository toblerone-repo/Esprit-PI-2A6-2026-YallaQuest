(function () {
function afficherErreur(champ, message) {
  let erreur = document.getElementById(champ.id + '-erreur');
  if (!erreur) {
    erreur = document.createElement('p');
    erreur.id = champ.id + '-erreur';
    erreur.className = 'community-field-error';
    erreur.setAttribute('aria-live', 'polite');
    champ.insertAdjacentElement('afterend', erreur);
    const description = champ.getAttribute('aria-describedby');
    champ.setAttribute('aria-describedby', [description, erreur.id].filter(Boolean).join(' '));
  }
  erreur.textContent = message;
  erreur.hidden = message === '';
  if (message === '') {
    champ.removeAttribute('aria-invalid');
  } else {
    champ.setAttribute('aria-invalid', 'true');
  }
  return message === '';
}

function longueur(texte) {
  return Array.from(texte).length;
}

function preparerFormulaire(formulaire, champs, verifier) {
  let envoye = false;
  for (const champ of champs) afficherErreur(champ, '');

  formulaire.addEventListener('submit', function (event) {
    envoye = true;
    if (verifier()) return;

    event.preventDefault();
    const premierChamp = champs.find(champ => champ.getAttribute('aria-invalid') === 'true');
    for (let parent = premierChamp.parentElement; parent; parent = parent.parentElement) {
      if (parent.tagName === 'DETAILS') parent.open = true;
    }
    premierChamp.focus();
  });

  for (const champ of champs) {
    champ.addEventListener('input', function () {
      if (envoye) verifier();
    });
    champ.addEventListener('change', function () {
      if (envoye) verifier();
    });
  }
}

const formulaireCommunaute = document.getElementById('community-publication-form');
if (formulaireCommunaute) {
  const titrePublication = document.getElementById('community-title');
  const contenuPublication = document.getElementById('community-content');
  const categoriePublication = document.getElementById('community-category');
  const typePublication = document.getElementById('community-type');

  function verifierCommunaute() {
    let valide = true;
    let erreurTitre = '';
    if (titrePublication.value.trim() === '') {
      erreurTitre = 'Le titre est obligatoire.';
    } else if (longueur(titrePublication.value.trim()) < 3 || longueur(titrePublication.value) > 150) {
      erreurTitre = 'Le titre doit contenir entre 3 et 150 caractères.';
    }
    if (!afficherErreur(titrePublication, erreurTitre)) valide = false;

    let erreurContenu = '';
    if (contenuPublication.value.trim() === '') {
      erreurContenu = 'Le message est obligatoire.';
    } else if (longueur(contenuPublication.value.trim()) < 10 || longueur(contenuPublication.value) > 5000) {
      erreurContenu = 'Le message doit contenir entre 10 et 5000 caractères.';
    }
    if (!afficherErreur(contenuPublication, erreurContenu)) valide = false;

    let erreurCategorie = '';
    if (categoriePublication.value !== 'nutrition' && categoriePublication.value !== 'mouvement' && categoriePublication.value !== 'serenite') {
      erreurCategorie = 'Choisis une catégorie de la liste.';
    }
    if (!afficherErreur(categoriePublication, erreurCategorie)) valide = false;

    let erreurType = '';
    if (typePublication.value !== 'Petite victoire' && typePublication.value !== 'Conseil' && typePublication.value !== 'Discussion') {
      erreurType = 'Choisis un type de publication de la liste.';
    }
    if (!afficherErreur(typePublication, erreurType)) valide = false;
    return valide;
  }

  preparerFormulaire(formulaireCommunaute,
    [titrePublication, contenuPublication, categoriePublication, typePublication], verifierCommunaute);
}

const formulairePublicationAdmin = document.getElementById('community-admin-publication-form');
if (formulairePublicationAdmin) {
  const titre = document.getElementById('community-admin-title');
  const auteur = document.getElementById('community-admin-author');
  const categorie = document.getElementById('community-admin-category');
  const type = document.getElementById('community-admin-type');
  const contenu = document.getElementById('community-admin-content');
  const complement = document.getElementById('community-admin-more');
  const statut = document.getElementById('community-admin-status');
  const image = document.getElementById('community-admin-image');

  function verifierPublicationAdmin() {
    let valide = true;
    let erreurTitre = '';
    if (titre.value.trim() === '') {
      erreurTitre = 'Le titre est obligatoire.';
    } else if (longueur(titre.value.trim()) < 3 || longueur(titre.value) > 150) {
      erreurTitre = 'Le titre doit contenir entre 3 et 150 caractères.';
    }
    if (!afficherErreur(titre, erreurTitre)) valide = false;

    let erreurAuteur = '';
    if (auteur.value.trim() === '') {
      erreurAuteur = 'Le nom de l’auteur est obligatoire.';
    } else if (longueur(auteur.value.trim()) < 2 || longueur(auteur.value) > 100) {
      erreurAuteur = 'Le nom de l’auteur doit contenir entre 2 et 100 caractères.';
    } else if (!YQRegex.tester('auteur', auteur.value)) {
      // format défini dans regex.js (chargé avant ce fichier sur publications.php)
      erreurAuteur = YQRegex.message('auteur');
    }
    if (!afficherErreur(auteur, erreurAuteur)) valide = false;

    let erreurCategorie = '';
    if (categorie.value !== 'nutrition' && categorie.value !== 'mouvement' && categorie.value !== 'serenite') {
      erreurCategorie = 'Choisis une catégorie de la liste.';
    }
    if (!afficherErreur(categorie, erreurCategorie)) valide = false;

    let erreurType = '';
    if (type.value !== 'Petite victoire' && type.value !== 'Conseil' && type.value !== 'Discussion') {
      erreurType = 'Choisis un type de publication de la liste.';
    }
    if (!afficherErreur(type, erreurType)) valide = false;

    let erreurContenu = '';
    if (contenu.value.trim() === '') {
      erreurContenu = 'Le message est obligatoire.';
    } else if (longueur(contenu.value.trim()) < 10 || longueur(contenu.value) > 5000) {
      erreurContenu = 'Le message doit contenir entre 10 et 5000 caractères.';
    }
    if (!afficherErreur(contenu, erreurContenu)) valide = false;

    let erreurComplement = '';
    if (longueur(complement.value) > 5000) {
      erreurComplement = 'Le complément ne doit pas dépasser 5000 caractères.';
    }
    if (!afficherErreur(complement, erreurComplement)) valide = false;

    let erreurStatut = '';
    if (statut.value !== 'published' && statut.value !== 'draft' && statut.value !== 'hidden') {
      erreurStatut = 'Choisis un statut de la liste.';
    }
    if (!afficherErreur(statut, erreurStatut)) valide = false;

    let erreurImage = '';
    if (image.value !== '' && image.value !== 'nutrition' && image.value !== 'endurance' && image.value !== 'mobilite' && image.value !== 'force' && image.value !== 'focus') {
      erreurImage = 'Choisis une illustration de la liste.';
    }
    if (!afficherErreur(image, erreurImage)) valide = false;
    return valide;
  }

  preparerFormulaire(formulairePublicationAdmin,
    [titre, auteur, categorie, type, contenu, complement, statut, image], verifierPublicationAdmin);
}

const formulairesSignalement = document.getElementsByClassName('community-report-form');
for (const element of formulairesSignalement) {
  const identifiant = element.id.replace('community-report-form-', '');
  const formulaireSignalement = document.getElementById('community-report-form-' + identifiant);
  const motif = document.getElementById('community-reason-' + identifiant);
  const precisions = document.getElementById('community-details-' + identifiant);

  function verifierSignalement() {
    let valide = true;
    let erreurMotif = '';
    if (motif.value !== 'spam' && motif.value !== 'inapproprie' && motif.value !== 'dangereux' && motif.value !== 'autre') {
      erreurMotif = 'Choisis un motif de signalement de la liste.';
    }
    if (!afficherErreur(motif, erreurMotif)) valide = false;

    let erreurPrecisions = '';
    if (longueur(precisions.value) > 1000) {
      erreurPrecisions = 'Le texte des précisions ne doit pas dépasser 1000 caractères.';
    }
    if (!afficherErreur(precisions, erreurPrecisions)) valide = false;
    return valide;
  }

  preparerFormulaire(formulaireSignalement, [motif, precisions], verifierSignalement);
}

const formulaireModeration = document.getElementById('community-moderation-form');
if (formulaireModeration) {
  const note = document.getElementById('community-admin-note');

  function verifierModeration() {
    let erreurNote = '';
    if (longueur(note.value) > 1000) {
      erreurNote = 'La note de modération ne doit pas dépasser 1000 caractères.';
    }
    return afficherErreur(note, erreurNote);
  }

  preparerFormulaire(formulaireModeration, [note], verifierModeration);
}

})();
