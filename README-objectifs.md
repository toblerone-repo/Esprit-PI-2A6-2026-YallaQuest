# YallaQuest — Module Objectifs & Étapes de Progression (CRUD MVC)
**Projet Technologies Web 2A — ESPRIT (2026-2027)**  
*« Chaque habitude est une quête. »*

---

## 🎯 Présentation du Projet & Objectifs

Le module **Objectifs & Étapes** de **YallaQuest** est une application web modulaire développée selon le patron de conception **MVC (Modèle - Vue - Contrôleur)** en PHP 8 / MySQL, strictement conforme aux enseignements du cours de Technologies Web 2A (*Chapitre 4 PHP*, *Chapitre 5 POO*, *Chapitre 6 PHP PDO - CRUD*).

Il permet à chaque aventurier :
1. **De planifier des buts de santé et bien-être** (activités sportives, sommeil, nutrition, santé mentale, habitudes) sous forme de notes pour le futur avec une date/heure de début et de fin.
2. **De découper chaque but en étapes obligatoires (`EtapeObjectif`)** : chaque objectif contient un ensemble d'étapes jalons nécessaires à son accomplissement.
3. **De suivre sa progression en temps réel** : chaque étape validée met à jour le ratio d'avancement et la barre de progression en %.
4. **D'automatiser l'accomplissement** : dès que toutes les étapes d'un objectif sont validées, l'objectif se termine automatiquement (`statut = 'atteint'`), attribue des points d'expérience (**+50 XP**), fait progresser le niveau de l'aventurier et affiche une notification de félicitation.
5. **D'administrer les objectifs (Back-Office)** : supervision globale, filtrage dynamique par état, et création/assignation directe d'objectifs avec étapes.


---

## 🏛️ Architecture Logicielle MVC (Conforme au Cours ESPRIT)

Le code a été simplifié pour être immédiatement compréhensible et défendable en soutenance académique, en évitant toute bibliothèque externe superflue :

```text
C:\xampp\htdocs\objectif/
├── index.php                         # Point d'entrée racine (redirection Front-Office)
├── config.php                        # Connexion PDO Singleton standard ESPRIT
├── logo.jpeg                         # Logo officiel Félin Géométrique
├── README.md                         # Documentation complète du projet
│
├── Front-Office/                     # Espace Aventurier (Joueur)
│   ├── index.php                     # Redirection vers la vue
│   ├── objectifs.php                 # Fichier d'accès direct
│   ├── Model/
│   │   ├── Objectif.php              # Entité Objectif (attributs privés, constructeur, getters/setters)
│   │   ├── EtapeObjectif.php         # Entité EtapeObjectif (attributs privés, constructeur, getters/setters)
│   │   └── Utilisateur.php           # Entité Utilisateur
│   ├── Controller/
│   │   ├── ObjectifC.php             # Contrôleur CRUD principal (requêtes préparées PDO)
│   │   └── ObjectifController.php    # Alias de compatibilité
│   ├── View/
│   │   └── objectifs.php             # Vue Front-Office complète (cards, formulaire, progression XP)
│   ├── css/
│   │   └── style.css                 # Feuilles de style Front-Office
│   └── js/
│       └── objectifs.js              # Fonctions JS simples de validation et interaction
│
├── Back-Office/                      # Espace Administration (Supervision)
│   ├── index.php                     # Redirection vers la vue d'administration
│   ├── objectifs.php                 # Fichier d'accès direct
│   ├── Model/ (Objectif.php, EtapeObjectif.php)
│   ├── Controller/ (ObjectifC.php, AdminController.php)
│   ├── View/
│   │   └── objectifs.php             # Table de supervision, filtres et attribution de but
│   └── css/
│       └── admin.css                 # Feuilles de style Back-Office
│
└── shared/                           # Ressources partagées (UI & Design)
    ├── css/yq.css                    # Charte graphique (noir nuit, lilas, ivoire, sauge, or)
    ├── fonts/                        # Polices web intégrées (Playfair Display & Inter)
    └── img/piliers/                  # Visuels d'ambiance des 5 piliers bien-être


## 🎨 Charte Graphique YallaQuest

L'interface respecte scrupuleusement la charte graphique de YallaQuest :
- **Noir Nuit** (`#15131F`) : Fond sombre immersif.
- **Lilas** (`#EBC1F2`) : Éléments d'accent, badges d'XP, boutons principaux.
- **Ivoire** (`#F6F4F8`) : Cartes d'objectifs à fort contraste et lisibilité.
- **Or Doux** (`#D7A64B`) : Totems et distinctions.
- **Sauge** (`#7BAE8C`) : Indicateurs de réussite et statuts accomplis.
- **Typographies** : *Playfair Display* (titres nobles) et *Inter* (corps de texte moderne).

---

## 🚀 Guide d'Exécution & Démarrage Rapide

1. Ouvrez le panneau de contrôle **XAMPP** et démarrez les modules **Apache** et **MySQL**.
2. Assurez-vous que la base de données `yallaquest` existe dans phpMyAdmin (le contrôleur injecte automatiquement les données initiales de démonstration si la table est vide).
3. Ouvrez votre navigateur web et accédez aux adresses locales :

- **Portail & Espace Aventurier (Front-Office) :**  
  👉 [http://localhost/objectif/](http://localhost/objectif/)  
  *(ou [http://localhost/objectif/Front-Office/View/objectifs.php](http://localhost/objectif/Front-Office/View/objectifs.php))*

- **Panneau d'Administration (Back-Office) :**  
  👉 [http://localhost/objectif/Back-Office/View/objectifs.php](http://localhost/objectif/Back-Office/View/objectifs.php)

*(Un bouton direct permet de basculer en 1 clic entre le Front-Office et le Back-Office sur chaque écran).*

---

## 🧪 Scénarios de Test pour la Soutenance

| Étape | Action à réaliser | Résultat attendu |
| :---: | :--- | :--- |
| **1** | Cliquer sur **« Nouveau but »** | La boîte de dialogue s'ouvre avec le formulaire. |
| **2** | Saisir un titre, des dates et ajouter 2 étapes via le champ `+ Ajouter` | Les étapes s'affichent sous forme de liste dynamique. |
| **3** | Cliquer sur **« Enregistrer l'objectif »** | L'objectif est inséré en base de données et la carte s'affiche instantanément. |
| **4** | Cocher la 1ère étape sur la carte | La barre de progression passe à 50% et la date de validation s'affiche. |
| **5** | Cocher la 2ème étape (dernière) | **L'objectif se termine automatiquement** : le titre est barré, le statut devient « Accompli », +50 XP sont crédités et la jauge de niveau augmente. |
| **6** | Cliquer sur le bouton **« Administration »** | Redirection vers le Back-Office : l'objectif et ses étapes complétées sont visibles dans le tableau de supervision. |
| **7** | Cliquer sur **« Supprimer »** | L'objectif et ses étapes sont supprimés en cascade dans la base de données. |

---

## 👥 Informations Académiques

- **Module :** Projet Technologies Web (PW)
- **Niveau :** 2ème Année Ingénieur (2A)
- **Établissement :** ESPRIT (École Supérieure Privée d'Ingénierie et de Technologies)
- **Année Universitaire :** 2026 - 2027
