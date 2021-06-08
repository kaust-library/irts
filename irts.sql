-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--

-- ---------- This file contains the irts database structure and some default or sample data for certain tables.
-- --------------------- Created by : Daryl Grenz and Yasmeen Alsaedy
-- ----------- Institute : King Abdullah University of Science and Technology | KAUST
-- ----------------------------- Date : May 2021
--

--
-- Database: `irts`
--
CREATE DATABASE IF NOT EXISTS `irts` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `irts`;

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

--
-- Dumping data for table `mappings`
--

INSERT INTO `metadata` (`rowID`, `source`, `idInSource`, `parentRowID`, `field`, `place`, `value`, `added`, `deleted`, `replacedByRowID`) VALUES
(1, 'irts', 'itemType_Conference Paper', NULL, 'irts.form.field', 1, 'dc.conference.date', '2019-05-23 03:09:56', NULL, NULL),
(2, 'irts', 'itemType_Conference Paper', NULL, 'irts.form.field', 1, 'dc.conference.location', '2019-05-23 03:09:56', NULL, NULL),
(3, 'irts', 'itemType_Conference Paper', NULL, 'irts.form.field', 1, 'dc.conference.name', '2019-05-23 03:09:56', NULL, NULL),
(4, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 3, 'dc.contributor.assignee', '2019-05-23 03:09:56', NULL, NULL),
(5, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 2, 'dc.contributor.author', '2019-05-23 03:09:56', NULL, NULL),
(6, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 3, 'dc.contributor.author', '2019-05-23 03:09:56', NULL, NULL),
(7, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 5, 'dc.date.issued', '2019-05-23 03:09:56', NULL, NULL),
(8, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 4, 'dc.date.issued', '2019-05-23 03:09:56', NULL, NULL),
(9, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 4, 'dc.date.submitted', '2019-05-23 03:09:56', NULL, NULL),
(10, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 6, 'dc.description.abstract', '2019-05-23 03:09:56', NULL, NULL),
(11, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 10, 'dc.description.abstract', '2019-05-23 03:09:56', NULL, NULL),
(12, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 17, 'dc.description.dataAvailability', '2019-05-23 03:09:56', NULL, NULL),
(13, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 12, 'dc.description.sponsorship', '2019-05-23 03:09:56', NULL, NULL),
(14, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 22, 'dc.eprint.version', '2019-05-23 03:09:56', NULL, NULL),
(15, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 8, 'dc.identifier.applicationnumber', '2019-05-23 03:09:56', NULL, NULL),
(16, 'irts', 'itemType_Preprint', NULL, 'irts.form.field', 7, 'dc.identifier.arxivid', '2019-05-23 03:09:56', NULL, NULL),
(17, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 8, 'dc.identifier.citation', '2019-05-23 03:09:56', NULL, NULL),
(18, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 5, 'dc.identifier.doi', '2019-05-23 03:09:56', NULL, NULL),
(19, 'irts', 'itemType_Article', NULL, 'irts.form.field', 7, 'dc.identifier.journal', '2019-05-23 03:09:56', NULL, NULL),
(20, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 7, 'dc.identifier.patentnumber', '2019-05-23 03:09:56', NULL, NULL),
(21, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 6, 'dc.publisher', '2019-05-23 03:09:56', NULL, NULL),
(22, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 18, 'dc.related.accessionNumber', '2019-05-23 03:09:56', NULL, NULL),
(23, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 19, 'dc.related.codeURL', '2019-05-23 03:09:56', NULL, NULL),
(24, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 20, 'dc.related.datasetDOI', '2019-05-23 03:09:56', NULL, NULL),
(25, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 21, 'dc.related.datasetURL', '2019-05-23 03:09:56', NULL, NULL),
(26, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 9, 'dc.relation.url', '2019-05-23 03:09:56', NULL, NULL),
(27, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 9, 'dc.relation.url', '2019-05-23 03:09:56', NULL, NULL),
(28, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 23, 'dc.rights', '2019-05-23 03:09:56', NULL, NULL),
(29, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 26, 'dc.rights.embargodate', '2019-05-23 03:09:56', NULL, NULL),
(30, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 25, 'dc.rights.embargolength', '2019-05-23 03:09:56', NULL, NULL),
(31, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 24, 'dc.rights.uri', '2019-05-23 03:09:56', NULL, NULL),
(32, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 11, 'dc.subject', '2019-05-23 03:09:56', NULL, NULL),
(33, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 1, 'dc.title', '2019-05-23 03:09:56', NULL, NULL),
(34, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 2, 'dc.title', '2019-05-23 03:09:56', NULL, NULL),
(35, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 0, 'dc.type', '2019-05-23 03:09:56', NULL, NULL),
(36, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 1, 'dc.type', '2019-05-23 03:09:56', NULL, NULL),
(37, 'irts', 'itemType_Patent', NULL, 'irts.form.field', 10, 'googlePatents.citation_pdf_url', '2019-05-23 03:09:56', NULL, NULL),
(38, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 15, 'local.acknowledged.supportUnit', '2019-05-23 03:09:56', NULL, NULL),
(39, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 16, 'local.acknowledged.person', '2019-05-23 03:09:56', NULL, NULL),
(40, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 13, 'local.acknowledgement.type', '2019-05-23 03:09:57', NULL, NULL),
(41, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 14, 'local.grant.number', '2019-05-23 03:09:57', NULL, NULL),
(42, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 4, 'abstract', '2019-05-23 03:09:57', NULL, NULL),
(43, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 5, 'acknowledgements', '2019-05-23 03:09:57', NULL, NULL),
(44, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 6, 'acknowledgementsPlus', '2019-05-23 03:09:57', NULL, NULL),
(45, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 2, 'affiliations', '2019-05-23 03:09:57', NULL, NULL),
(46, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 3, 'authors', '2019-05-23 03:09:57', NULL, NULL),
(47, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 7, 'dataAvailability', '2019-05-23 03:09:57', NULL, NULL),
(48, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 8, 'dataAvailabilityPlus', '2019-05-23 03:09:57', NULL, NULL),
(49, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 1, 'initial', '2019-05-23 03:09:57', NULL, NULL),
(50, 'irts', 'itemType_Article', NULL, 'irts.form.step', 1, 'initial', '2019-05-23 03:09:57', NULL, NULL),
(51, 'irts', 'itemType_Preprint', NULL, 'irts.form.step', 1, 'initial', '2019-05-23 03:09:57', NULL, NULL),
(52, 'irts', 'itemType_Conference Paper', NULL, 'irts.form.step', 1, 'initial', '2019-05-23 03:09:57', NULL, NULL),
(53, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 9, 'rights', '2019-05-23 03:09:57', NULL, NULL),
(54, 'irts', 'itemType_Article', NULL, 'irts.form.template', 1, 'itemType_Publication', '2019-05-23 03:09:57', NULL, NULL),
(55, 'irts', 'itemType_Conference Paper', NULL, 'irts.form.template', 1, 'itemType_Publication', '2019-05-23 03:09:57', NULL, NULL),
(56, 'irts', 'itemType_Preprint', NULL, 'irts.form.template', 1, 'itemType_Publication', '2019-05-23 03:09:57', NULL, NULL),
(57, 'irts', 'itemType_Article', 50, 'irts.form.fields', 1, 'dc.identifier.journal', '2019-05-23 06:21:17', NULL, NULL),
(58, 'irts', 'itemType_Article', 19, 'irts.form.label', 1, 'Journal', '2019-05-23 06:21:17', NULL, NULL),
(59, 'irts', 'itemType_Conference Paper', 52, 'irts.form.fields', 1, 'dc.conference.name,dc.conference.location,dc.conference.date', '2019-05-23 06:21:17', NULL, NULL),
(60, 'irts', 'itemType_Conference Paper', 3, 'irts.form.label', 1, 'Conference Name', '2019-05-23 06:21:17', NULL, NULL),
(61, 'irts', 'itemType_Conference Paper', 2, 'irts.form.label', 1, 'Conference Location', '2019-05-23 06:21:17', NULL, NULL),
(62, 'irts', 'itemType_Conference Paper', 1, 'irts.form.label', 1, 'Conference Dates', '2019-05-23 06:21:17', NULL, NULL),
(63, 'irts', 'itemType_Patent', 35, 'irts.form.label', 1, 'Type', '2019-05-23 06:21:17', NULL, NULL),
(64, 'irts', 'itemType_Patent', 33, 'irts.form.label', 1, 'Title', '2019-05-23 06:21:17', NULL, NULL),
(65, 'irts', 'itemType_Patent', 5, 'irts.form.label', 1, 'Inventors', '2019-05-23 06:21:17', NULL, NULL),
(66, 'irts', 'itemType_Patent', 4, 'irts.form.label', 1, 'Assignee', '2019-05-23 06:21:17', NULL, NULL),
(67, 'irts', 'itemType_Patent', 9, 'irts.form.label', 1, 'Filing Date', '2019-05-23 06:21:17', NULL, NULL),
(68, 'irts', 'itemType_Patent', 7, 'irts.form.label', 1, 'Date of Issue', '2019-05-23 06:21:17', NULL, NULL),
(69, 'irts', 'itemType_Patent', 10, 'irts.form.label', 1, 'Abstract', '2019-05-23 06:21:17', NULL, NULL),
(70, 'irts', 'itemType_Patent', 20, 'irts.form.label', 1, 'Patent Number', '2019-05-23 06:21:17', NULL, NULL),
(71, 'irts', 'itemType_Patent', 15, 'irts.form.label', 1, 'Application Number', '2019-05-23 06:21:17', NULL, NULL),
(72, 'irts', 'itemType_Patent', 26, 'irts.form.label', 1, 'Additional Links', '2019-05-23 06:21:17', NULL, NULL),
(73, 'irts', 'itemType_Patent', 37, 'irts.form.label', 1, 'Patent PDF', '2019-05-23 06:21:17', NULL, NULL),
(74, 'irts', 'itemType_Patent', 20, 'irts.form.note', 1, 'add all numbers in the patent family (from any country) that have status of Grant, separate by ||', '2019-05-23 06:21:17', NULL, NULL),
(75, 'irts', 'itemType_Patent', 15, 'irts.form.note', 1, 'add all numbers in the patent family (from any country) that have status of Application, separate by ||', '2019-05-23 06:21:17', NULL, NULL),
(76, 'irts', 'itemType_Preprint', 51, 'irts.form.fields', 1, 'dc.identifier.arxivid', '2019-05-23 06:21:17', NULL, NULL),
(77, 'irts', 'itemType_Preprint', 16, 'irts.form.label', 1, 'arXiv ID', '2019-05-23 06:21:17', NULL, NULL),
(78, 'irts', 'itemType_Publication', 49, 'irts.form.fields', 1, 'dc.type,dc.title,dc.contributor.author,dc.date.submitted,dc.date.accepted,dc.date.issued,dc.identifier.doi,dc.relation.url', '2019-05-23 06:21:17', NULL, NULL),
(79, 'irts', 'itemType_Publication', 42, 'irts.form.fields', 1, 'dc.description.abstract,dc.subject', '2019-05-23 06:21:17', NULL, NULL),
(80, 'irts', 'itemType_Publication', 43, 'irts.form.fields', 1, 'dc.description.sponsorship', '2019-05-23 06:21:17', NULL, NULL),
(81, 'irts', 'itemType_Publication', 47, 'irts.form.fields', 1, 'dc.description.dataAvailability', '2019-05-23 06:21:17', NULL, NULL),
(82, 'irts', 'itemType_Publication', 53, 'irts.form.fields', 1, 'dc.eprint.version,dc.rights.embargolength,dc.rights.embargodate,dc.rights,dc.rights.uri,dc.date.issued,dc.publisher,dc.identifier.arxivid,unpaywall.relation.url', '2019-05-23 06:21:17', NULL, NULL),
(83, 'irts', 'itemType_Publication', 30, 'irts.form.label', 1, 'Embargo Length', '2019-05-23 06:21:17', NULL, NULL),
(84, 'irts', 'itemType_Publication', 30, 'irts.form.note', 1, 'In Months', '2019-05-23 06:21:17', NULL, NULL),
(85, 'irts', 'itemType_Publication', 45, 'irts.form.fields', 1, 'dc.contributor.affiliation', '2019-05-23 06:21:17', NULL, NULL),
(86, 'irts', 'itemType_Publication', 22, 'irts.form.label', 1, 'Accession Numbers', '2019-05-23 06:21:17', NULL, NULL),
(87, 'irts', 'itemType_Publication', 23, 'irts.form.label', 1, 'Code URLs', '2019-05-23 06:21:17', NULL, NULL),
(88, 'irts', 'itemType_Publication', 24, 'irts.form.label', 1, 'Dataset DOIs', '2019-05-23 06:21:17', NULL, NULL),
(89, 'irts', 'itemType_Publication', 25, 'irts.form.label', 1, 'Dataset URLs', '2019-05-23 06:21:17', NULL, NULL),
(90, 'irts', 'itemType_Publication', 40, 'irts.form.label', 1, 'Type of Local Acknowledgement', '2019-05-23 06:21:17', NULL, NULL),
(91, 'irts', 'itemType_Publication', 41, 'irts.form.label', 1, 'Local Grant Number', '2019-05-23 06:21:17', NULL, NULL),
(92, 'irts', 'itemType_Publication', 38, 'irts.form.label', 1, 'Acknowledged Local Org Unit', '2019-05-23 06:21:17', NULL, NULL),
(93, 'irts', 'itemType_Publication', 39, 'irts.form.label', 1, 'Acknowledged Local Person', '2019-05-23 06:21:17', NULL, NULL),
(94, 'irts', 'itemType_Publication', 14, 'irts.form.inputType', 1, 'dropdown', '2019-05-23 06:21:17', NULL, NULL),
(95, 'irts', 'itemType_Publication', 46, 'irts.form.fields', 1, 'dc.contributor.author', '2019-05-23 06:21:17', NULL, NULL),
(96, 'irts', 'itemType_Publication', 44, 'irts.form.fields', 1, 'dc.description.sponsorship,local.acknowledgement.type,local.grant.number,local.acknowledged.supportUnit,local.acknowledged.person', '2019-05-23 06:21:17', NULL, NULL),
(97, 'irts', 'itemType_Publication', 48, 'irts.form.fields', 1, 'dc.description.dataAvailability,dc.related.accessionNumber,dc.related.codeURL,dc.related.datasetDOI,dc.related.datasetURL', '2019-05-23 06:21:17', NULL, NULL),
(98, 'irts', 'itemType_Publication', 18, 'irts.form.baseURL', 1, 'https://doi.org/', '2019-05-23 06:21:17', NULL, NULL),
(99, 'irts', 'itemType_Publication', 29, 'irts.form.label', 1, 'Embargo End Date', '2019-05-23 06:21:17', NULL, NULL),
(100, 'irts', 'itemType_Publication', 31, 'irts.form.label', 1, 'Link to License', '2019-05-23 06:21:17', NULL, NULL),
(101, 'irts', 'itemType_Publication', 28, 'irts.form.label', 1, 'Terms of Use', '2019-05-23 06:21:17', NULL, NULL),
(102, 'irts', 'itemType_Publication', 14, 'irts.form.label', 1, 'Version', '2019-05-23 06:21:17', NULL, NULL),
(103, 'irts', 'itemType_Publication', 12, 'irts.form.label', 1, 'Data Availability Statement', '2019-05-23 06:21:17', NULL, NULL),
(104, 'irts', 'itemType_Publication', 13, 'irts.form.label', 1, 'Acknowledgments', '2019-05-23 06:21:17', NULL, NULL),
(105, 'irts', 'itemType_Publication', 32, 'irts.form.label', 1, 'Keywords', '2019-05-23 06:21:17', NULL, NULL),
(106, 'irts', 'itemType_Publication', 11, 'irts.form.label', 1, 'Abstract', '2019-05-23 06:21:17', NULL, NULL),
(107, 'irts', 'itemType_Publication', 27, 'irts.form.label', 1, 'Related URL', '2019-05-23 06:21:17', NULL, NULL),
(108, 'irts', 'itemType_Publication', 17, 'irts.form.label', 1, 'Citation', '2019-05-23 06:21:17', NULL, NULL),
(109, 'irts', 'itemType_Publication', 21, 'irts.form.label', 1, 'Publisher', '2019-05-23 06:21:17', NULL, NULL),
(110, 'irts', 'itemType_Publication', 18, 'irts.form.label', 1, 'DOI', '2019-05-23 06:21:17', NULL, NULL),
(111, 'irts', 'itemType_Publication', 8, 'irts.form.label', 1, 'Publication Date', '2019-05-23 06:21:17', NULL, NULL),
(112, 'irts', 'itemType_Publication', 36, 'irts.form.label', 1, 'Type', '2019-05-23 06:21:17', NULL, NULL),
(113, 'irts', 'itemType_Publication', 6, 'irts.form.label', 1, 'Authors', '2019-05-23 06:21:17', NULL, NULL),
(114, 'irts', 'itemType_Publication', 34, 'irts.form.label', 1, 'Title', '2019-05-23 06:21:17', NULL, NULL),
(115, 'irts', 'itemType_Publication', 8, 'irts.form.note', 1, 'yyyy-mm-dd', '2019-05-23 06:21:17', NULL, NULL),
(116, 'irts', 'itemType_Publication', 29, 'irts.form.note', 1, 'yyyy-mm-dd', '2019-05-23 06:21:17', NULL, NULL),
(117, 'irts', 'itemType_Publication', 6, 'irts.form.field', 3, 'dc.contributor.affiliation', '2019-05-23 06:48:05', NULL, NULL),
(118, 'irts', 'itemType_Publication', 6, 'irts.form.field', 1, 'dc.identifier.orcid', '2019-05-23 06:48:05', NULL, NULL),
(119, 'irts', 'itemType_Publication', 6, 'irts.form.field', 2, 'irts.author.correspondingEmail', '2019-05-23 06:48:05', NULL, NULL),
(120, 'irts', 'itemType_Publication', 118, 'irts.form.baseURL', 1, 'https://orcid.org/', '2019-05-23 06:49:28', NULL, NULL),
(121, 'irts', 'itemType_Publication', 117, 'irts.form.label', 1, 'Affiliations', '2019-05-23 06:49:28', NULL, NULL),
(122, 'irts', 'itemType_Publication', 118, 'irts.form.label', 1, 'ORCID', '2019-05-23 06:49:28', NULL, NULL),
(123, 'irts', 'itemType_Publication', 119, 'irts.form.label', 1, 'Corresponding Author Email', '2019-05-23 06:49:28', NULL, NULL),
(124, 'irts', 'itemType_Publication', 40, 'irts.form.inputType', 1, 'dropdown', '2019-05-23 06:21:17', NULL, NULL),
(125, 'irts', 'itemType_Publication', 40, 'irts.form.values', 1, 'No acknowledgement,No mention of Local in acknowledgement,Non-funding acknowledgement,Partially funded (multiple funders acknowledged),Fully funded (only Local funding is acknowledged)', '2019-05-23 06:21:17', NULL, NULL),
(126, 'irts', 'itemType_Publication', 14, 'irts.form.values', 1, 'Pre-print,Post-print,Publisher\'s Version/PDF', '2019-05-23 06:21:17', NULL, NULL),
(127, 'irts', 'itemType_Publication', 16, 'irts.form.baseURL', 1, 'http://arxiv.org/abs/', '2019-05-23 06:21:17', NULL, NULL),
(128, 'irts', 'itemType_Book', NULL, 'irts.form.field', 12, 'dc.relation.haspart', '2018-11-20 04:21:11', NULL, NULL),
(129, 'irts', 'itemType_Book', NULL, 'irts.form.field', 3, 'dc.contributor.editor', '2018-11-20 04:21:11', NULL, NULL),
(130, 'irts', 'itemType_Book', NULL, 'irts.form.field', 3, 'irts.contributor.type', '2018-11-20 04:21:11', NULL, NULL),
(131, 'irts', 'itemType_Book', NULL, 'irts.form.field', 2, 'dc.title', '2018-11-20 04:21:11', NULL, NULL),
(132, 'irts', 'itemType_Book', NULL, 'irts.form.field', 3, 'dc.contributor.author', '2018-11-20 04:21:11', NULL, NULL),
(133, 'irts', 'itemType_Book', NULL, 'irts.form.field', 1, 'dc.type', '2018-11-20 04:21:11', NULL, NULL),
(134, 'irts', 'itemType_Book', NULL, 'irts.form.field', 4, 'dc.date.issued', '2018-11-20 04:21:11', NULL, NULL),
(135, 'irts', 'itemType_Book', NULL, 'irts.form.field', 5, 'dc.identifier.doi', '2018-11-20 04:21:11', NULL, NULL),
(136, 'irts', 'itemType_Book', NULL, 'irts.form.field', 6, 'dc.publisher', '2018-11-20 04:21:11', NULL, NULL),
(137, 'irts', 'itemType_Book', NULL, 'irts.form.field', 8, 'dc.identifier.citation', '2018-11-20 04:21:11', NULL, NULL),
(138, 'irts', 'itemType_Book', NULL, 'irts.form.field', 9, 'dc.relation.url', '2018-11-20 04:21:11', NULL, NULL),
(139, 'irts', 'itemType_Book', NULL, 'irts.form.field', 10, 'dc.description.abstract', '2018-11-20 04:21:11', NULL, NULL),
(140, 'irts', 'itemType_Book', NULL, 'irts.form.field', 11, 'dc.subject', '2018-11-20 04:21:11', NULL, NULL),
(141, 'irts', 'itemType_Book', 134, 'irts.form.note', 1, 'yyyy-mm-dd', '2018-11-21 02:05:20', NULL, NULL),
(142, 'irts', 'itemType_Book', NULL, 'irts.form.step', 1, 'initial', '2018-11-26 13:09:47', NULL, NULL),
(143, 'irts', 'itemType_Book', NULL, 'irts.form.step', 2, 'affiliations', '2018-11-26 13:09:47', NULL, NULL),
(144, 'irts', 'itemType_Book', NULL, 'irts.form.step', 3, 'authors', '2018-11-26 13:09:47', NULL, NULL),
(145, 'irts', 'itemType_Book', NULL, 'irts.form.step', 4, 'abstract', '2018-11-26 13:09:47', NULL, NULL),
(146, 'irts', 'itemType_Book', NULL, 'irts.form.field', 5, 'dc.identifier.isbn', '2018-11-20 04:21:11', NULL, NULL),
(147, 'irts', 'itemType_Book', NULL, 'irts.form.step', 5, 'chapters', '2018-11-26 13:09:47', NULL, NULL),
(148, 'irts', 'itemType_Book', 131, 'irts.form.label', 1, 'Title', '2018-11-21 02:05:20', NULL, NULL),
(149, 'irts', 'itemType_Book', 132, 'irts.form.field', 3, 'dc.contributor.affiliation', '2018-11-21 02:05:20', NULL, NULL),
(150, 'irts', 'itemType_Book', 132, 'irts.form.label', 1, 'Authors', '2018-11-21 02:05:20', NULL, NULL),
(151, 'irts', 'itemType_Book', 133, 'irts.form.label', 1, 'Type', '2018-11-21 02:05:20', NULL, NULL),
(152, 'irts', 'itemType_Book', 134, 'irts.form.label', 1, 'Publication Date', '2018-11-21 02:05:20', NULL, NULL),
(153, 'irts', 'itemType_Book', 135, 'irts.form.label', 1, 'DOI', '2018-11-21 02:05:20', NULL, NULL),
(154, 'irts', 'itemType_Book', 135, 'irts.form.baseURL', 1, 'https://doi.org/', '2018-11-20 04:21:11', NULL, NULL),
(155, 'irts', 'itemType_Book', 136, 'irts.form.label', 1, 'Publisher', '2018-11-21 02:05:20', NULL, NULL),
(156, 'irts', 'itemType_Book', 137, 'irts.form.label', 1, 'Citation', '2018-11-21 02:05:20', NULL, NULL),
(157, 'irts', 'itemType_Book', 138, 'irts.form.label', 1, 'Related URL', '2018-11-21 02:05:20', NULL, NULL),
(158, 'irts', 'itemType_Book', 139, 'irts.form.label', 1, 'Abstract', '2018-11-21 02:05:20', NULL, NULL),
(159, 'irts', 'itemType_Book', 140, 'irts.form.label', 1, 'Keywords', '2018-11-21 02:05:20', NULL, NULL),
(160, 'irts', 'itemType_Book', 149, 'irts.form.label', 1, 'Affiliations', '2018-11-21 02:05:20', NULL, NULL),
(161, 'irts', 'itemType_Book', 142, 'irts.form.fields', 1, 'dc.type,dc.title,dc.contributor.author,dc.date.issued,dc.identifier.doi,dc.identifier.isbn,dc.relation.url', '2018-11-26 13:09:47', NULL, NULL),
(162, 'irts', 'itemType_Book', 143, 'irts.form.fields', 1, 'dc.contributor.affiliation', '2018-11-26 13:09:47', NULL, NULL),
(163, 'irts', 'itemType_Book', 144, 'irts.form.fields', 1, 'dc.contributor.author,irts.contributor.type', '2018-11-26 13:09:47', NULL, NULL),
(164, 'irts', 'itemType_Book', 145, 'irts.form.fields', 1, 'dc.description.abstract,dc.subject', '2018-11-26 13:09:47', NULL, NULL),
(165, 'irts', 'itemType_Book', 129, 'irts.form.label', 1, 'Editors', '2018-11-21 02:05:20', NULL, NULL),
(166, 'irts', 'itemType_Book', 130, 'irts.form.label', 1, 'Contributor Type', '2018-11-21 02:05:20', NULL, NULL),
(167, 'irts', 'itemType_Book', 130, 'irts.form.note', 1, 'Are the listed people editors or authors of this book?', '2018-11-21 02:05:20', NULL, NULL),
(168, 'irts', 'itemType_Book', 130, 'irts.form.inputType', 1, 'dropdown', '2018-11-21 02:05:20', NULL, NULL),
(169, 'irts', 'itemType_Book', 130, 'irts.form.values', 1, ',Authors,Editors', '2018-11-21 02:05:20', NULL, NULL),
(170, 'irts', 'itemType_Book', 146, 'irts.form.label', 1, 'ISBN', '2018-11-20 04:21:11', NULL, NULL),
(171, 'irts', 'itemType_Book', 147, 'irts.form.fields', 1, 'dc.relation.haspart', '2018-11-26 13:09:47', NULL, NULL),
(172, 'irts', 'itemType_Book', 128, 'irts.form.label', 1, 'Chapters', '2018-11-21 02:05:20', NULL, NULL),
(173, 'irts', 'itemType_Book', 128, 'irts.form.field', 1, 'irts.withdraw.handle', '2018-11-21 02:05:20', NULL, NULL),
(174, 'irts', 'itemType_Book', 128, 'irts.form.field', 2, 'irts.add.doi', '2018-11-21 02:05:20', NULL, NULL),
(175, 'irts', 'itemType_Book', 173, 'irts.form.label', 1, 'Remove from repository', '2018-11-21 02:05:20', NULL, NULL),
(176, 'irts', 'itemType_Book', 174, 'irts.form.label', 1, 'Add to repository', '2018-11-21 02:05:20', NULL, NULL),
(177, 'irts', 'itemType_Book', 129, 'irts.form.fields', 1, 'dc.contributor.affiliation', '2018-11-26 13:09:47', NULL, NULL),
(178, 'irts', 'itemType_Book', 177, 'irts.form.label', 1, 'Affiliations', '2018-11-21 02:05:20', NULL, NULL),
(179, 'irts', 'itemType_Book Chapter', NULL, 'irts.form.template', 1, 'itemType_Publication', '2018-11-26 19:09:47', NULL, NULL),
(180, 'irts', 'itemType_Publication', 183, 'irts.form.label', 1, 'Type of Relationship', '2018-11-21 08:05:20', NULL, NULL),
(181, 'irts', 'itemType_Publication', 183, 'irts.form.values', 1, ',issupplementedby,references,ignore', '2018-11-21 08:05:20', NULL, NULL),
(182, 'irts', 'itemType_Publication', 183, 'irts.form.inputType', 1, 'dropdown', '2018-11-21 08:05:20', NULL, NULL),
(183, 'irts', 'itemType_Publication', 25, 'irts.form.field', 1, 'dc.relation.type', '2018-11-20 10:21:11', NULL, NULL),
(184, 'irts', 'itemType_Publication', 24, 'irts.form.field', 1, 'dc.relation.type', '2018-11-20 10:21:11', NULL, NULL),
(185, 'irts', 'itemType_Publication', 23, 'irts.form.field', 1, 'dc.relation.type', '2018-11-20 10:21:11', NULL, NULL),
(186, 'irts', 'itemType_Publication', 22, 'irts.form.field', 1, 'dc.relation.type', '2018-11-20 10:21:11', NULL, NULL),
(187, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 27, 'dc.relation.type', '2018-11-20 10:21:11', NULL, NULL),
(188, 'irts', 'itemType_Publication', 189, 'irts.form.fields', 1, 'dc.description.dataAvailability,dc.related.accessionNumber,dc.related.codeURL,dc.related.datasetDOI,dc.related.datasetURL,dc.relation.type', '2018-11-26 19:09:47', NULL, NULL),
(189, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 0, 'dataRelations', '2018-11-26 19:09:47', NULL, NULL),
(190, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 27, 'unpaywall.relation.url', '2019-11-13 05:40:40', NULL, NULL),
(191, 'irts', 'itemType_Publication', 190, 'irts.form.label', 1, 'Unpaywall Result', '2019-11-13 05:51:07', NULL, NULL),
(192, 'irts', 'itemType_Publication', 190, 'irts.form.inputType', 1, 'radiobutton', '2019-11-13 05:52:47', NULL, NULL),
(193, 'irts', 'itemType_Publication', 190, 'irts.form.field', 1, 'unpaywall.version', '2019-11-13 05:53:39', NULL, NULL),
(194, 'irts', 'itemType_Publication', NULL, 'irts.form.step', 29, 'UnpaywallStep', '2019-11-28 07:31:48', NULL, NULL),
(195, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 28, 'dc.identifier.arxivid', '2019-12-09 08:19:45', NULL, NULL),
(196, 'irts', 'itemType_Publication', 195, 'irts.form.label', 1, 'Arxiv ID', '2019-12-09 08:21:29', NULL, NULL),
(197, 'irts', 'itemType_Erratum', NULL, 'irts.form.template', 1, 'itemType_Publication', '2019-05-23 03:09:57', NULL, NULL),
(198, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 4, 'dc.date.accepted', '2020-01-19 10:46:38', NULL, NULL),
(199, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 4, 'dc.date.submitted', '2020-01-19 10:46:57', NULL, NULL),
(200, 'irts', 'itemType_Publication', 198, 'irts.form.label', 1, 'Date Accepted for Publication', '2020-01-19 10:46:57', NULL, NULL),
(201, 'irts', 'itemType_Publication', 198, 'irts.form.note', 1, 'yyyy-mm-dd', '2020-01-19 10:46:38', NULL, NULL),
(202, 'irts', 'itemType_Publication', 199, 'irts.form.label', 1, 'Date Submitted for Publication', '2020-01-19 10:46:57', NULL, NULL),
(203, 'irts', 'itemType_Publication', 199, 'irts.form.note', 1, 'yyyy-mm-dd', '2020-01-19 10:46:38', NULL, NULL),
(204, 'irts', 'itemType_Dataset', NULL, 'irts.form.template', 1, 'itemType_Dataset', '2020-03-04 09:28:42', NULL, NULL),
(205, 'irts', 'itemType_Dataset', NULL, 'irts.form.step', 1, 'initial', '2020-03-04 09:30:46', NULL, NULL),
(206, 'irts', 'itemType_Dataset', NULL, 'irts.form.step', 3, 'authors', '2020-03-04 09:33:41', NULL, NULL),
(207, 'irts', 'itemType_Dataset', NULL, 'irts.form.step', 2, 'affiliations', '2020-03-04 09:34:02', NULL, NULL),
(208, 'irts', 'itemType_Dataset', NULL, 'irts.form.step', 4, 'abstract', '2020-03-04 09:34:15', NULL, NULL),
(209, 'irts', 'itemType_Dataset', NULL, 'irts.form.step', 5, 'relations', '2020-03-04 09:34:28', NULL, NULL),
(210, 'irts', 'itemType_Dataset', 205, 'irts.form.fields', 1, 'dc.type,dc.title,dc.creator,dc.contributor.author,dc.date.submitted,dc.date.accepted,dc.date.issued,dc.identifier.doi,dc.relation.url,dc.publisher,dc.identifier.handle', '2020-03-04 09:35:56', NULL, NULL),
(211, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 1, 'dc.type', '2020-03-04 09:36:48', NULL, NULL),
(212, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 2, 'dc.title', '2020-03-04 09:37:09', NULL, NULL),
(213, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 4, 'dc.contributor.author', '2020-03-04 09:37:24', NULL, NULL),
(214, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 5, 'dc.date.issued', '2020-03-04 09:37:39', NULL, NULL),
(215, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 6, 'dc.identifier.doi', '2020-03-04 09:38:36', NULL, NULL),
(216, 'irts', 'itemType_Dataset', 212, 'irts.form.label', 1, 'Title', '2020-03-04 09:40:13', NULL, NULL),
(217, 'irts', 'itemType_Dataset', 211, 'irts.form.label', 1, 'Type', '2020-03-04 10:18:44', NULL, NULL),
(218, 'irts', 'itemType_Dataset', 213, 'irts.form.field', 3, 'dc.contributor.affiliation', '2020-03-04 10:19:22', NULL, NULL),
(219, 'irts', 'itemType_Dataset', 213, 'irts.form.field', 2, 'irts.author.correspondingEmail', '2020-03-04 10:24:13', NULL, NULL),
(220, 'irts', 'itemType_Dataset', 213, 'irts.form.label', 1, 'Authors', '2020-03-04 10:24:37', NULL, NULL),
(221, 'irts', 'itemType_Dataset', 214, 'irts.form.label', 1, 'Publication Date', '2020-03-04 10:25:10', NULL, NULL),
(222, 'irts', 'itemType_Dataset', 214, 'irts.form.note', 1, 'yyyy-mm-dd', '2020-03-04 10:26:17', NULL, NULL),
(223, 'irts', 'itemType_Dataset', 215, 'irts.form.baseURL', 1, 'https://doi.org/', '2020-03-04 10:27:30', NULL, NULL),
(224, 'irts', 'itemType_Dataset', 208, 'irts.form.fields', 1, 'dc.description.abstract,dc.subject', '2020-03-04 10:30:27', NULL, NULL),
(225, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 8, 'dc.description.abstract', '2020-03-04 10:31:07', NULL, NULL),
(226, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 9, 'dc.subject', '2020-03-04 10:31:27', NULL, NULL),
(227, 'irts', 'itemType_Dataset', 226, 'irts.form.label', 1, 'Keywords', '2020-03-04 10:32:15', NULL, NULL),
(228, 'irts', 'itemType_Dataset', 225, 'irts.form.label', 1, 'Abstract', '2020-03-04 10:32:37', NULL, NULL),
(229, 'irts', 'itemType_Dataset', 213, 'irts.form.label', 1, 'Affiliations', '2020-03-04 10:37:01', NULL, NULL),
(230, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 10, 'dc.relationType', '2020-03-04 10:37:36', NULL, NULL),
(231, 'irts', 'itemType_Dataset', 230, 'irts.form.label', 1, 'Relation Type', '2020-03-04 10:38:17', NULL, NULL),
(232, 'irts', 'itemType_Dataset', 230, 'irts.form.inputType', 1, 'dropdown', '2020-03-04 10:38:42', NULL, NULL),
(233, 'irts', 'itemType_Dataset', 230, 'irts.form.values', 1, 'No relation,ispartof,isprevious,isversionof,issupplementto,isreferencedby,isnewversionof,isdocumentedby,iscitedby,haspart,reviews,isidenticalto,ispreviousversionof,references,isreferencedby', '2020-03-04 10:39:14', NULL, NULL),
(234, 'irts', 'itemType_Dataset', 209, 'irts.form.fields', 1, 'dc.relationType,dc.relatedIdentifier', '2020-03-04 10:40:12', NULL, NULL),
(235, 'irts', 'itemType_Dataset', 230, 'irts.form.field', 11, 'dc.relatedIdentifier', '2020-03-04 10:44:42', NULL, NULL),
(236, 'irts', 'itemType_Dataset', 235, 'irts.form.label', 1, 'Related identifier', '2020-03-04 10:45:48', NULL, NULL),
(237, 'irts', 'itemType_Dataset', 215, 'irts.form.label', 1, 'Dataset DOI', '2020-03-04 10:47:04', NULL, NULL),
(238, 'irts', 'itemType_Dataset', 206, 'irts.form.fields', 1, 'dc.creator,dc.contributor.author', '2020-03-04 10:48:00', NULL, NULL),
(239, 'irts', 'itemType_Dataset', 219, 'irts.form.label', 1, 'Corresponding Author Email', '2020-03-04 10:48:55', NULL, NULL),
(240, 'irts', 'itemType_Dataset', 207, 'irts.form.fields', 1, 'dc.contributor.affiliation', '2020-03-04 10:50:09', NULL, NULL),
(241, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 7, 'dc.identifier.handle', '2020-03-04 10:50:41', NULL, NULL),
(242, 'irts', 'itemType_Dataset', 241, 'irts.form.label', 1, 'Article handle', '2020-03-04 10:51:17', NULL, NULL),
(243, 'irts', 'itemType_Dataset', 241, 'irts.form.baseURL', 1, 'http://hdl.handle.net/', '2020-03-04 10:51:42', NULL, NULL),
(244, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 3, 'dc.creator', '2020-03-04 10:52:25', NULL, NULL),
(245, 'irts', 'itemType_Dataset', 244, 'irts.form.label', 1, 'Creators', '2020-03-04 10:52:57', NULL, NULL),
(246, 'irts', 'itemType_Dataset', 244, 'irts.form.field', 2, 'dc.contributor.affiliation', '2020-03-04 10:53:43', NULL, NULL),
(247, 'irts', 'itemType_Dataset', 246, 'irts.form.label', 1, 'Affiliation', '2020-03-04 10:54:17', NULL, NULL),
(248, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 30, 'dc.identifier.pages', '2020-03-28 01:36:37', NULL, NULL),
(249, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 31, 'dc.identifier.volume', '2020-03-28 20:37:31', NULL, NULL),
(250, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 32, 'dc.identifier.issue', '2020-03-28 20:43:09', NULL, NULL),
(251, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 33, 'dc.identifier.eid', '2020-03-28 21:03:40', NULL, NULL),
(252, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 34, 'dc.identifier.wosut', '2020-03-29 08:02:32', NULL, NULL),
(253, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 35, 'dc.identifier.pmcid', '2020-03-29 08:02:43', NULL, NULL),
(254, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 36, 'dc.identifier.pmid', '2020-03-29 08:02:57', NULL, NULL),
(255, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 37, 'dc.identifier.issn', '2020-03-29 09:56:48', NULL, NULL),
(256, 'irts', 'itemType_Publication', NULL, 'irts.form.field', 38, 'dc.identifier.isbn', '2020-03-30 08:35:51', NULL, NULL),
(257, 'irts', 'itemType_Presentation', NULL, 'irts.form.template', 1, 'itemType_Publication', '2020-04-14 03:09:57', NULL, NULL),
(258, 'irts', 'itemType_Presentation', NULL, 'irts.form.template', 2, 'itemType_Conference Paper', '2020-04-14 03:09:57', NULL, NULL),
(259, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 6, 'dc.publisher', '2020-07-01 04:45:54', NULL, NULL),
(260, 'irts', 'itemType_Dataset', 259, 'irts.form.label', 1, 'Publisher', '2020-07-01 04:46:19', NULL, NULL),
(261, 'irts', 'itemType_Dataset', NULL, 'irts.form.field', 9, 'dc.relation.url', '2020-07-01 04:26:56', NULL, NULL),
(262, 'irts', 'itemType_Dataset', 261, 'irts.form.label', 1, 'Related URL', '2020-07-29 04:55:43', NULL, NULL),
(263, 'irts', 'itemType_Bioproject', 266, 'irts.form.fields', 1, 'dwc.taxon.scientificName', '2020-12-08 10:40:32', NULL, NULL),
(264, 'irts', 'itemType_Bioproject', NULL, 'irts.form.field', 1, 'dwc.taxon.scientificName', '2020-06-01 05:45:54', NULL, NULL),
(265, 'irts', 'itemType_Bioproject', 264, 'irts.form.label', 1, 'Scientific Name', '2020-06-01 05:45:54', NULL, NULL),
(266, 'irts', 'itemType_Bioproject', NULL, 'irts.form.step', 2, 'abstract', '2020-06-01 05:45:54', NULL, NULL),
(267, 'irts', 'itemType_Bioproject', 270, 'irts.form.baseURL', 1, 'https://www.ncbi.nlm.nih.gov/bioproject/?term=', '2020-06-07 08:45:50', NULL, NULL),
(268, 'irts', 'itemType_Bioproject', NULL, 'irts.form.step', 1, 'initial', '2020-06-01 05:45:54', NULL, NULL),
(269, 'irts', 'itemType_Bioproject', 270, 'irts.form.label', 1, 'Bioproject ID', '2020-06-01 05:45:54', NULL, NULL),
(270, 'irts', 'itemType_Bioproject', NULL, 'irts.form.field', 1, 'dc.identifier.bioproject', '2020-06-01 05:45:54', NULL, NULL),
(271, 'irts', 'itemType_Bioproject', 268, 'irts.form.fields', 1, 'dc.identifier.bioproject', '2020-06-01 05:45:54', NULL, NULL),
(272, 'irts', 'itemType_Bioproject', NULL, 'irts.form.template', 1, 'itemType_Dataset', '2020-06-01 05:45:54', NULL, NULL),
(273, 'irts', 'itemType_Software', 275, 'irts.form.baseURL', 1, 'https://github.com/', '2020-06-01 05:45:54', NULL, NULL),
(274, 'irts', 'itemType_Software', 275, 'irts.form.label', 1, 'Github ID', '2020-06-01 05:45:54', NULL, NULL),
(275, 'irts', 'itemType_Software', NULL, 'irts.form.field', 1, 'dc.identifier.github', '2020-06-01 05:45:54', NULL, NULL),
(276, 'irts', 'itemType_Software', 277, 'irts.form.fields', 1, 'dc.identifier.github', '2020-06-01 05:45:54', NULL, NULL),
(277, 'irts', 'itemType_Software', NULL, 'irts.form.step', 1, 'initial', '2020-06-01 05:45:54', NULL, NULL),
(278, 'irts', 'itemType_Software', NULL, 'irts.form.template', 1, 'itemType_Dataset', '2020-05-03 06:51:21', NULL, NULL),
(279, 'irts', 'itemType_Dataset', 213, 'irts.form.field', 1, 'dc.identifier.orcid', '2020-03-04 10:24:13', NULL, NULL),
(280, 'irts', 'itemType_Dataset', 244, 'irts.form.field', 1, 'dc.identifier.orcid', '2020-03-04 10:24:13', NULL, NULL),
(281, 'irts', 'itemType_Dataset', 279, 'irts.form.label', 1, 'ORCID', '2019-05-23 06:49:28', NULL, NULL),
(282, 'irts', 'itemType_Dataset', 279, 'irts.form.baseURL', 1, 'https://orcid.org/', '2019-05-23 06:49:28', NULL, NULL),
(283, 'irts', 'itemType_Dataset', 280, 'irts.form.baseURL', 1, 'https://orcid.org/', '2019-05-23 06:49:28', NULL, NULL),
(284, 'irts', 'itemType_Dataset', 280, 'irts.form.label', 1, 'ORCID', '2019-05-23 06:49:28', NULL, NULL);

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
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;
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
