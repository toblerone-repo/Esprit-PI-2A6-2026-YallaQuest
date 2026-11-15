<?php
$title = 'Compétences | YallaQuest Admin';
$currentPage = 'competences';
require_once __DIR__ . '/../partials/head.php';
?>

<div class="competences-admin">

  <div class="competences-admin-entete">
    <div>
      <p class="competences-admin-sur-titre">Compétences et XP</p>
      <h1>Compétences</h1>
    </div>
    <a class="competences-admin-bouton competences-admin-bouton-principal" href="#formulaire">+ Nouvelle compétence</a>
  </div>

  <section class="competences-admin-bloc">
    <div class="competences-admin-bloc-titre">
      <h2>Liste des compétences</h2>
      <p class="competences-admin-info">5 compétences · chaque membre les possède toutes · total des parts : 100 %</p>
    </div>

    <div class="competences-admin-tableau">
      <table>
        <tr>
            <th>ID</th>
            <th>Visuel</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Part de l'XP</th>
            <th>Totem</th>
            <th>Actions</th>
        </tr>
          <tr>
            <td>1</td>
            <td>force.jpg</td>
            <td><strong>Force</strong></td>
            <td>Renforcement musculaire</td>
            <td><span class="competences-admin-pourcentage">30 %</span></td>
            <td class="competences-admin-totem">Le Bélier</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>endurance.jpg</td>
            <td><strong>Endurance</strong></td>
            <td>Marche, cardio</td>
            <td><span class="competences-admin-pourcentage">30 %</span></td>
            <td class="competences-admin-totem">La Voile</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>3</td>
            <td>mobilite.jpg</td>
            <td><strong>Mobilité</strong></td>
            <td>Étirements, souplesse</td>
            <td><span class="competences-admin-pourcentage">10 %</span></td>
            <td class="competences-admin-totem">La Plume</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>4</td>
            <td>focus.jpg</td>
            <td><strong>Focus</strong></td>
            <td>Respiration, sérénité</td>
            <td><span class="competences-admin-pourcentage">20 %</span></td>
            <td class="competences-admin-totem">L'Arbre</td>
            <td>
              <div class="competences-admin-actions">
                <a class="competences-admin-bouton competences-admin-bouton-petit" href="#formulaire">Modifier</a>
                <button type="button" class="competences-admin-bouton competences-admin-bouton-petit competences-admin-bouton-supprimer">Supprimer</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>5</td>
            <td>nutrition.jpg</td>
            <td><strong>Nutrition</strong></td>
            <td>Recettes locales, hydratation</td>
            <td><span class="competences-admin-pourcentage">10 %</span></td>
            <td class="competences-admin-totem">La Graine</td>
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
      <h2>Ajouter une compétence</h2>
    </div>

    <form action="#" method="post">
      <div class="competences-admin-champs">
        <div class="competences-admin-champ">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" maxlength="50" required placeholder="Ex : Force">
        </div>
        <div class="competences-admin-champ">
          <label for="pourcentage">Part de l'XP totale (%)</label>
          <input type="number" id="pourcentage" name="pourcentage" min="0" max="100" required placeholder="Ex : 20">
        </div>
        <div class="competences-admin-champ">
          <label for="visuel">Visuel de la compétence</label>
          <input type="file" id="visuel" name="visuel">
        </div>
      </div>

      <div class="competences-admin-champ">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Ex : Renforcement musculaire"></textarea>
      </div>

      <div class="competences-admin-boutons">
        <button type="submit" class="competences-admin-bouton competences-admin-bouton-principal">Enregistrer</button>
        <button type="reset" class="competences-admin-bouton">Annuler</button>
      </div>
    </form>
  </section>

</div>

</body>
</html>
