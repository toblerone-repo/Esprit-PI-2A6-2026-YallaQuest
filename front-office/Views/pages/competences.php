<!DOCTYPE html>
<html lang="fr">
<head>
  <?php
    $pageTitle = 'Mes compétences · YallaQuest';
    $currentPage = 'competences';
    require_once 'includes/head.php';
  ?>
</head>
<body>

<main class="competences-page">

  <section class="competences-hero">
    <p class="competences-sur-titre">Progression · Semaine 18</p>
    <h1>Mes compétences</h1>
    <p class="competences-intro">Chaque quête validée te rapporte de l'XP, répartie entre tes compétences. Atteins le seuil d'une compétence pour débloquer son totem.</p>
    <p class="competences-total-xp"><strong>1 060 XP</strong> au total · Niveau 4</p>
  </section>

  <section class="competences-bloc">
    <h2>XP par compétence</h2>

    <div class="competences-grille">
      <article class="competences-carte">
        <img src="img/competences/force.jpg" alt="Force">
        <div class="competences-carte-contenu">
          <h3>Force</h3>
          <p class="competences-part">Part de l'XP : 30 %</p>
          <div class="competences-barre">
            <div class="competences-remplissage" style="width: 100%;"></div>
          </div>
          <p class="competences-xp"><strong>300 XP</strong> · seuil du totem : 300 XP</p>
          <p class="competences-totem">Totem obtenu : <strong>Le Bélier</strong></p>
        </div>
      </article>
      <article class="competences-carte">
        <img src="img/competences/endurance.jpg" alt="Endurance">
        <div class="competences-carte-contenu">
          <h3>Endurance</h3>
          <p class="competences-part">Part de l'XP : 30 %</p>
          <div class="competences-barre">
            <div class="competences-remplissage" style="width: 70%;"></div>
          </div>
          <p class="competences-xp"><strong>420 XP</strong> · seuil du totem : 600 XP</p>
          <p class="competences-totem">Totem à débloquer : <strong>La Voile</strong></p>
        </div>
      </article>
      <article class="competences-carte">
        <img src="img/competences/mobilite.jpg" alt="Mobilité">
        <div class="competences-carte-contenu">
          <h3>Mobilité</h3>
          <p class="competences-part">Part de l'XP : 10 %</p>
          <div class="competences-barre">
            <div class="competences-remplissage" style="width: 30%;"></div>
          </div>
          <p class="competences-xp"><strong>60 XP</strong> · seuil du totem : 200 XP</p>
          <p class="competences-totem">Totem à débloquer : <strong>La Plume</strong></p>
        </div>
      </article>
      <article class="competences-carte">
        <img src="img/competences/focus.jpg" alt="Focus">
        <div class="competences-carte-contenu">
          <h3>Focus</h3>
          <p class="competences-part">Part de l'XP : 20 %</p>
          <div class="competences-barre">
            <div class="competences-remplissage" style="width: 62%;"></div>
          </div>
          <p class="competences-xp"><strong>250 XP</strong> · seuil du totem : 400 XP</p>
          <p class="competences-totem">Totem à débloquer : <strong>L'Arbre</strong></p>
        </div>
      </article>
      <article class="competences-carte">
        <img src="img/competences/nutrition.jpg" alt="Nutrition">
        <div class="competences-carte-contenu">
          <h3>Nutrition</h3>
          <p class="competences-part">Part de l'XP : 10 %</p>
          <div class="competences-barre">
            <div class="competences-remplissage" style="width: 12%;"></div>
          </div>
          <p class="competences-xp"><strong>30 XP</strong> · seuil du totem : 250 XP</p>
          <p class="competences-totem">Totem à débloquer : <strong>La Graine</strong></p>
        </div>
      </article>
    </div>
  </section>

  <section class="competences-bloc">
    <h2>Mes totems</h2>

    <div class="competences-tableau">
      <table>
        <tr>
          <th>Visuel</th>
          <th>Totem</th>
          <th>Compétence</th>
          <th>Seuil</th>
          <th>Statut</th>
          <th>Obtenu le</th>
        </tr>
        <tr>
          <td><img src="img/totems/belier.jpg" alt="Le Bélier"></td>
          <td>Le Bélier</td>
          <td>Force</td>
          <td>300 XP</td>
          <td><span class="competences-statut competences-obtenu">Obtenu</span></td>
          <td>12/11/2026</td>
        </tr>
        <tr>
          <td><img class="competences-grise" src="img/totems/voile.jpg" alt="La Voile"></td>
          <td>La Voile</td>
          <td>Endurance</td>
          <td>600 XP</td>
          <td><span class="competences-statut competences-verrouille">Verrouillé</span></td>
          <td>—</td>
        </tr>
        <tr>
          <td><img class="competences-grise" src="img/totems/plume.jpg" alt="La Plume"></td>
          <td>La Plume</td>
          <td>Mobilité</td>
          <td>200 XP</td>
          <td><span class="competences-statut competences-verrouille">Verrouillé</span></td>
          <td>—</td>
        </tr>
        <tr>
          <td><img class="competences-grise" src="img/totems/arbre.jpg" alt="L'Arbre"></td>
          <td>L'Arbre</td>
          <td>Focus</td>
          <td>400 XP</td>
          <td><span class="competences-statut competences-verrouille">Verrouillé</span></td>
          <td>—</td>
        </tr>
        <tr>
          <td><img class="competences-grise" src="img/totems/graine.jpg" alt="La Graine"></td>
          <td>La Graine</td>
          <td>Nutrition</td>
          <td>250 XP</td>
          <td><span class="competences-statut competences-verrouille">Verrouillé</span></td>
          <td>—</td>
        </tr>
      </table>
    </div>
  </section>

</main>

</body>
</html>
