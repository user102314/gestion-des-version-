
CREATE TABLE `application` (
  `idapplication` int(11) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `nomapplication` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `nomresponsable` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `folder` (
  `idfolderp` int(11) NOT NULL,
  `idfolder` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `dateupload` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `misajour` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `user` (
  `iduser` int(11) NOT NULL,
  `nep` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` int(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `date_token` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `valid` (
  `idvalid` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `idversion` int(11) DEFAULT NULL,
  `idapplication` int(11) DEFAULT NULL,
  `estvalid` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `version` (
  `idversion` int(11) NOT NULL,
  `idapplication` int(11) DEFAULT NULL,
  `version` varchar(50) NOT NULL,
  `idfolderp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
