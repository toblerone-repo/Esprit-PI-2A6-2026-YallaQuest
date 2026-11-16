<?php

// Les mêmes choix sont utilisés par le front-office et l'administration.
$categories = [
    'all' => 'Tout le camp',
    'nutrition' => 'Nutrition',
    'mouvement' => 'Mouvement',
    'serenite' => 'Sérénité',
];
$publicationTypes = ['Petite victoire', 'Conseil', 'Discussion'];
$publicationStatuses = [
    'published' => 'Publiée',
    'draft' => 'Brouillon',
    'hidden' => 'Masquée',
];
$publicationImages = [
    '' => 'Aucune illustration',
    'nutrition' => 'Nutrition',
    'endurance' => 'Endurance',
    'mobilite' => 'Mobilité',
    'force' => 'Force',
    'focus' => 'Sérénité et concentration',
];
$reportReasons = [
    'spam' => 'Spam ou publicité',
    'inapproprie' => 'Contenu inapproprié',
    'dangereux' => 'Conseil potentiellement dangereux',
    'autre' => 'Autre motif',
];
$reportStatuses = [
    'pending' => 'À traiter',
    'reviewing' => 'En cours',
    'resolved' => 'Traité',
    'dismissed' => 'Classé sans suite',
];
$reportDecisions = [
    'keep' => 'Publication conservée',
    'hide' => 'Publication masquée',
    'delete' => 'Publication supprimée',
];
