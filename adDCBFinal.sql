-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : lun. 07 août 2023 à 21:18
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `adDCBFinal`
--

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20230627200956', '2023-06-27 20:10:04', 166),
('DoctrineMigrations\\Version20230627204626', '2023-06-27 20:46:30', 44),
('DoctrineMigrations\\Version20230629104904', '2023-06-29 10:49:24', 62),
('DoctrineMigrations\\Version20230629120723', '2023-06-29 12:07:29', 64),
('DoctrineMigrations\\Version20230630002518', '2023-06-30 00:25:51', 46);

-- --------------------------------------------------------

--
-- Structure de la table `employeur`
--

CREATE TABLE `employeur` (
  `id` int(11) NOT NULL,
  `entreprise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `employeur`
--

INSERT INTO `employeur` (`id`, `entreprise`, `adresse`) VALUES
(4, 'ESIEE IT', '3 Rue de Vendôme'),
(5, 'TEST', '1 ALLEE DES PLATANES');

-- --------------------------------------------------------

--
-- Structure de la table `employeur_formation`
--

CREATE TABLE `employeur_formation` (
  `employeur_id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formateur`
--

CREATE TABLE `formateur` (
  `id` int(11) NOT NULL,
  `entreprise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formateur`
--

INSERT INTO `formateur` (`id`, `entreprise`) VALUES
(1, 'ESIEE-IT');

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

CREATE TABLE `formation` (
  `id` int(11) NOT NULL,
  `formateur_id` int(11) DEFAULT NULL,
  `nom_formation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duree_formation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domaine_formation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien_formation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_formation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_formation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut_formation` datetime DEFAULT NULL,
  `date_fin_formation` datetime DEFAULT NULL,
  `modalite_formation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formation`
--

INSERT INTO `formation` (`id`, `formateur_id`, `nom_formation`, `duree_formation`, `domaine_formation`, `lien_formation`, `adresse_formation`, `pdf_formation`, `date_debut_formation`, `date_fin_formation`, `modalite_formation`) VALUES
(1, 1, 'ILMSI', '2 Semaines', 'Informatique', 'lienzoomformation.fr', 'Rue de Courbetin', NULL, '2023-06-30 09:00:00', '2023-07-07 09:00:00', 'Présentiel'),
(2, 1, 'Big Data', '2 Semaines', 'Informatique', 'Zoom.fr', 'Rue de Courbetin', NULL, '2023-06-29 10:00:00', '2023-06-30 20:00:00', 'Mixte'),
(3, 1, 'CYBER', '1 MOIS', 'SECURITE', 'lien.fr', 'Rue de Courbetin', NULL, '2023-06-30 15:41:00', '2023-07-23 15:41:00', 'Mixte');

-- --------------------------------------------------------

--
-- Structure de la table `formation_stagiaire`
--

CREATE TABLE `formation_stagiaire` (
  `formation_id` int(11) NOT NULL,
  `stagiaire_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formation_stagiaire`
--

INSERT INTO `formation_stagiaire` (`formation_id`, `stagiaire_id`) VALUES
(1, 3),
(1, 6),
(1, 7),
(3, 3),
(3, 7);

-- --------------------------------------------------------

--
-- Structure de la table `gestionnaire`
--

CREATE TABLE `gestionnaire` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stagiaire`
--

CREATE TABLE `stagiaire` (
  `id` int(11) NOT NULL,
  `employeur_id` int(11) DEFAULT NULL,
  `civilite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fonction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entreprise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stagiaire`
--

INSERT INTO `stagiaire` (`id`, `employeur_id`, `civilite`, `fonction`, `entreprise`) VALUES
(3, NULL, 'Madame', 'Etudiante', 'ESIEE IT'),
(6, NULL, 'Madame', 'Etudiante', 'ESIEE IT'),
(7, NULL, 'Madame', 'CEO', 'ESIEE IT');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `nom`, `prenom`, `telephone`, `type`) VALUES
(1, 'seoulouaimeedesiree@hotmail.fr', '[]', 'passwordacrypter', 'SEOULOU', 'AIMEE DESIREE LYDE', '09876543', 'formateur'),
(3, 'seoulouaimeedesiree@hotmail.com', '[]', 'ChocolatN0ir', 'Gims', 'AIMEE DESIREE LYDE', '+33769800802', 'stagiaire'),
(4, 'aimee.seoulou19@inphb.ci', '[]', 'Testeur', 'SEOULOU', 'AIMEE DESIREE LYDE', '234567998', 'employeur'),
(5, 'lyde.test@gmail.com', '[]', 'Testeur', 'Lyde', 'Test', '0101010101', 'employeur'),
(6, 'seoulouaimeedesiree@hotmail.ci', '[]', 'ChocolatN0ir', 'JOHN', 'DOH', '+33769800802', 'stagiaire'),
(7, 'cecile@gmail.com', '[]', 'root', 'YIN', 'Cecile', NULL, 'stagiaire');

-- --------------------------------------------------------

--
-- Structure de la table `user_one`
--

CREATE TABLE `user_one` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `employeur`
--
ALTER TABLE `employeur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `employeur_formation`
--
ALTER TABLE `employeur_formation`
  ADD PRIMARY KEY (`employeur_id`,`formation_id`),
  ADD KEY `IDX_D41B1F5A5D7C53EC` (`employeur_id`),
  ADD KEY `IDX_D41B1F5A5200282E` (`formation_id`);

--
-- Index pour la table `formateur`
--
ALTER TABLE `formateur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `formation`
--
ALTER TABLE `formation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_404021BF155D8F51` (`formateur_id`);

--
-- Index pour la table `formation_stagiaire`
--
ALTER TABLE `formation_stagiaire`
  ADD PRIMARY KEY (`formation_id`,`stagiaire_id`),
  ADD KEY `IDX_851FA7EC5200282E` (`formation_id`),
  ADD KEY `IDX_851FA7ECBBA93DD6` (`stagiaire_id`);

--
-- Index pour la table `gestionnaire`
--
ALTER TABLE `gestionnaire`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `stagiaire`
--
ALTER TABLE `stagiaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4F62F7315D7C53EC` (`employeur_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- Index pour la table `user_one`
--
ALTER TABLE `user_one`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `formation`
--
ALTER TABLE `formation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `user_one`
--
ALTER TABLE `user_one`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `employeur`
--
ALTER TABLE `employeur`
  ADD CONSTRAINT `FK_8747E1C7BF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `employeur_formation`
--
ALTER TABLE `employeur_formation`
  ADD CONSTRAINT `FK_D41B1F5A5200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D41B1F5A5D7C53EC` FOREIGN KEY (`employeur_id`) REFERENCES `employeur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formateur`
--
ALTER TABLE `formateur`
  ADD CONSTRAINT `FK_ED767E4FBF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formation`
--
ALTER TABLE `formation`
  ADD CONSTRAINT `FK_404021BF155D8F51` FOREIGN KEY (`formateur_id`) REFERENCES `formateur` (`id`);

--
-- Contraintes pour la table `formation_stagiaire`
--
ALTER TABLE `formation_stagiaire`
  ADD CONSTRAINT `FK_851FA7EC5200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_851FA7ECBBA93DD6` FOREIGN KEY (`stagiaire_id`) REFERENCES `stagiaire` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `gestionnaire`
--
ALTER TABLE `gestionnaire`
  ADD CONSTRAINT `FK_F4461B20BF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stagiaire`
--
ALTER TABLE `stagiaire`
  ADD CONSTRAINT `FK_4F62F7315D7C53EC` FOREIGN KEY (`employeur_id`) REFERENCES `employeur` (`id`),
  ADD CONSTRAINT `FK_4F62F731BF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
