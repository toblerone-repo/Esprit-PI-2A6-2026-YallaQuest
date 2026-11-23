<?php
// Eya : types d’activité. Houssem : protection CSRF/XSS.
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'types-activite';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

?>
<?php
$title           = "Types d'activité - Administration YallaQuest";
$currentPage     = 'types-activite';
require_once __DIR__ . '/../partials/head.php';
?>


<div class="journal-admin">

  <div class="journal-entete-page">
    <div>
      <p class="journal-sous-titre">Journal d'activité</p>
      <h1>Types d'activité</h1>
    </div>
    <a class="journal-bouton journal-principal" href="#formulaire">+ Nouveau type</a>
  </div>

  <!-- Liste des types -->
  <section class="journal-panneau">
    <div class="journal-entete-panneau">
      <h2>Liste des types</h2>
      <p class="journal-aide">11 types (un type par unité de mesure)</p>
    </div>

    <div class="journal-tableau">
      <table>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Unité de mesure</th>
          <th>Activités</th>
          <th>Quêtes</th>
          <th>Actions</th>
        </tr>
        <tr>
          <td>1</td>
          <td><strong>Préparation alimentaire</strong></td>
          <td>pièces</td>
          <td><span class="journal-nombre">3</span></td>
          <td><span class="journal-nombre">1</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td><strong>Préparation alimentaire</strong></td>
          <td>grammes</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>3</td>
          <td><strong>Préparation alimentaire</strong></td>
          <td>minutes</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>4</td>
          <td><strong>Nutrition</strong></td>
          <td>ml</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>5</td>
          <td><strong>Renforcement</strong></td>
          <td>répétitions</td>
          <td><span class="journal-nombre">2</span></td>
          <td><span class="journal-nombre">1</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>6</td>
          <td><strong>Course</strong></td>
          <td>km</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>7</td>
          <td><strong>Étirements</strong></td>
          <td>minutes</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>8</td>
          <td><strong>Méditation</strong></td>
          <td>minutes</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">1</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>9</td>
          <td><strong>Respiration</strong></td>
          <td>minutes</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>10</td>
          <td><strong>Écriture</strong></td>
          <td>éléments</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">0</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>11</td>
          <td><strong>Hydratation</strong></td>
          <td>litres</td>
          <td><span class="journal-nombre">1</span></td>
          <td><span class="journal-nombre">1</span></td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </section>

  <!-- Formulaire : ajouter ou modifier un type -->
  <section class="journal-panneau" id="formulaire">
    <div class="journal-entete-panneau">
      <h2>Ajouter ou modifier un type</h2>
      <p class="journal-aide">Même activité avec une autre unité : crée un nouveau type.</p>
    </div>

    <form action="#" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
      <div class="journal-grille-formulaire">
        <div class="journal-champ">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" maxlength="50" required data-regex="libelle" placeholder="Ex : Préparation alimentaire">
        </div>
        <div class="journal-champ">
          <label for="unite">Unité de mesure</label>
          <input type="text" id="unite" name="unite_mesure" data-regex="unite" maxlength="20" required placeholder="Ex : grammes">
        </div>
      </div>

      <div class="journal-boutons-formulaire">
        <button type="submit" class="journal-bouton journal-principal">Enregistrer</button>
        <button type="reset" class="journal-bouton">Annuler</button>
      </div>
    </form>
  </section>

</div>

<script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" src="../../js/regex.js"></script>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
