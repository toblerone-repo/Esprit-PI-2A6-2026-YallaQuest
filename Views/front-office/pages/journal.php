<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <?php
    $pageTitle = 'Mon journal - YallaQuest';
    $currentPage = 'journal';
    require_once __DIR__ . '/../includes/head.php';
  ?>
</head>
<body>
<?php include_once __DIR__ . '/../includes/navbar.php'; ?>



<main class="journal-page">

  
  <section class="journal-intro">
    <p class="journal-sous-titre">Journal d'activité</p>
    <h1>Mon journal</h1>
    <p>Termine tes quêtes étape par étape : chaque activité réalisée fait avancer ta progression.</p>
  </section>

  
  <section class="journal-resume">
    <div class="journal-case">
      <p class="journal-libelle">Quêtes en cours</p>
      <p class="journal-chiffre">2</p>
    </div>
    <div class="journal-case">
      <p class="journal-libelle">Activités terminées</p>
      <p class="journal-chiffre">6</p>
    </div>
    <div class="journal-case">
      <p class="journal-libelle">Quêtes à valider</p>
      <p class="journal-chiffre">1</p>
    </div>
  </section>

  <!-- Mes quêtes -->
  <section class="journal-bloc">
    <h2>Mes quêtes</h2>

    <div class="journal-liste-quetes">

      <!-- Quête 1 : 3 activités terminées sur 6 -->
      <article class="journal-carte-quete">
        <div class="journal-entete-quete">
          <div>
            <p class="journal-infos">Préparation alimentaire - Facile</p>
            <h3>Préparer une salade saine</h3>
          </div>
          <span class="journal-statut journal-en-cours">En cours</span>
        </div>

        <div class="journal-progression">
          <p>3/6 activités terminées <strong>50 %</strong></p>
          
          <div class="journal-barre"><div class="journal-remplissage" style="width: 50%"></div></div>
        </div>

        <ol class="journal-liste-activites">
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Laver la laitue</p>
              <p class="journal-description">Rincer les feuilles à l'eau froide puis les essorer.</p>
              <p><span class="journal-type">Préparation alimentaire</span> <span class="journal-quantite">1 pièce</span></p>
            </div>
            <p class="journal-date">Terminée le 15/11</p>
          </li>
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Couper les tomates</p>
              <p class="journal-description">En petits dés, sans les pépins.</p>
              <p><span class="journal-type">Préparation alimentaire</span> <span class="journal-quantite">2 pièces</span></p>
            </div>
            <p class="journal-date">Terminée le 15/11</p>
          </li>
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Couper le concombre</p>
              <p class="journal-description">En rondelles fines.</p>
              <p><span class="journal-type">Préparation alimentaire</span> <span class="journal-quantite">1 pièce</span></p>
            </div>
            <p class="journal-date">Terminée le 15/11</p>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Râper les carottes</p>
              <p class="journal-description">Avec une râpe fine.</p>
              <p><span class="journal-type">Préparation alimentaire</span> <span class="journal-quantite">100 grammes</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Ajouter l'huile d'olive</p>
              <p class="journal-description">Verser en filet sur les légumes.</p>
              <p><span class="journal-type">Nutrition</span> <span class="journal-quantite">15 ml</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Mélanger les ingrédients</p>
              <p class="journal-description">Mélanger délicatement avant de servir.</p>
              <p><span class="journal-type">Préparation alimentaire</span> <span class="journal-quantite">2 minutes</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
        </ol>

        <div class="journal-pied-quete">
          <p class="journal-xp">+30 XP</p>
          <p class="journal-aide">Termine toutes les activités pour valider la quête.</p>
        </div>
      </article>

      <!-- Quête 2 : toutes les activités sont terminées -->
      <article class="journal-carte-quete journal-terminee">
        <div class="journal-entete-quete">
          <div>
            <p class="journal-infos">Méditation - Facile</p>
            <h3>Bien-être du soir</h3>
          </div>
          <span class="journal-statut journal-termine">Terminée</span>
        </div>

        <div class="journal-progression">
          <p>3/3 activités terminées <strong>100 %</strong></p>
          <div class="journal-barre"><div class="journal-remplissage" style="width: 100%"></div></div>
        </div>

        <ol class="journal-liste-activites">
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Méditer</p>
              <p class="journal-description">Assis au calme, concentré sur ta respiration.</p>
              <p><span class="journal-type">Méditation</span> <span class="journal-quantite">10 minutes</span></p>
            </div>
            <p class="journal-date">Terminée le 14/11</p>
          </li>
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Exercices de respiration</p>
              <p class="journal-description">Inspirer 4 secondes, bloquer 4 secondes, expirer 6 secondes.</p>
              <p><span class="journal-type">Respiration</span> <span class="journal-quantite">5 minutes</span></p>
            </div>
            <p class="journal-date">Terminée le 14/11</p>
          </li>
          <li class="journal-activite journal-faite">
            <span class="journal-coche">✓</span>
            <div class="journal-texte">
              <p class="journal-nom">Écrire des choses positives</p>
              <p class="journal-description">Note ce qui t'a fait du bien aujourd'hui.</p>
              <p><span class="journal-type">Écriture</span> <span class="journal-quantite">3 éléments</span></p>
            </div>
            <p class="journal-date">Terminée le 15/11</p>
          </li>
        </ol>

        <div class="journal-pied-quete">
          <p class="journal-xp">+20 XP</p>
          <form action="#" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input type="hidden" name="id_quete" value="2">
            <button type="submit" class="journal-bouton journal-principal">Valider la quête</button>
          </form>
        </div>
      </article>

      <!-- Quête 3 : aucune activité terminée -->
      <article class="journal-carte-quete">
        <div class="journal-entete-quete">
          <div>
            <p class="journal-infos">Renforcement - Moyen</p>
            <h3>Séance sportive express</h3>
          </div>
          <span class="journal-statut journal-en-cours">En cours</span>
        </div>

        <div class="journal-progression">
          <p>0/4 activités terminées <strong>0 %</strong></p>
          <div class="journal-barre"><div class="journal-remplissage" style="width: 0%"></div></div>
        </div>

        <ol class="journal-liste-activites">
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Squats</p>
              <p class="journal-description">Dos droit, descendre jusqu'à l'horizontale.</p>
              <p><span class="journal-type">Renforcement</span> <span class="journal-quantite">20 répétitions</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Pompes</p>
              <p class="journal-description">Sur les genoux si besoin.</p>
              <p><span class="journal-type">Renforcement</span> <span class="journal-quantite">10 répétitions</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">Courir</p>
              <p class="journal-description">À ton rythme, sans t'arrêter si possible.</p>
              <p><span class="journal-type">Course</span> <span class="journal-quantite">2 km</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
          <li class="journal-activite">
            <span class="journal-coche"></span>
            <div class="journal-texte">
              <p class="journal-nom">S'étirer</p>
              <p class="journal-description">Jambes, dos et épaules.</p>
              <p><span class="journal-type">Étirements</span> <span class="journal-quantite">5 minutes</span></p>
            </div>
            <a class="journal-bouton" href="#terminer">Terminer</a>
          </li>
        </ol>

        <div class="journal-pied-quete">
          <p class="journal-xp">+50 XP</p>
          <p class="journal-aide">Termine toutes les activités pour valider la quête.</p>
        </div>
      </article>

    </div>
  </section>

  <!-- Formulaire : terminer une activité -->
  <section class="journal-bloc" id="terminer">
    <h2>Terminer une activité</h2>

    <form class="journal-formulaire" action="#" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

      <div class="journal-champ">
        <label for="activite">Activité réalisée</label>
        <select id="activite" name="id_activite" required>
          <option value="">Choisir une activité</option>
          <option value="4">Préparer une salade saine - Râper les carottes (100 grammes)</option>
          <option value="5">Préparer une salade saine - Ajouter l'huile d'olive (15 ml)</option>
          <option value="6">Préparer une salade saine - Mélanger les ingrédients (2 minutes)</option>
          <option value="10">Séance sportive express - Squats (20 répétitions)</option>
          <option value="11">Séance sportive express - Pompes (10 répétitions)</option>
          <option value="12">Séance sportive express - Courir (2 km)</option>
          <option value="13">Séance sportive express - S'étirer (5 minutes)</option>
        </select>
      </div>

      <div class="journal-champ">
        <label for="ressenti">Mon ressenti</label>
        <select id="ressenti" name="ressenti">
          <option value="1">1 - Très dur</option>
          <option value="2">2 - Dur</option>
          <option value="3" selected>3 - Correct</option>
          <option value="4">4 - Bien</option>
          <option value="5">5 - Super</option>
        </select>
      </div>

      <div class="journal-champ">
        <label for="note">Note ou preuve (facultatif)</label>
        <textarea id="note" name="note_preuve" rows="3" maxlength="255" placeholder="Ex : carottes râpées pour 2 personnes"></textarea>
      </div>

      <div class="journal-champ-case">
        <input type="checkbox" id="confirme" name="confirme" required>
        <label for="confirme">Je confirme avoir réalisé cette activité.</label>
      </div>

      <button type="submit" class="journal-bouton journal-principal">Terminer l'activité</button>
      <p class="journal-aide">La date de validation est enregistrée automatiquement.</p>

    </form>
  </section>

  <!-- Historique -->
  <section class="journal-bloc">
    <h2>Mon historique</h2>

    <div class="journal-tableau">
      <table>
        <tr>
          <th>Date</th>
          <th>Quête</th>
          <th>Activité</th>
          <th>Objectif</th>
          <th>Type</th>
          <th>Ressenti</th>
        </tr>
        <tr>
          <td>15/11/2026</td>
          <td>Préparer une salade saine</td>
          <td>Couper le concombre</td>
          <td>1 pièce</td>
          <td>Préparation alimentaire</td>
          <td>4/5</td>
        </tr>
        <tr>
          <td>15/11/2026</td>
          <td>Préparer une salade saine</td>
          <td>Couper les tomates</td>
          <td>2 pièces</td>
          <td>Préparation alimentaire</td>
          <td>4/5</td>
        </tr>
        <tr>
          <td>15/11/2026</td>
          <td>Préparer une salade saine</td>
          <td>Laver la laitue</td>
          <td>1 pièce</td>
          <td>Préparation alimentaire</td>
          <td>5/5</td>
        </tr>
        <tr>
          <td>15/11/2026</td>
          <td>Bien-être du soir</td>
          <td>Écrire des choses positives</td>
          <td>3 éléments</td>
          <td>Écriture</td>
          <td>5/5</td>
        </tr>
        <tr>
          <td>14/11/2026</td>
          <td>Bien-être du soir</td>
          <td>Exercices de respiration</td>
          <td>5 minutes</td>
          <td>Respiration</td>
          <td>4/5</td>
        </tr>
        <tr>
          <td>14/11/2026</td>
          <td>Bien-être du soir</td>
          <td>Méditer</td>
          <td>10 minutes</td>
          <td>Méditation</td>
          <td>3/5</td>
        </tr>
      </table>
    </div>
  </section>

</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
