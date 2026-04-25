-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 25 avr. 2026 à 13:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `moneykids`
--

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE `compte` (
  `id` int(11) NOT NULL,
  `solde` decimal(10,2) DEFAULT 0.00,
  `montant_argent_poche` decimal(10,2) DEFAULT 0.00,
  `frequence` enum('hebdo','mensuel') NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `compte`
--

INSERT INTO `compte` (`id`, `solde`, `montant_argent_poche`, `frequence`, `user_id`) VALUES
(4, 0.00, 0.00, 'mensuel', 6),
(15, 215.00, 250.00, 'mensuel', 18);

-- --------------------------------------------------------

--
-- Structure de la table `objectif_epargne`
--

CREATE TABLE `objectif_epargne` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `montant_cible` decimal(10,2) NOT NULL,
  `montant_actuel` decimal(10,2) DEFAULT 0.00,
  `date_limite` date DEFAULT NULL,
  `compte_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `objectif_epargne`
--

INSERT INTO `objectif_epargne` (`id`, `nom`, `montant_cible`, `montant_actuel`, `date_limite`, `compte_id`) VALUES
(7, 'jeu video', 35.00, 60.00, '2026-04-25', 15);

-- --------------------------------------------------------

--
-- Structure de la table `recompense`
--

CREATE TABLE `recompense` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_obtention` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recompense`
--

INSERT INTO `recompense` (`id`, `user_id`, `badge`, `description`, `date_obtention`) VALUES
(1, 18, 'Super Epargnant', 'Objectif « jeu video » atteint !', '2026-04-25 10:57:28');

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

CREATE TABLE `transaction` (
  `id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'approved',
  `motif_refus` varchar(255) DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_reponse` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_transaction` timestamp NOT NULL DEFAULT current_timestamp(),
  `compte_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transaction`
--

INSERT INTO `transaction` (`id`, `montant`, `type`, `status`, `motif_refus`, `date_soumission`, `date_reponse`, `description`, `date_transaction`, `compte_id`, `parent_id`) VALUES
(16, 49.98, 'credit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Argent de poche — mensuel', '2026-04-22 18:46:50', 15, NULL),
(17, 50.00, 'credit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Argent de poche — hebdo', '2026-04-22 18:47:28', 15, NULL),
(18, 250.00, 'credit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Argent de poche — mensuel', '2026-04-22 19:00:46', 15, NULL),
(20, 5.00, 'debit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Education — stylo', '2026-04-25 10:56:10', 15, NULL),
(21, 5.00, 'debit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Epargne — jeu video', '2026-04-25 10:57:03', 15, NULL),
(22, 25.00, 'debit', 'approved', NULL, '2026-04-25 12:15:47', NULL, 'Epargne — jeu video', '2026-04-25 10:57:28', 15, NULL),
(23, 5.00, 'debit', 'declined', 'Non specifie', '2026-04-25 12:26:30', '2026-04-25 12:26:52', 'Education - stylo', '2026-04-25 11:26:30', 15, 6);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('parent','enfant') NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `parent_id`, `age`) VALUES
(6, 'rekik', 'ons', 'noussarekik20@gmail.com', '$2y$10$l4FHbYQnaWy1AkrES8XWO.WCdLP5F8yXP1Wd5.E2XRmGeIiCw1C1q', 'parent', NULL, NULL),
(11, 'rk', 'mohamed', 'mohamed@gmail.com', '$2y$10$oFhKY.1.fpj238UX78NgR.OfSBdSPxooPLEMTH6MFN91gcqDQaoCy', 'enfant', 6, 10),
(18, 'rk', 'hiba', 'hiba@gmail.com', '$2y$10$zPPRmM3nWT7gyXFt4XqyY.q4LYGh5ZgDLK52SWFqrkMvm9Py88qk.', 'enfant', 6, 9);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `compte`
--
ALTER TABLE `compte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Index pour la table `objectif_epargne`
--
ALTER TABLE `objectif_epargne`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compte_id` (`compte_id`);

--
-- Index pour la table `recompense`
--
ALTER TABLE `recompense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compte_id` (`compte_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `parent_id` (`parent_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `compte`
--
ALTER TABLE `compte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `objectif_epargne`
--
ALTER TABLE `objectif_epargne`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `recompense`
--
ALTER TABLE `recompense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `compte`
--
ALTER TABLE `compte`
  ADD CONSTRAINT `compte_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `objectif_epargne`
--
ALTER TABLE `objectif_epargne`
  ADD CONSTRAINT `objectif_epargne_ibfk_1` FOREIGN KEY (`compte_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `recompense`
--
ALTER TABLE `recompense`
  ADD CONSTRAINT `recompense_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`compte_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
