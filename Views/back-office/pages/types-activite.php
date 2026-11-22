<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Gestion des types d'activité de YallaQuest.">
  <title>Types d'activité - Administration YallaQuest</title>

  
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display&display=swap">

  <link rel="stylesheet" href="../../css/journal-admin.css">
</head>
<body>
<?php include_once __DIR__ . '/../partials/sidebar.php'; ?>
<?php include_once __DIR__ . '/../partials/header.php'; ?>


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

<script src="../../js/regex.js"></script>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
