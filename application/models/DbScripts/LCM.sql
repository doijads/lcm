-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 09, 2013 at 03:55 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lcm`
--

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE IF NOT EXISTS `cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lawyer_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `date_of_allotment` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `closing_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lawyer_id` (`lawyer_id`),
  KEY `client_id` (`client_id`),
  KEY `closed_by` (`closed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `case_documents`
--

CREATE TABLE IF NOT EXISTS `case_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `document_name` varchar(25) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_on` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `case_history`
--

CREATE TABLE IF NOT EXISTS `case_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `hearing_date` datetime DEFAULT NULL,
  `next_hearing_date` datetime DEFAULT NULL,
  `judge_name` varchar(25) NOT NULL,
  `content` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `case_transactions`
--

CREATE TABLE IF NOT EXISTS `case_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `transaction_type_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `submission_date` datetime NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `transaction_details` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`),
  KEY `submitted_by` (`submitted_by`),
  KEY `approved_by` (`approved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent_from` int(11) NOT NULL,
  `sent_to` int(11) NOT NULL,
  `message` varchar(50) NOT NULL,
  `posted_datetime` datetime NOT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sent_from` (`sent_from`),
  KEY `sent_to` (`sent_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `user_type` int(11) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `password` varchar(256) DEFAULT NULL,
  `street_line` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `home_phone` varchar(20) DEFAULT NULL,
  `work_phone` varchar(20) DEFAULT NULL,
  `fax_number` varchar(20) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `user_type`, `email`, `password`, `street_line`, `city`, `state`, `postal_code`, `country`, `home_phone`, `work_phone`, `fax_number`, `mobile_number`, `created_on`, `created_by`) VALUES
(2, 'hey', 1, 'sachindoijad@gmail.com', NULL, 'pune', 'pune', 'maharashtra', '45656', 'india', '6565', '5656556', '565665', '5655656', '0000-00-00 00:00:00', 0),
(3, 'sexy', 1, 'sachindoijad@gmail.com', '15285722f9def45c091725aee9c387cb', 'pune', 'pune', 'maharashtra', '45656', 'india', '6565', '5656556', '565665', '5655656', '2013-09-08 00:00:00', 2),
(4, 'Sumit', 1, 'sumit@gmail.com', NULL, '15454', 'pune', 'maharashtra', '45454', 'india', '78878', '487985', '45454', '8569859', '2013-09-08 00:00:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE IF NOT EXISTS `user_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(20) DEFAULT NULL,
  `company_profile` varchar(50) DEFAULT NULL,
  `designation` varchar(25) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `role_description` varchar(255) DEFAULT NULL,
  `area_of_practice` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `IFSC_code` varchar(20) DEFAULT NULL,
  `service_tax_number` varchar(20) DEFAULT NULL,
  `pan_card_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `company_name`, `company_profile`, `designation`, `role`, `role_description`, `area_of_practice`, `bank_account_number`, `IFSC_code`, `service_tax_number`, `pan_card_number`) VALUES
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, '655', NULL, '', '2323'),
(3, 3, NULL, NULL, NULL, NULL, NULL, NULL, '5454545', NULL, '455656', '546565'),
(4, 4, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '4545');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cases_ibfk_3` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD CONSTRAINT `case_documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `case_history`
--
ALTER TABLE `case_history`
  ADD CONSTRAINT `case_history_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `case_transactions`
--
ALTER TABLE `case_transactions`
  ADD CONSTRAINT `case_transactions_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `case_transactions_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `case_transactions_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`sent_from`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`sent_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_details`
--
ALTER TABLE `user_details`
  ADD CONSTRAINT `user_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
