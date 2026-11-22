<?php
$title           = 'Activités - Administration YallaQuest';
$currentPage     = 'activites';
require_once __DIR__ . '/../partials/head.php';
?>


<div class="journal-admin">

  <div class="journal-entete-page">
    <div>
      <p class="journal-sous-titre">Journal d'activité</p>
      <h1>Activités</h1>
    </div>
    <a class="journal-bouton journal-principal" href="#formulaire">+ Nouvelle activité</a>
  </div>

  <!-- Liste des activités par quête -->
  <section class="journal-panneau">
    <div class="journal-entete-panneau">
      <h2>Activités par quête</h2>
      <p class="journal-aide">14 activités dans 4 quêtes</p>
    </div>

    <div class="journal-tableau">
      <table>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th>Quantité</th>
          <th>Unité</th>
          <th>Type</th>
          <th>Actions</th>
        </tr>
        <tr class="journal-titre-groupe">
          <td colspan="7">Préparer une salade saine <span>- 6 activités - type de la quête : Préparation alimentaire</span></td>
        </tr>
        <tr>
          <td>1</td>
          <td><strong>Laver la laitue</strong></td>
          <td class="journal-description">Rincer les feuilles à l'eau froide puis les essorer.</td>
          <td class="journal-quantite">1</td>
          <td>pièces</td>
          <td>Préparation alimentaire</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td><strong>Couper les tomates</strong></td>
          <td class="journal-description">En petits dés, sans les pépins.</td>
          <td class="journal-quantite">2</td>
          <td>pièces</td>
          <td>Préparation alimentaire</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>3</td>
          <td><strong>Couper le concombre</strong></td>
          <td class="journal-description">En rondelles fines.</td>
          <td class="journal-quantite">1</td>
          <td>pièces</td>
          <td>Préparation alimentaire</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>4</td>
          <td><strong>Râper les carottes</strong></td>
          <td class="journal-description">Avec une râpe fine.</td>
          <td class="journal-quantite">100</td>
          <td>grammes</td>
          <td>Préparation alimentaire</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>5</td>
          <td><strong>Ajouter l'huile d'olive</strong></td>
          <td class="journal-description">Verser en filet sur les légumes.</td>
          <td class="journal-quantite">15</td>
          <td>ml</td>
          <td>Nutrition</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>6</td>
          <td><strong>Mélanger les ingrédients</strong></td>
          <td class="journal-description">Mélanger délicatement avant de servir.</td>
          <td class="journal-quantite">2</td>
          <td>minutes</td>
          <td>Préparation alimentaire</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr class="journal-titre-groupe">
          <td colspan="7">Bien-être du soir <span>- 3 activités - type de la quête : Méditation</span></td>
        </tr>
        <tr>
          <td>7</td>
          <td><strong>Méditer</strong></td>
          <td class="journal-description">Assis au calme, concentré sur ta respiration.</td>
          <td class="journal-quantite">10</td>
          <td>minutes</td>
          <td>Méditation</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>8</td>
          <td><strong>Exercices de respiration</strong></td>
          <td class="journal-description">Inspirer 4 s, bloquer 4 s, expirer 6 s.</td>
          <td class="journal-quantite">5</td>
          <td>minutes</td>
          <td>Respiration</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>9</td>
          <td><strong>Écrire des choses positives</strong></td>
          <td class="journal-description">Note ce qui t'a fait du bien aujourd'hui.</td>
          <td class="journal-quantite">3</td>
          <td>éléments</td>
          <td>Écriture</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr class="journal-titre-groupe">
          <td colspan="7">Séance sportive express <span>- 4 activités - type de la quête : Renforcement</span></td>
        </tr>
        <tr>
          <td>10</td>
          <td><strong>Squats</strong></td>
          <td class="journal-description">Dos droit, descendre jusqu'à l'horizontale.</td>
          <td class="journal-quantite">20</td>
          <td>répétitions</td>
          <td>Renforcement</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>11</td>
          <td><strong>Pompes</strong></td>
          <td class="journal-description">Sur les genoux si besoin.</td>
          <td class="journal-quantite">10</td>
          <td>répétitions</td>
          <td>Renforcement</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>12</td>
          <td><strong>Courir</strong></td>
          <td class="journal-description">À ton rythme, sans t'arrêter si possible.</td>
          <td class="journal-quantite">2</td>
          <td>km</td>
          <td>Course</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>13</td>
          <td><strong>S'étirer</strong></td>
          <td class="journal-description">Jambes, dos et épaules.</td>
          <td class="journal-quantite">5</td>
          <td>minutes</td>
          <td>Étirements</td>
          <td>
            <div class="journal-actions">
              <a class="journal-bouton journal-petit" href="#formulaire">Modifier</a>
              <button type="button" class="journal-bouton journal-petit journal-supprimer">Supprimer</button>
            </div>
          </td>
        </tr>
        <tr class="journal-titre-groupe">
          <td colspan="7">Hydratation de la journée <span>- 1 activité - type de la quête : Hydratation</span></td>
        </tr>
        <tr>
          <td>14</td>
          <td><strong>Boire de l'eau</strong></td>
          <td class="journal-description">Répartir sur toute la journée.</td>
          <td class="journal-quantite">2</td>
          <td>litres</td>
          <td>Hydratation</td>
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

  <!-- Formulaire : ajouter ou modifier une activité -->
  <section class="journal-panneau" id="formulaire">
    <div class="journal-entete-panneau">
      <h2>Ajouter ou modifier une activité</h2>
      <p class="journal-aide">L'unité dépend du type choisi.</p>
    </div>

    <form action="#" method="post">
      <div class="journal-grille-formulaire">
        <div class="journal-champ">
          <label for="quete">Quête</label>
          <select id="quete" name="id_quete" required>
            <option value="">Choisir une quête</option>
            <option value="1">Préparer une salade saine</option>
            <option value="2">Bien-être du soir</option>
            <option value="3">Séance sportive express</option>
            <option value="4">Hydratation de la journée</option>
          </select>
        </div>
        <div class="journal-champ">
          <label for="type">Type d'activité</label>
          <select id="type" name="id_type" required>
            <option value="">Choisir un type</option>
            <option value="1">Préparation alimentaire (pièces)</option>
            <option value="2">Préparation alimentaire (grammes)</option>
            <option value="3">Préparation alimentaire (minutes)</option>
            <option value="4">Nutrition (ml)</option>
            <option value="5">Renforcement (répétitions)</option>
            <option value="6">Course (km)</option>
            <option value="7">Étirements (minutes)</option>
            <option value="8">Méditation (minutes)</option>
            <option value="9">Respiration (minutes)</option>
            <option value="10">Écriture (éléments)</option>
            <option value="11">Hydratation (litres)</option>
          </select>
        </div>
        <div class="journal-champ">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" maxlength="100" required placeholder="Ex : Râper les carottes">
        </div>
        <div class="journal-champ">
          <label for="quantite">Quantité à atteindre</label>
          <input type="number" id="quantite" name="quantite" min="0" step="0.1" required placeholder="Ex : 100">
        </div>
      </div>

      <div class="journal-champ">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Ex : avec une râpe fine"></textarea>
      </div>

      <div class="journal-boutons-formulaire">
        <button type="submit" class="journal-bouton journal-principal">Enregistrer</button>
        <button type="reset" class="journal-bouton">Annuler</button>
      </div>
    </form>
  </section>

  <!-- Avancement des membres dans leurs quêtes -->
  <section class="journal-panneau">
    <div class="journal-entete-panneau">
      <h2>Avancement des membres</h2>
      <p class="journal-aide">Progression = activités terminées / activités de la quête</p>
    </div>

    <div class="journal-tableau">
      <table>
        <tr>
          <th>Membre</th>
          <th>Quête</th>
          <th>Progression</th>
          <th>Statut</th>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Préparer une salade saine</td>
          <td>
            <div class="journal-progression">
              <div class="journal-barre"><div class="journal-remplissage" style="width: 50%"></div></div>
              3/6 - 50 %
            </div>
          </td>
          <td><span class="journal-statut journal-en-cours">En cours</span></td>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Bien-être du soir</td>
          <td>
            <div class="journal-progression">
              <div class="journal-barre"><div class="journal-remplissage" style="width: 100%"></div></div>
              3/3 - 100 %
            </div>
          </td>
          <td><span class="journal-statut journal-termine">Terminée</span></td>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Séance sportive express</td>
          <td>
            <div class="journal-progression">
              <div class="journal-barre"><div class="journal-remplissage" style="width: 0%"></div></div>
              0/4 - 0 %
            </div>
          </td>
          <td><span class="journal-statut journal-en-cours">En cours</span></td>
        </tr>
        <tr>
          <td>Yassine K.</td>
          <td>Séance sportive express</td>
          <td>
            <div class="journal-progression">
              <div class="journal-barre"><div class="journal-remplissage" style="width: 25%"></div></div>
              1/4 - 25 %
            </div>
          </td>
          <td><span class="journal-statut journal-en-cours">En cours</span></td>
        </tr>
        <tr>
          <td>Mariem T.</td>
          <td>Hydratation de la journée</td>
          <td>
            <div class="journal-progression">
              <div class="journal-barre"><div class="journal-remplissage" style="width: 100%"></div></div>
              1/1 - 100 %
            </div>
          </td>
          <td><span class="journal-statut journal-validee">Validée</span></td>
        </tr>
      </table>
    </div>
  </section>

  <!-- Activités terminées par les membres  -->
  <section class="journal-panneau">
    <div class="journal-entete-panneau">
      <h2>Dernières activités terminées</h2>
      <p class="journal-aide">Annuler une activité fait baisser la progression du membre.</p>
    </div>

    <div class="journal-tableau">
      <table>
        <tr>
          <th>Membre</th>
          <th>Quête</th>
          <th>Activité</th>
          <th>Date</th>
          <th>Ressenti</th>
          <th>Note ou preuve</th>
          <th>Action</th>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Préparer une salade saine</td>
          <td>Couper le concombre</td>
          <td>15/11/2026</td>
          <td>4/5</td>
          <td class="journal-description">Rondelles fines, prêtes.</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Préparer une salade saine</td>
          <td>Couper les tomates</td>
          <td>15/11/2026</td>
          <td>4/5</td>
          <td class="journal-description">-</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Préparer une salade saine</td>
          <td>Laver la laitue</td>
          <td>15/11/2026</td>
          <td>5/5</td>
          <td class="journal-description">-</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
        <tr>
          <td>Sana B.</td>
          <td>Bien-être du soir</td>
          <td>Écrire des choses positives</td>
          <td>15/11/2026</td>
          <td>5/5</td>
          <td class="journal-description">Carnet du soir : 3 choses notées.</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
        <tr>
          <td>Yassine K.</td>
          <td>Séance sportive express</td>
          <td>Squats</td>
          <td>14/11/2026</td>
          <td>3/5</td>
          <td class="journal-description">Fait en 2 séries de 10.</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
        <tr>
          <td>Mariem T.</td>
          <td>Hydratation de la journée</td>
          <td>Boire de l'eau</td>
          <td>14/11/2026</td>
          <td>4/5</td>
          <td class="journal-description">Bouteille de 1,5 L + 2 verres.</td>
          <td><button type="button" class="journal-bouton journal-petit journal-supprimer">Annuler</button></td>
        </tr>
      </table>
    </div>
  </section>

</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
