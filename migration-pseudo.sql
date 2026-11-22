-- Eya : colonne nécessaire à l’inscription ; appliquer une seule fois à une base existante.
ALTER TABLE utilisateur ADD COLUMN pseudo VARCHAR(50) NULL AFTER prenom,
    ADD CONSTRAINT uq_utilisateur_pseudo UNIQUE (pseudo);
