/* Compteur d'inactivité de la session (Partie 5.5, côté navigateur).
 * Affiché par Views/front-office/includes/layout.php quand l'utilisateur est connecté.
 * Le serveur ferme la session après $sessionLifetime secondes sans requête (session.php) ;
 * ce script fait le même décompte dans la page :
 *   - à 60 s de la fin (ou dès le début si la durée est plus courte), une fenêtre affiche
 *     « La session va se fermer dans … s » ;
 *   - « Rester connecté » envoie index.php?action=ping, ce qui relance la session côté serveur ;
 *   - à 0 s, la page va sur index.php?action=logout&expire=1 : déconnexion sans rafraîchir.
 * Plusieurs onglets ouverts : chacun note son dernier chargement dans localStorage, et le
 * décompte part du plus récent. Un onglet resté inactif ne ferme donc pas la session d'un
 * onglet qui vient d'être utilisé.
 * Styles : Views/css/session-timeout.css. */
(function () {
    'use strict';

    var boite = document.getElementById('yqSessionTimeout');
    if (!boite) {
        return;
    }

    var duree = parseInt(boite.dataset.duree, 10);          // secondes (session.php)
    if (!(duree > 0)) {
        return;
    }
    var alerteA = Math.min(60, duree);                       // la fenêtre s'ouvre à 60 s de la fin
    var urlPing = boite.dataset.ping;
    var urlDeconnexion = boite.dataset.deconnexion;
    var urlExpiree = boite.dataset.expiree;

    var compteur = boite.querySelector('[data-yq-compteur]');
    var boutonRester = boite.querySelector('[data-yq-rester]');
    var boutonQuitter = boite.querySelector('[data-yq-quitter]');
    var erreur = boite.querySelector('[data-yq-erreur]');

    var CLE = 'yq_session_derniere_activite';
    var derniereActivite = Date.now();
    var minuterie = null;
    var termine = false;

    function lireStockage() {
        try {
            return parseInt(window.localStorage.getItem(CLE), 10) || 0;
        } catch (e) {
            return 0;                                         // navigation privée, stockage bloqué...
        }
    }

    function ecrireStockage(instant) {
        try {
            window.localStorage.setItem(CLE, String(instant));
        } catch (e) {
            /* sans stockage, le décompte reste propre à cet onglet */
        }
    }

    function noterActivite() {
        derniereActivite = Date.now();
        ecrireStockage(derniereActivite);
    }

    function secondesRestantes() {
        derniereActivite = Math.max(derniereActivite, lireStockage());
        return Math.ceil((derniereActivite + duree * 1000 - Date.now()) / 1000);
    }

    function afficher(restant) {
        compteur.textContent = String(restant);
        if (boite.hidden) {
            erreur.hidden = true;
            boite.hidden = false;
            boutonRester.focus();
        }
    }

    function masquer() {
        boite.hidden = true;
    }

    function aller(url) {
        termine = true;
        window.clearInterval(minuterie);
        window.location.href = url;
    }

    function tic() {
        if (termine) {
            return;
        }
        var restant = secondesRestantes();
        if (restant <= 0) {
            aller(urlExpiree);                                // déconnexion automatique
        } else if (restant <= alerteA) {
            afficher(restant);
        } else {
            masquer();
        }
    }

    boutonRester.addEventListener('click', function () {
        boutonRester.disabled = true;
        fetch(urlPing, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (reponse) {
                if (reponse.status === 401) {                 // session déjà fermée par le serveur
                    aller(urlExpiree);
                    return;
                }
                if (!reponse.ok) {
                    throw new Error('HTTP ' + reponse.status);
                }
                noterActivite();
                masquer();
            })
            .catch(function () {
                erreur.hidden = false;
            })
            .then(function () {
                boutonRester.disabled = false;
            });
    });

    boutonQuitter.addEventListener('click', function () {
        aller(urlDeconnexion);
    });

    // Un onglet en arrière-plan est ralenti par le navigateur : on recompte dès qu'il revient.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            tic();
        }
    });

    noterActivite();                                          // charger la page = activité côté serveur
    tic();
    minuterie = window.setInterval(tic, 1000);
})();
