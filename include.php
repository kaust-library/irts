<?php
	$directoriesToInclude = array("config", "functions", "sources/arxiv", "sources/crossref", "sources/datacite", "sources/doi", "sources/dspace", "sources/europePMC", "sources/github", "sources/googleScholar", "sources/ieee", "sources/local", "sources/repository", "sources/ncbi", "sources/scienceDirect", "sources/sherpa", "sources/scopus", "sources/unpaywall");
	
	foreach($directoriesToInclude as $directory)
	{
		//load files
		$filesToInclude = array_diff(scandir(__DIR__.'/'.$directory), array('..', '.'));
		foreach($filesToInclude as $file)
		{
			if(is_file(__DIR__.'/'.$directory.'/'.$file))
			{
				include_once $directory.'/'.$file;
			}
		}
	}