-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 15 Septembre 2013 à 11:50
-- Version du serveur: 5.5.20-log
-- Version de PHP: 5.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `byfr_bookino`
--
CREATE DATABASE IF NOT EXISTS `byfr_bookino` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `byfr_bookino`;

-- --------------------------------------------------------

--
-- Structure de la table `commentaires_p`
--

CREATE TABLE IF NOT EXISTS `commentaires_p` (
  `idCommentaire_p` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idParagraphe` int(11) NOT NULL,
  `commentaire` text,
  `date_cree` datetime DEFAULT NULL,
  PRIMARY KEY (`idCommentaire_p`),
  KEY `fk_Commentaires_p_Paragraphes1_idx` (`idParagraphe`),
  KEY `fk_Commentaires_p_Utilisateurs1_idx` (`idUtilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `commentaires_r`
--

CREATE TABLE IF NOT EXISTS `commentaires_r` (
  `idCommentaire_r` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idRoman` int(11) NOT NULL,
  `commentaire` text,
  `date_cree` datetime DEFAULT NULL,
  PRIMARY KEY (`idCommentaire_r`),
  KEY `fk_Commentaires_p_Utilisateurs1_idx` (`idUtilisateur`),
  KEY `fk_Commentaires_r_Romans1_idx` (`idRoman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `connexions`
--

CREATE TABLE IF NOT EXISTS `connexions` (
  `idConnexion` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `cleConnexion` varchar(96) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`idConnexion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Contenu de la table `connexions`
--

INSERT INTO `connexions` (`idConnexion`, `idUtilisateur`, `cleConnexion`, `date`) VALUES
(12, 98, 'd3385282383a938e236d3198bbb72076b8356ed56008908619600a8ba7fc77d85952d5f0dcda51a8932681b5f66c1e80', '2013-09-11 11:42:45'),
(13, 98, 'd3385282383a938e236d3198bbb72076b8356ed56008908619600a8ba7fc77d85fc066bfca2e2f4a53054f7caa9186a4', '2013-09-11 16:23:45'),
(14, 98, 'd3385282383a938e236d3198bbb72076b8356ed56008908619600a8ba7fc77d879c2f8f6556d20b43c79fb634643ff40', '2013-09-11 16:36:31');

-- --------------------------------------------------------

--
-- Structure de la table `idees`
--

CREATE TABLE IF NOT EXISTS `idees` (
  `idIdee` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idRoman` int(11) NOT NULL,
  `titre` varchar(30) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  `date_cree` datetime DEFAULT NULL,
  `statut` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idIdee`),
  KEY `fk_Idees_Utilisateurs1_idx` (`idUtilisateur`),
  KEY `fk_Idees_Romans1_idx` (`idRoman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `idMessage` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` varchar(45) DEFAULT NULL,
  `idUtilisateur_envoi` int(11) NOT NULL,
  `idUtilisateur_recoi` int(11) NOT NULL,
  `date_envoi` datetime DEFAULT NULL,
  `lu` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`idMessage`),
  KEY `fk_Messages_Utilisateurs2_idx` (`idUtilisateur_envoi`),
  KEY `fk_Messages_Utilisateurs1_idx` (`idUtilisateur_recoi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `notes_i`
--

CREATE TABLE IF NOT EXISTS `notes_i` (
  `idNote_i` int(11) NOT NULL AUTO_INCREMENT,
  `idIdee` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `note` varchar(3) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`idNote_i`),
  KEY `fk_Notes_i_Idees1_idx` (`idIdee`),
  KEY `fk_Notes_i_Utilisateurs1_idx` (`idUtilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `notes_p`
--

CREATE TABLE IF NOT EXISTS `notes_p` (
  `idNote_p` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idParagraphe` int(11) NOT NULL,
  `note` varchar(3) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`idNote_p`),
  KEY `fk_Notes_Utilisateurs1_idx` (`idUtilisateur`),
  KEY `fk_Notes_Paragraphes1_idx` (`idParagraphe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `notes_r`
--

CREATE TABLE IF NOT EXISTS `notes_r` (
  `idNote_r` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idRoman` int(11) NOT NULL,
  `note` varchar(3) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`idNote_r`),
  KEY `fk_Notes_Utilisateurs1_idx` (`idUtilisateur`),
  KEY `fk_Notes_r_Romans1_idx` (`idRoman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `paragraphes`
--

CREATE TABLE IF NOT EXISTS `paragraphes` (
  `idParagraphe` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idRoman` int(11) NOT NULL,
  `contenu` text,
  `date_cree` datetime DEFAULT NULL,
  PRIMARY KEY (`idParagraphe`),
  KEY `fk_Paragraphes_Romans1_idx` (`idRoman`),
  KEY `fk_Paragraphes_Utilisateurs1_idx` (`idUtilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `romans`
--

CREATE TABLE IF NOT EXISTS `romans` (
  `idRoman` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `description` text,
  `date_cree` datetime DEFAULT NULL,
  PRIMARY KEY (`idRoman`),
  KEY `fk_Romans_Utilisateurs_idx` (`idUtilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `idUtilisateur` int(11) NOT NULL AUTO_INCREMENT,
  `cleUtilisateur` varchar(64) NOT NULL,
  `pseudo` varchar(30) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `motdepasse` varchar(32) DEFAULT NULL,
  `motdepasse_nouveau` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `date_inscription` datetime DEFAULT NULL,
  `enligne` varchar(3) DEFAULT NULL,
  `date_activite` datetime DEFAULT NULL,
  `poste` varchar(20) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `etat` varchar(20) NOT NULL,
  PRIMARY KEY (`idUtilisateur`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=99 ;

--
-- Contenu de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`idUtilisateur`, `cleUtilisateur`, `pseudo`, `email`, `motdepasse`, `motdepasse_nouveau`, `date_inscription`, `enligne`, `date_activite`, `poste`, `points`, `etat`) VALUES
(98, 'd3385282383a938e236d3198bbb72076b8356ed56008908619600a8ba7fc77d8', 'ncoden', 'contact@ncoden.fr', 'c4eb226a26c1d02bc3592ca5ce259e50', '2013-09-09 20:30:27', '2013-09-01 14:56:36', NULL, NULL, NULL, 0, 'nouveau');

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `commentaires_p`
--
ALTER TABLE `commentaires_p`
  ADD CONSTRAINT `fk_Commentaires_p_Paragraphes1` FOREIGN KEY (`idParagraphe`) REFERENCES `paragraphes` (`idParagraphe`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Commentaires_p_Utilisateurs1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `commentaires_r`
--
ALTER TABLE `commentaires_r`
  ADD CONSTRAINT `fk_Commentaires_p_Utilisateurs10` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Commentaires_r_Romans1` FOREIGN KEY (`idRoman`) REFERENCES `romans` (`idRoman`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `idees`
--
ALTER TABLE `idees`
  ADD CONSTRAINT `fk_Idees_Romans1` FOREIGN KEY (`idRoman`) REFERENCES `romans` (`idRoman`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Idees_Utilisateurs1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_Messages_Utilisateurs1` FOREIGN KEY (`idUtilisateur_recoi`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Messages_Utilisateurs2` FOREIGN KEY (`idUtilisateur_envoi`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `notes_i`
--
ALTER TABLE `notes_i`
  ADD CONSTRAINT `fk_Notes_i_Idees1` FOREIGN KEY (`idIdee`) REFERENCES `idees` (`idIdee`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Notes_i_Utilisateurs1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `notes_p`
--
ALTER TABLE `notes_p`
  ADD CONSTRAINT `fk_Notes_Paragraphes1` FOREIGN KEY (`idParagraphe`) REFERENCES `paragraphes` (`idParagraphe`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Notes_Utilisateurs1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `notes_r`
--
ALTER TABLE `notes_r`
  ADD CONSTRAINT `fk_Notes_r_Romans1` FOREIGN KEY (`idRoman`) REFERENCES `romans` (`idRoman`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Notes_Utilisateurs10` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `paragraphes`
--
ALTER TABLE `paragraphes`
  ADD CONSTRAINT `fk_Paragraphes_Romans1` FOREIGN KEY (`idRoman`) REFERENCES `romans` (`idRoman`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Paragraphes_Utilisateurs1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `romans`
--
ALTER TABLE `romans`
  ADD CONSTRAINT `fk_Romans_Utilisateurs` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
