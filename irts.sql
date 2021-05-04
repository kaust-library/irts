-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: May 04, 2021 at 11:03 AM
-- Server version: 5.7.33-0ubuntu0.16.04.1-log
-- PHP Version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `irts-publicCode-test`
--
CREATE DATABASE IF NOT EXISTS `irts-publicCode-test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `irts-publicCode-test`;

-- --------------------------------------------------------

--
-- Table structure for table `mappings`
--

CREATE TABLE `mappings` (
  `mappingID` int(11) NOT NULL,
  `source` varchar(30) NOT NULL,
  `parentFieldInSource` varchar(30) NOT NULL,
  `sourceField` varchar(100) NOT NULL,
  `standardField` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mappings`
--

INSERT INTO `mappings` (`mappingID`, `source`, `parentFieldInSource`, `sourceField`, `standardField`) VALUES
(1, 'arxiv', '', 'title', 'dc.title'),
(2, 'arxiv', '', 'id', 'dc.identifier.arxivid'),
(3, 'arxiv', '', 'published', 'dc.date.issued'),
(4, 'arxiv', '', 'updated', 'dc.date.updated'),
(5, 'arxiv', '', 'summary', 'dc.description.abstract'),
(6, 'arxiv', '', 'author', 'dc.contributor.author'),
(7, 'arxiv', '', 'link', 'dc.relation.url'),
(8, 'arxiv', '', 'category', 'dc.subject'),
(10, 'arxiv', '', 'comment', 'dc.description'),
(11, 'arxiv', 'author', 'affiliation', 'dc.contributor.affiliation'),
(12, 'arxiv', '', 'journal_ref', 'dc.identifier.citation'),
(13, 'arxiv', '', 'doi', 'dc.identifier.doi'),
(14, 'crossref', '', 'crossref.type', 'dc.type'),
(15, 'crossref', '', 'crossref.title', 'dc.title'),
(16, 'crossref', '', 'crossref.publisher', 'dc.publisher'),
(17, 'crossref', '', 'crossref.URL', 'dc.identifier.url'),
(18, 'crossref', '', 'crossref.volume', 'dc.identifier.volume'),
(19, 'crossref', '', 'crossref.page', 'dc.identifier.pages'),
(20, 'crossref', '', 'crossref.DOI', 'dc.identifier.doi'),
(21, 'crossref', '', 'crossref.issue', 'dc.identifier.issue'),
(22, 'crossref', '', 'crossref.container-title', 'dc.identifier.journal'),
(23, 'crossref', '', 'crossref.ISSN', 'dc.identifier.issn'),
(24, 'crossref', '', 'crossref.author.name', 'dc.contributor.author'),
(25, 'crossref', '', 'crossref.author.affiliation.name', 'dc.contributor.affiliation'),
(26, 'crossref', '', 'crossref.author.ORCID', 'dc.identifier.orcid'),
(27, 'crossref', '', 'crossref.abstract', 'dc.description.abstract'),
(28, 'crossref', '', 'crossref.ISBN', 'dc.identifier.isbn'),
(29, 'googlePatents', '', 'DC.description', 'dc.description.abstract'),
(30, 'googlePatents', '', 'citation_patent_application_number', 'dc.identifier.prioritynumber'),
(31, 'googlePatents', '', 'assignee', 'dc.contributor.assignee'),
(32, 'googlePatents', '', 'inventor/author', 'dc.contributor.author'),
(33, 'googlePatents', '', 'title', 'dc.title'),
(34, 'googlePatents', '', 'publication date', 'dc.date.issued'),
(35, 'googlePatents', '', 'filing/creation date', 'dc.date.submitted'),
(36, 'googlePatents', '', 'result link', 'dc.relation.url'),
(37, 'googlePatents', '', 'DC.title', 'dc.title'),
(38, 'arxiv', '', 'primary_category', 'dc.subject.arxiv'),
(39, 'ebird', '', 'ebird.subId', 'ebird.checklist.id'),
(40, 'ebird', '', 'obsId', 'observation.id'),
(41, 'ebird', '', 'subId', 'checklist.id'),
(42, 'ebird', '', 'obsId', 'observation.id'),
(43, 'ebird', '', 'obsId', 'observation.id'),
(44, 'ebird', '', 'subId', 'ebird.checklist.id'),
(45, 'ebird', '', 'obsDt', 'dc.date.observed'),
(46, 'ebird', '', 'ebird.obsTime', 'ebird.observation.time'),
(47, 'ebird', '', 'userDisplayName', 'dwc.occurrence.recordedBy'),
(48, 'ebird', '', 'locId', 'ebird.location.id'),
(49, 'ebird', '', 'observation.id', 'ebird.observation.id'),
(50, 'ebird', '', 'howManyStr', 'dwc.occurrence.individualCount'),
(51, 'ebird', '', 'comments', 'dc.description.note'),
(52, 'ebird', '', 'observation.id', 'ebird.observation.id'),
(53, 'ebird', '', 'obsTime', 'ebird.observation.time'),
(54, 'ebird', '', 'observation.id', 'ebird.observation.id'),
(55, 'ebird', '', 'observation.id', 'obs'),
(56, 'ebird', '', 'observation.id', 'ebird.observation.id'),
(57, 'ebird', '', 'obsId', 'ebird.observation.id'),
(58, 'ebird', '', 'speciesCode', 'ebird.species.code'),
(59, 'ebird', '', 'obsyear', 'kaust.presence.year'),
(60, 'ebird', '', 'obsmonth', 'kaust.presence.month'),
(61, 'ieee', '', 'doi', 'dc.identifier.doi'),
(62, 'ieee', '', 'title', 'dc.title'),
(63, 'ieee', '', 'publication_title', 'dc.identifier.journal'),
(64, 'ieee', '', 'publisher', 'dc.publisher'),
(65, 'ieee', '', 'abstract', 'dc.description.abstract'),
(66, 'ieee', '', 'issn', 'dc.identifier.issn'),
(67, 'ieee', '', 'isbn', 'dc.identifier.isbn'),
(68, 'ieee', '', 'conference_dates', 'dc.conference.date'),
(69, 'ieee', '', 'conference_location', 'dc.conference.location'),
(70, 'crossref', '', 'crossref.link.URL', 'dc.relation.url'),
(71, 'scopus', '', 'scopus.coredata.doi', 'dc.identifier.doi'),
(72, 'scopus', '', 'scopus.coredata.title', 'dc.title'),
(73, 'scopus', '', 'scopus.coredata.subtypeDescription', 'dc.type'),
(74, 'scopus', '', 'scopus.coredata.publicationName', 'dc.identifier.journal'),
(75, 'scopus', '', 'scopus.coredata.publisher', 'dc.publisher'),
(76, 'scopus', '', 'scopus.coredata.coverDate', 'dc.date.issued'),
(77, 'scopus', '', 'scopus.coredata.isbn', 'dc.identifier.isbn'),
(78, 'europePMC', '', 'title', 'dc.title'),
(79, 'europePMC', '', 'abstractText', 'dc.description.abstract'),
(80, 'europePMC', '', 'doi', 'dc.identifier.doi'),
(81, 'europePMC', '', 'journalInfo.journal.title', 'dc.identifier.journal'),
(82, 'europePMC', '', 'journalInfo.journal.issn', 'dc.identifier.issn'),
(83, 'europePMC', '', 'dateOfCreation', 'dc.date.issued'),
(84, 'europePMC', '', 'keywordList.keyword', 'dc.subject'),
(85, 'europePMC', '', 'pubTypeList.pubType', 'dc.type'),
(87, 'europePMC', '', 'pmid', 'dc.identifier.pmid'),
(88, 'europePMC', '', 'pmcid', 'dc.identifier.pmcid'),
(89, 'scopus', '', 'scopus.coredata.eid', 'dc.identifier.eid'),
(90, 'semanticScholar', '', 'doi', 'dc.identifier.doi'),
(91, 'semanticScholar', '', 'abstract', 'dc.description.abstract'),
(92, 'semanticScholar', '', 'arxivId', 'dc.identifier.arxivid'),
(93, 'semanticScholar', '', 'paperId', 'dc.identifier.semanticScholarPaper'),
(94, 'semanticScholar', '', 'title', 'dc.title'),
(95, 'ieee', '', 'publication_year', 'dc.date.issued'),
(96, 'scopus', '', 'scopus.coredata.pageRange', 'dc.identifier.pages'),
(97, 'scopus', '', 'scopus.coredata.issueIdentifier', 'dc.identifier.issue'),
(98, 'datacite', '', 'doi', 'dc.identifier.doi'),
(99, 'datacite', '', 'datacite.titles.title', 'dc.title'),
(100, 'datacite', '', 'url', 'dc.relation.url'),
(101, 'datacite', '', 'publisher', 'dc.publisher'),
(102, 'ncbi', '', 'ncbi.DocumentSummary.Project.ProjectID.ArchiveID.accession', 'dc.identifier.bioproject'),
(103, 'ncbi', '', 'ncbi.DocumentSummary.Project.ProjectDescr.Title', 'dc.title'),
(104, 'ncbi', '', 'ncbi.DocumentSummary.Project.ProjectDescr.Description', 'dc.description.abstract'),
(105, 'ncbi', '', 'ncbi.DocumentSummary.Project.ProjectType.ProjectTypeSubmission.Target.Organism.OrganismName', 'dwc.taxon.scientificName'),
(106, 'ncbi', '', 'ncbi.DocumentSummary.Submission.Description.Organization.Name', 'dc.creator'),
(107, 'ncbi', '', 'ncbi.DocumentSummary.Project.ProjectType.ProjectTypeSubmission.Target.Provider', 'dc.creator'),
(108, 'github', '', 'language', 'dc.language.programming');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `messageID` int(11) NOT NULL,
  `process` varchar(200) NOT NULL,
  `type` varchar(20) NOT NULL,
  `message` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metadata`
--

CREATE TABLE `metadata` (
  `rowID` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `parentRowID` int(11) DEFAULT NULL,
  `field` varchar(200) NOT NULL,
  `place` int(11) NOT NULL,
  `value` longtext NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sourceData`
