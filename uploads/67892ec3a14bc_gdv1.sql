-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 16 jan. 2025 à 16:34
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gdv1`
--

-- --------------------------------------------------------

--
-- Structure de la table `application`
--

CREATE TABLE `application` (
  `idapplication` int(11) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `nomapplication` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `nomresponsable` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `application`
--

INSERT INTO `application` (`idapplication`, `iduser`, `nomapplication`, `description`, `nomresponsable`) VALUES
(4, 5, '1 ere application ', 'tohjgrfdsoi,v', 'oussema lwess');

-- --------------------------------------------------------

--
-- Structure de la table `folder`
--

CREATE TABLE `folder` (
  `idfolderp` int(11) NOT NULL,
  `idfolder` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `dateupload` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `folder`
--

INSERT INTO `folder` (`idfolderp`, `idfolder`, `filename`, `filepath`, `dateupload`) VALUES
(9, NULL, '678068b197292_67802221f3f38_Binary Admin Free Website Template - Free-CSS.com (1).zip', 'uploads/6781491fcca69_678068b197292_67802221f3f38_Binary Admin Free Website Template - Free-CSS.com (1).zip', '2025-01-10 17:21:51');

-- --------------------------------------------------------

--
-- Structure de la table `misajour`
--

CREATE TABLE `misajour` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `misajour`
--

INSERT INTO `misajour` (`id`, `date`, `nom`) VALUES
(4, '2025-01-10 17:21:51', 'test@test');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `iduser` int(11) NOT NULL,
  `nep` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`iduser`, `nep`, `email`, `mdp`, `role`) VALUES
(5, 'test', 'test@testt', '$2y$10$w8zCy4LX8tdkFHsbf1YVuOcfp1X98IlH6C15/a80lEoVOlJ/fg67u', 0),
(6, 'maha banneni', 'oussemalw1ess@gmail.com', '$2y$10$QWQrtwF0goFegUskllzkS.3QnVP40aBjWbIzycZULwhMkDG6qmm9u', 0),
(7, 'elpatron', 'elpatron@gmail.com', 'oussama01', 0),
(8, 'patron', 'patron@gmail.com', '$2y$10$bizbl/tbIwsrNLrQoxMH3ePvdnwEMctD4WuFibWkAVzxZ7U4JMa.G', 1);

-- --------------------------------------------------------

--
-- Structure de la table `valid`
--

CREATE TABLE `valid` (
  `idvalid` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `idversion` int(11) DEFAULT NULL,
  `idapplication` int(11) DEFAULT NULL,
  `estvalid` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `version`
--

CREATE TABLE `version` (
  `idversion` int(11) NOT NULL,
  `idapplication` int(11) DEFAULT NULL,
  `version` varchar(50) NOT NULL,
  `idfolderp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `version`
--

INSERT INTO `version` (`idversion`, `idapplication`, `version`, `idfolderp`) VALUES
(9, 4, 'v1.1', 9);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`idapplication`),
  ADD KEY `iduser` (`iduser`);

--
-- Index pour la table `folder`
--
ALTER TABLE `folder`
  ADD PRIMARY KEY (`idfolderp`),
  ADD KEY `idfolder` (`idfolder`);

--
-- Index pour la table `misajour`
--
ALTER TABLE `misajour`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`iduser`);

--
-- Index pour la table `valid`
--
ALTER TABLE `valid`
  ADD PRIMARY KEY (`idvalid`),
  ADD KEY `idversion` (`idversion`),
  ADD KEY `idapplication` (`idapplication`);

--
-- Index pour la table `version`
--
ALTER TABLE `version`
  ADD PRIMARY KEY (`idversion`),
  ADD KEY `idapplication` (`idapplication`),
  ADD KEY `idfolderp` (`idfolderp`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `application`
--
ALTER TABLE `application`
  MODIFY `idapplication` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `folder`
--
ALTER TABLE `folder`
  MODIFY `idfolderp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `misajour`
--
ALTER TABLE `misajour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `valid`
--
ALTER TABLE `valid`
  MODIFY `idvalid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `version`
--
ALTER TABLE `version`
  MODIFY `idversion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `application_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`);

--
-- Contraintes pour la table `valid`
--
ALTER TABLE `valid`
  ADD CONSTRAINT `valid_ibfk_1` FOREIGN KEY (`idversion`) REFERENCES `version` (`idversion`),
  ADD CONSTRAINT `valid_ibfk_2` FOREIGN KEY (`idapplication`) REFERENCES `application` (`idapplication`);

--
-- Contraintes pour la table `version`
--
ALTER TABLE `version`
  ADD CONSTRAINT `version_ibfk_1` FOREIGN KEY (`idapplication`) REFERENCES `application` (`idapplication`),
  ADD CONSTRAINT `version_ibfk_2` FOREIGN KEY (`idfolderp`) REFERENCES `folder` (`idfolderp`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
