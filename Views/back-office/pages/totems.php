<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Jeton de sécurité invalide. Rechargez la page.');
}

$title = 'Totems | YallaQuest Admin';
$currentPage = 'totems';
require_once __DIR__ . '/../partials/head.php';
?>

<div class="competences-admin">

  <div class="competences-admin-entete">
    <div>
      <p class="competences-admin-sur-titre">Compétences et XP</p>
      <h1>Totems</h1>
    </div>
    <a class="competences-admin-bouton competences-admin-bouton-principal" href="#formulaire">+ Nouveau totem</a>
  </div>

  <section class="competences-admin-bloc">
    <div class="competences-admin-bloc-titre">
      <h2>Liste des totems</h2>
      <p class="competences-admin-info">Un totem par compétence · débloqué quand l'XP du membre atteint le seuil</p>
    </div>

    <div class="competences-admin-tableau">
      <table>
        <tr>
            <th>ID</th>
            <th>Visuel</th>
            <th>Nom</th>
            <th>Compétence</th>
            <th>Seuil XP</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
          <tr>
            <td>1</td>
            <td>belier.jpg</td>
            <td class="competences-admin-totem">Le Bélier</td>
            <td>Force</td>
            <td>300 XP</td>
            <td>Symbole de puissance et de détermination</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>voile.jpg</td>
            <td class="competences-admin-totem">La Voile</td>
            <td>Endurance</td>
            <td>600 XP</td>
            <td>Avancer longtemps, porté par le souffle</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>3</td>
            <td>plume.jpg</td>
            <td class="competences-admin-totem">La Plume</td>
            <td>Mobilité</td>
            <td>200 XP</td>
            <td>Légèreté et liberté de mouvement</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>4</td>
            <td>arbre.jpg</td>
            <td class="competences-admin-totem">L'Arbre</td>
            <td>Focus</td>
            <td>400 XP</td>
            <td>Enraciné, calme et concentré</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>5</td>
            <td>graine.jpg</td>
            <td class="competences-admin-totem">La Graine</td>
            <td>Nutrition</td>
            <td>250 XP</td>
            <td>Ce que tu sèmes te fait grandir</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
      </table>
    </div>
  </section>

  <section class="competences-admin-bloc" id="formulaire">
    <div class="competences-admin-bloc-titre">
      <h2>Ajouter un totem</h2>
    </div>

    <form action="#" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
      <div class="competences-admin-champs">
        <div class="competences-admin-champ">
          <label for="nom">Nom du totem</label>
          <input type="text" id="nom" name="nom" maxlength="50" required placeholder="Ex : Le Bélier">
        </div>
        <div class="competences-admin-champ">
          <label for="id_competence">Compétence</label>
          <select id="id_competence" name="id_competence" required>
            <option value="">Choisir…</option>
            <option value="1">Force</option>
            <option value="2">Endurance</option>
            <option value="3">Mobilité</option>
            <option value="4">Focus</option>
            <option value="5">Nutrition</option>
          </select>
        </div>
        <div class="competences-admin-champ">
          <label for="seuil">Seuil d'XP</label>
          <input type="number" id="seuil" name="seuil" min="0" step="10" required placeholder="Ex : 300">
        </div>
        <div class="competences-admin-champ">
          <label for="visuel">Visuel du totem</label>
          <input type="file" id="visuel" name="visuel">
        </div>
      </div>

      <div class="competences-admin-champ">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Ex : Symbole de puissance et de détermination"></textarea>
      </div>

      <div class="competences-admin-boutons">
        <button type="submit" class="competences-admin-bouton competences-admin-bouton-principal">Enregistrer</button>
        <button type="reset" class="competences-admin-bouton">Annuler</button>
      </div>
    </form>
  </section>

</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
