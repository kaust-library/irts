<?php
	//Locally Defined Constants
	define('INSTITUTION_ABBREVIATION', '');
	
	define('INSTITUTION_NAME', '');
	
	define('INSTITUTION_CITY', '');
	
	//Unique institutional affiliation strings
	define('INSTITUTION_NAME_VARIANTS', array(INSTITUTION_ABBREVIATION, INSTITUTION_NAME, ''));
	
	//Known false positives for institutional affiliation
	define('AFFILIATION_STRINGS_TO_IGNORE', array(""));
	
	//Fixed institutional name strings for querying particular services	
	define('SCOPUS_AF_ID', '');
	
	define('IR_EMAIL', '');
	
	define('OAPOLICY_URL', '');
	
	define('OAPOLICY_START_DATE', ''); //YYYY-MM-DD format
	
	define('PUBLICATION_TRACKING_START_DATE', '');
	
	// DSpace repository details
	define('DSPACE_VERSION', ''); //'5' or '6'
	
	if(DSPACE_VERSION === '5')
	{
		define('DSPACE_INTERNAL_ID_KEY_NAME', 'id');
	}
	elseif(DSPACE_VERSION === '6')
	{
		define('DSPACE_INTERNAL_ID_KEY_NAME', 'uuid');
	}
	
	//If DSpace REST API has been modified to support collection mapping
	define('DSPACE_COLLECTION_MAPPING_SUPPORTED', FALSE);
	
	define('REPOSITORY_BASE_URL', '');
	
	define('REPOSITORY_API_URL', 'https://'.REPOSITORY_BASE_URL.'/rest/');
	
	define('REPOSITORY_OAI_URL', 'https://'.REPOSITORY_BASE_URL.'/oai/request?');
	
	define('REPOSITORY_OAI_ID_PREFIX', 'oai:'.REPOSITORY_BASE_URL.':');
	
	define('SUBMISSIONS_COLLECTION_ID', '');
	
	define('ACKNOWLEDGEMENT_ONLY_COLLECTION_ID', '');
	
	define('TYPE_COLLECTION_IDS', array('Article' => '', 
								'Book' => '', 
								'Book Chapter' => '', 
								'Conference Paper' => '', 
								'Dataset' => '', 
								'Bioproject' => '', 
								'Patent' => '',
								'Poster' => '', 
								'Preprint' => '',
								'Presentation' => '',
								'Software' => '', 
								'Technical Report' => ''));
	
	define('LOCAL_PERSON_FIELD', '');
	
	define('ORCID_ENABLED_FIELDS', array('dc.contributor.author','dc.contributor.advisor','dc.contributor.committeemember'));
	
	//Item types that will be flagged for special relation handling
	define('HANDLING_RELATIONS', array('Dataset', 'Data File', 'Bioproject', 'Software'));
	
	//LDAP constants
	define('LDAP_ACCOUNT_SUFFIX', ''); //binding parameters

	define('LDAP_HOSTNAME_SSL', ''); // space-separated list of valid hostnames for failover

	define('LDAP_BASE_DN', '');

	define('LDAP_PERSON_ID_ATTRIBUTE', '');

	define('LDAP_EMAIL_ATTRIBUTE', '');
	
	define('LDAP_NAME_ATTRIBUTE', '');

	define('LDAP_TITLE_ATTRIBUTE', '');
	
	//These are the email addresses of the people who are permitted to edit the item type templates via LDAP login
	define('ADMINS', array(""));
	
	//These are the email addresses of the people who are permitted access to the main item processing form via LDAP login
	define('AUTHORIZED_PROCESSORS', array(""));
	
	// Comparable publication sources (which primarily track items with Crossref DOIs, such as journal articles and conference papers)
	define('PUBLICATION_SOURCES', array('crossref','europePMC','googleScholar','ieee','repository','scopus','wos'));
	
	// Item types to be considered in charts showing "Publications"
	define('PUBLICATION_TYPES', array('Article', 'Book', 'Book Chapter', 'Conference Paper'));
	
	//External data source URLs
	define('ARXIV_API_URL', 'http://export.arxiv.org/api/query?search_query=');
	
	define('CROSSREF_API', 'https://api.crossref.org/');

    define('DATACITE_API', 'https://api.datacite.org/');
	
	define('DOI_BASE_URL', 'https://doi.org/');
	
	define('ELSEVIER_API_URL', 'https://api.elsevier.com/content/');
	
	define('EUROPEPMC_API_URL', 'https://www.ebi.ac.uk/europepmc/webservices/rest/');

  define('GITHUB_API', 'https://api.github.com/repos/');
	
	define('GOOGLE_SCHOLAR_URL', 'https://scholar.google.com/scholar');
	
	define('IEEE_API', 'http://ieeexploreapi.ieee.org/api/v1/search/articles?');

  define('NCBI_API_URL', 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/');
	
	define('SHERPA_ROMEO_API_URL', 'https://v2.sherpa.ac.uk/cgi/retrieve?');
	
	define('UNPAYWALL_API_URL', 'https://api.unpaywall.org/v2/');
	
	//Common Constants
	define('TODAY', date("Y-m-d"));

	define('YESTERDAY', date("Y-m-d", strtotime("-1 days")));

	define('ONE_WEEK_AGO', date("Y-m-d", strtotime("-7 days")));
	
	define('ONE_WEEK_LATER', date("Y-m-d", strtotime("+7 days")));
	
	define('THREE_MONTHS_AGO', date("Y-m-d", strtotime("-3 months")));
	
	define('ONE_YEAR_AGO', date("Y-m-d", strtotime("-1 years")));
	
	define('CURRENT_YEAR', date("Y"));
	
	define('OAPOLICY_START_YEAR', date("Y-m-d", strtotime(OAPOLICY_START_DATE)));
	
	define('YEARS_UNDER_OA_POLICY', range(OAPOLICY_START_YEAR, CURRENT_YEAR));
	
	define('PUBLICATION_TRACKING_START_YEAR', date("Y-m-d", strtotime(PUBLICATION_TRACKING_START_DATE)));

	define('YEARS_TO_TRACK', range(PUBLICATION_TRACKING_START_YEAR, CURRENT_YEAR));
