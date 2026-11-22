/**
 * Yalla Quest · expressions régulières communes à tous les formulaires (front-office et back-office).
 *
 * À charger AVANT les scripts de validation de la page :
 *     <script src="../../js/regex.js"></script>
 *
 * 1. Dans un script de validation :
 *        if (!YQRegex.tester('email', valeur)) { … YQRegex.message('email') … }
 *
 * 2. Sans script : ajouter data-regex="nom-du-motif" sur le champ.
 *        <input type="text" id="nom" name="nom" data-regex="libelle" required>
 *    À l'envoi du formulaire, regex.js vérifie le champ (vide s'il est obligatoire, puis le motif),
 *    affiche le message sous le champ et bloque l'envoi tant qu'un champ est faux.
 *
 * Pour ajouter un motif : une entrée dans « motifs », avec sa regex et son message.
 */
(function () {
  'use strict';

  // Lettres de toutes les langues (accents, ç, œ, arabe…) suivies de leurs accents combinés éventuels.
  const MOT = '\\p{L}[\\p{L}\\p{M}]*';

  // Nom fait de mots séparés par un espace, un tiret ou une apostrophe, de min à max caractères.
  function nomDeMots(min, max) {
    return new RegExp('^(?=.{' + min + ',' + max + '}$)' + MOT + "(?:[ '’-]" + MOT + ')*$', 'u');
  }

  const motifs = {
    // Adresse e-mail : nom@domaine.extension
    email: {
      regex: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/,
      message: "L'adresse e-mail n'est pas valide (ex. nom@exemple.com).",
    },
    // Prénom ou nom d'une personne : 2 à 50 caractères, lettres, espaces, tirets, apostrophes
    nom: {
      regex: nomDeMots(2, 50),
      message: 'Utilise 2 à 50 lettres (espaces, tirets et apostrophes acceptés).',
    },
    // Nom de l'auteur d'une publication : comme un nom, jusqu'à 100 caractères
    auteur: {
      regex: nomDeMots(2, 100),
      message: "Le nom de l'auteur ne peut contenir que des lettres, espaces, tirets et apostrophes.",
    },
    // Pseudo de quête : 3 à 20 lettres, chiffres ou tirets
    pseudo: {
      regex: /^[A-Za-z0-9-]{3,20}$/,
      message: 'Le pseudo doit contenir 3 à 20 lettres, chiffres ou tirets, sans espace ni accent.',
    },
    // Nouveau mot de passe : 8 à 64 caractères, au moins une lettre et un chiffre
    motDePasse: {
      regex: /^(?=.*[A-Za-z])(?=.*\d).{8,64}$/,
      message: 'Le mot de passe doit contenir 8 caractères au moins, dont une lettre et un chiffre.',
    },
    // Libellé (compétence, totem, type d'activité) : 2 à 50 caractères, lettres, espaces, tirets, apostrophes
    libelle: {
      regex: nomDeMots(2, 50),
      message: 'Utilise 2 à 50 lettres (espaces, tirets et apostrophes acceptés), sans chiffre.',
    },
    // Unité de mesure : 1 à 20 caractères, lettres et % ° / (ex. grammes, km, km/h)
    unite: {
      regex: /^(?=.{1,20}$)[\p{L}%°/]+(?: [\p{L}%°/]+)*$/u,
      message: "L'unité ne peut contenir que des lettres et % ° / (ex. grammes, km/h).",
    },
    // XP d'une quête : nombre entier de 1 à 999 999
    xp: {
      regex: /^[1-9]\d{0,5}$/,
      message: "L'XP doit être un nombre entier supérieur à 0 (ex. 25).",
    },
    // Seuil d'XP d'un totem : 0 ou un nombre entier multiple de 10
    seuil: {
      regex: /^(?:0|[1-9]\d{0,5}0)$/,
      message: "Le seuil doit être un nombre entier multiple de 10 (ex. 300).",
    },
    // Pourcentage : nombre entier de 0 à 100
    pourcentage: {
      regex: /^(?:100|[1-9]?\d)$/,
      message: 'La part doit être un nombre entier entre 0 et 100.',
    },
    // Quantité : nombre positif avec au plus une décimale (ex. 100 ou 2.5)
    quantite: {
      regex: /^(?:0|[1-9]\d{0,5})(?:\.\d)?$/,
      message: 'La quantité doit être un nombre positif avec au plus une décimale (ex. 2.5).',
    },
  };

  function motif(nom) {
    const m = motifs[nom];
    if (!m) throw new Error('YQRegex : motif inconnu « ' + nom + ' »');
    return m;
  }

  const YQRegex = window.YQRegex = {
    motifs: motifs,
    // true si la valeur (sans espaces autour) respecte le motif
    tester: function (nom, valeur) {
      return motif(nom).regex.test(String(valeur).trim());
    },
    message: function (nom) {
      return motif(nom).message;
    },
  };

  /* ---------- Champs avec data-regex : vérification automatique à l'envoi ---------- */

  const OBLIGATOIRE = 'Ce champ est obligatoire.';

  // Message sous le champ : réutilise un élément .message-erreur qui suit déjà le champ, sinon en crée un.
  function zoneMessage(champ) {
    const suivant = champ.nextElementSibling;
    if (suivant && (suivant.classList.contains('message-erreur') || suivant.classList.contains('yq-regex-erreur'))) {
      return suivant;
    }
    const zone = document.createElement('p');
    zone.className = 'yq-regex-erreur';
    zone.setAttribute('aria-live', 'polite');
    zone.style.cssText = 'margin:6px 0 0;color:#f2a7a7;font-size:0.8rem;line-height:1.4;';
    if (champ.id) {
      zone.id = champ.id + '-regex-erreur';
      const decrit = champ.getAttribute('aria-describedby');
      champ.setAttribute('aria-describedby', [decrit, zone.id].filter(Boolean).join(' '));
    }
    champ.insertAdjacentElement('afterend', zone);
    return zone;
  }

  function verifierChamp(champ) {
    const valeur = champ.value.trim();
    let erreur = '';
    if (valeur === '') {
      if (champ.required) erreur = OBLIGATOIRE;
    } else if (!YQRegex.tester(champ.dataset.regex, valeur)) {
      erreur = YQRegex.message(champ.dataset.regex);
    }
    zoneMessage(champ).textContent = erreur;
    if (erreur) champ.setAttribute('aria-invalid', 'true');
    else champ.removeAttribute('aria-invalid');
    return erreur === '';
  }

  function preparer() {
    const formulaires = new Set();
    document.querySelectorAll('[data-regex]').forEach(function (champ) {
      motif(champ.dataset.regex); // erreur claire dans la console si le motif n'existe pas
      if (champ.form) formulaires.add(champ.form);
    });

    formulaires.forEach(function (formulaire) {
      const champs = Array.from(formulaire.querySelectorAll('[data-regex]'));
      let envoye = false;

      formulaire.addEventListener('submit', function (event) {
        envoye = true;
        const faux = champs.filter(function (champ) { return !verifierChamp(champ); });
        if (faux.length > 0) {
          event.preventDefault();
          event.stopImmediatePropagation();
          faux[0].focus();
        }
      });

      // Après un premier envoi, le message se met à jour pendant la saisie.
      champs.forEach(function (champ) {
        champ.addEventListener('input', function () { if (envoye) verifierChamp(champ); });
      });

      // Bouton « Annuler » (reset) : efface les messages.
      formulaire.addEventListener('reset', function () {
        envoye = false;
        champs.forEach(function (champ) {
          champ.removeAttribute('aria-invalid');
          const zone = champ.nextElementSibling;
          if (zone && (zone.classList.contains('message-erreur') || zone.classList.contains('yq-regex-erreur'))) zone.textContent = '';
        });
      });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', preparer);
  else preparer();
})();
