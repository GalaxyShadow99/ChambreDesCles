INSERT INTO client (nom, prenom, avis) VALUES
('Dupont', 'Jean', 'Très bon séjour, je recommande !'),
('Martin', 'Sophie', 'Accueil chaleureux et chambre confortable.'),
('Durand', 'Pierre', 'Séjour agréable, mais le petit-déjeuner pourrait être amélioré.'),
('Lefevre', 'Marie', 'Superbe expérience, nous reviendrons avec plaisir !'),
('Moreau', 'Luc', 'Chambre propre et bien équipée, mais un peu bruyant la nuit.'),
('Girard', 'Claire', 'Hôtes très sympathiques et disponibles.'),
('Rousseau', 'Antoine', 'Séjour parfait, nous avons adoré la région.'),
('Blanc', 'Isabelle', 'Chambre spacieuse et confortable, mais le wifi était lent.'),
('Faure', 'Julien', 'Très bon rapport qualité-prix, nous recommandons cet établissement.'),
('Garnier', 'Camille', 'Séjour agréable, mais la salle de bain pourrait être rénovée.');

INSERT INTO reservation (id_client, date_debut, date_fin, prix, valide, plateforme) VALUES
(1, '2023-07-01', '2023-07-05', 400.00, TRUE, 'booking'),
(2, '2023-08-10', '2023-08-15', 500.00, FALSE, 'airbnb'),
(3, '2023-09-20', '2023-09-25', 450.00, TRUE, 'sans plateforme'),
(4, '2023-10-05', '2023-10-10', 600.00, FALSE, 'booking'),
(5, '2023-11-15', '2023-11-20', 550.00, TRUE, 'airbnb'),
(6, '2023-12-01', '2023-12-05', 700.00, FALSE, 'sans plateforme'),
(7, '2024-01-10', '2024-01-15', 650.00, TRUE, 'booking'),
(8, '2024-02-20', '2024-02-25', 800.00, FALSE, 'airbnb'),
(9, '2024-03-05', '2024-03-10', 750.00, TRUE, 'sans plateforme'),
(10, '2024-04-15', '2024-04-20', 900.00, FALSE, 'booking');
