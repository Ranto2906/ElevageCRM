

-- Insertion des données de test par défaut
INSERT INTO department (name)
VALUES ('Finance'),
       ('Administration'),
       ('Technique'),
       ('Marketing'),
       ('Office');

INSERT INTO priority (name)
VALUES  ('Peu important'),
        ('Normal'),
        ('Urgent');

INSERT INTO category (name)
VALUES  ('Recette'),
        ('Depense');
      

INSERT INTO user (name, password, department_id)
VALUES ('finance', 'finance', 1),
       ('administration', 'administration', 2),
       ('technique', 'technique', 3),
       ('marketing', 'marketing', 4),
       ('office', 'office', 5);

INSERT INTO type (name)
VALUES ('achat'),
       ('vente'),
       ('don'),
       ('CRM');


-- Insertion des types de cheveux simplifiés
INSERT INTO typecheveux (nom) VALUES
('Lisse'), ('Crepu'), ('Ondule'), ('Frise'), ('Raide'),
('Boucle'), ('Afro'), ('Dreadlocks'), ('Tresse'), ('Naturel');

-- Insertion des 25 clients
INSERT INTO client (nom, prenom, age, typecheveux_id, email, telephone, preferences) VALUES
('Dubois', 'Marc', 32, 1, 'marc.dubois@mail.com', '0611223344', 'Prefere coupe classique'),
('Lambert', 'Alice', 27, 2, 'alice.lambert@mail.com', '0622334455', 'Cheveux tres secs'),
('Royer', 'Thomas', 45, 3, 'thomas.royer@mail.com', '0633445566', 'Aime les produits bio'),
('Fontaine', 'Lea', 23, 4, 'lea.fontaine@mail.com', '0644556677', 'Change souvent de style'),
('Chevalier', 'Paul', 38, 5, 'paul.chevalier@mail.com', '0655667788', 'Entretien barbe et cheveux'),
('Berger', 'Julie', 29, 6, 'julie.berger@mail.com', '0666778899', 'Boucles a definir'),
('Perrin', 'Mohamed', 34, 7, 'mohamed.perrin@mail.com', '0677889900', 'Coupe afro mensuelle'),
('Garnier', 'Sarah', 25, 8, 'sarah.garnier@mail.com', '0688990011', 'Dreads depuis 3 ans'),
('Clement', 'Antoine', 41, 9, 'antoine.clement@mail.com', '0699001122', 'Tresses professionnelles'),
('Lefevre', 'Zoe', 30, 10, 'zoe.lefevre@mail.com', '0600112233', 'Pas de produits chimiques'),
('Martinez', 'Louis', 28, 1, 'louis.martinez@mail.com', '0611223345', 'Coupe rapide hebdomadaire'),
('Blanc', 'Camille', 33, 2, 'camille.blanc@mail.com', '0622334456', 'Besoin d hydratation intense'),
('Dufour', 'Nathan', 26, 3, 'nathan.dufour@mail.com', '0633445567', 'Produits sans silicone'),
('Brun', 'Amandine', 39, 4, 'amandine.brun@mail.com', '0644556678', 'Style boheme chic'),
('Mercier', 'Alex', 24, 5, 'alex.mercier@mail.com', '0655667789', 'Coupe militaire'),
('Fournier', 'Clara', 37, 6, 'clara.fournier@mail.com', '0666778890', 'Soins capillaires reguliers'),
('Masson', 'Yanis', 31, 7, 'yanis.masson@mail.com', '0677889901', 'Coupe afro toutes les 3 semaines'),
('Girard', 'Lina', 22, 8, 'lina.girard@mail.com', '0688990012', 'Nouveaux dreads'),
('Bonnet', 'Hugo', 40, 9, 'hugo.bonnet@mail.com', '0699001123', 'Tresses serrees'),
('Francois', 'Lucie', 35, 10, 'lucie.francois@mail.com', '0600112234', 'Shampoing seulement'),
('Legrand', 'Maxime', 29, 1, 'maxime.legrand@mail.com', '0611223346', 'Style professionnel'),
('Roussel', 'Eva', 36, 2, 'eva.roussel@mail.com', '0622334457', 'Proteines capillaires'),
('Nicolas', 'Tom', 27, 3, 'tom.nicolas@mail.com', '0633445568', 'Pas de chaleur'),
('Henry', 'Manon', 42, 4, 'manon.henry@mail.com', '0644556679', 'Style retro'),
('Petit', 'Leo', 31, 5, 'leo.petit@mail.com', '0655667790', 'Barbe et cheveux');


--Insertion par defaut exercise 
INSERT INTO exercise (start_date, nb_period, start_balance) 
VALUES ('2025-01-01', 2, 10000000.00);
INSERT INTO exercise (start_date, nb_period, start_balance) 
VALUES ('2026-01-01', 2, 10000000.00);

--Initialisation des transaction pour le CRM
INSERT INTO budget_element(department_id, category_id, type_id, description) VALUES (4, 2, 4, "CRM");

INSERT INTO transaction(nature, exercise_id, budget_element_id, period_num, amount, status, priority_id)
VALUES (1, 1, 1, 1, 0, 1, 1),
       (1, 1, 1, 2, 0, 1, 1),
       (2, 1, 1, 1, 0, 1, 1),
       (2, 1, 1, 2, 0, 1, 1),
       (1, 2, 1, 1, 0, 1, 1),
       (1, 2, 1, 2, 0, 1, 1),
       (2, 2, 1, 1, 0, 1, 1),
       (2, 2, 1, 2, 0, 1, 1);