--

CREATE TABLE `sourceData` (
  `rowID` int(11) NOT NULL,
  `source` varchar(30) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `sourceData` longtext NOT NULL,
  `format` varchar(10) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transformations`
--

CREATE TABLE `transformations` (
  `transformationID` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `field` varchar(100) NOT NULL,
  `place` int(11) NOT NULL DEFAULT '1',
  `type` varchar(50) NOT NULL,
  `transformation` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transformations`
--

INSERT INTO `transformations` (`transformationID`, `source`, `field`, `place`, `type`, `transformation`) VALUES
(1, 'arxiv', 'dc.identifier.arxivid', 1, 'replacePartOfString', 'http://arxiv.org/abs/::with::'),
(2, 'arxiv', 'dc.identifier.arxivid', 2, 'getPartOfString', '0::to::-2'),
(3, 'arxiv', 'dc.contributor.author', 2, 'reorderPartsOfString', 'firstName lastName::to::lastName, firstName'),
(5, 'arxiv', 'dc.date.issued', 1, 'getPartOfString', '0::to::10'),
(6, 'arxiv', 'dc.date.updated', 1, 'getPartOfString', '0::to::10'),
(7, 'arxiv', 'dc.contributor.author', 1, 'useValueOfChildElement', 'name'),
(8, 'repository', 'dspace.collection.handle', 1, 'replacePartOfString', 'col_::with::'),
(9, 'repository', 'dspace.collection.handle', 2, 'replacePartOfString', '_::with::/'),
(10, 'repository', 'dspace.community.handle', 1, 'replacePartOfString', 'com_::with::'),
(11, 'repository', 'dspace.community.handle', 2, 'replacePartOfString', '_::with::/'),
(12, 'repository', 'dc.identifier.orcid', 1, 'replacePartOfString', 'http://orcid.org/::with::'),
(13, 'crossref', 'dc.type', 1, 'runFunction', 'convertCrossrefType'),
(14, 'crossref', 'crossref.date', 1, 'runFunction', 'convertCrossrefDate'),
(15, 'crossref', 'dc.identifier.orcid', 1, 'replacePartOfString', 'http://orcid.org/::with::'),
(16, 'repository', 'dc.identifier.patentnumber\r\n', 1, 'runFunction', 'googlePatentsToUniversal'),
(17, 'repository', 'dc.identifier.applicationnumber\r\n\r\n', 1, 'runFunction', 'googlePatentsToUniversal'),
(18, 'googlePatents', 'dc.contributor.author', 1, 'reorderPartsOfString', 'firstName lastName::to::lastName, firstName'),
(19, 'googlePatents', 'dc.description.abstract', 1, 'runFunction', 'trim'),
(20, 'googlePatents', 'dc.title', 1, 'runFunction', 'trim'),
(21, 'arxiv', 'dc.subject.arxiv', 1, 'useValueOfAttribute', 'term'),
(22, 'arxiv', 'dc.description.abstract', 1, 'pregReplacePartOfString', '/\\r|\\n/::with:: '),
(23, 'arxiv', 'dc.subject', 1, 'useValueOfAttribute', 'term'),
(24, 'arxiv', 'dc.relation.url', 1, 'useValueOfAttribute', 'href'),
(25, 'ieee', 'dc.contributor.author', 1, 'reorderPartsOfString', 'firstName lastName::to::lastName, firstName'),
(26, 'semanticScholar', 'dc.contributor.author', 1, 'reorderPartsOfString', 'firstName lastName::to::lastName, firstName'),
(27, 'semanticScholar', 'dc.identifier.arxivid', 1, 'prependString', 'arXiv:'),
(28, 'arxiv', 'dc.title', 1, 'pregReplacePartOfString', '/\\n/::with::'),
(29, 'arxiv', 'dc.title', 2, 'replacePartOfString', '  ::with:: '),
(30, 'googleScholar', 'dc.contributor.author', 1, 'reorderPartsOfString', 'firstName lastName::to::lastName, firstName');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `emails` varchar(100) NOT NULL,
  `admin` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mappings`
--
ALTER TABLE `mappings`
  ADD PRIMARY KEY (`mappingID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table `metadata`
--
ALTER TABLE `metadata`
  ADD PRIMARY KEY (`rowID`),
  ADD KEY `value` (`value`(200)) KEY_BLOCK_SIZE=200,
  ADD KEY `field` (`field`),
  ADD KEY `added` (`added`),
  ADD KEY `parentRowID` (`parentRowID`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `checkAll` (`source`,`idInSource`,`parentRowID`,`field`,`place`,`deleted`) USING BTREE;

--
-- Indexes for table `sourceData`
--
ALTER TABLE `sourceData`
  ADD PRIMARY KEY (`rowID`),
  ADD KEY `added` (`added`),
  ADD KEY `replacedBy` (`replacedByRowID`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `source` (`source`,`idInSource`) USING BTREE;

--
-- Indexes for table `transformations`
--
ALTER TABLE `transformations`
  ADD PRIMARY KEY (`transformationID`),
  ADD KEY `mappedFieldID` (`source`),
  ADD KEY `type` (`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mappings`
--
ALTER TABLE `mappings`
  MODIFY `mappingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `metadata`
--
ALTER TABLE `metadata`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sourceData`
--
ALTER TABLE `sourceData`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transformations`
--
ALTER TABLE `transformations`
  MODIFY `transformationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
