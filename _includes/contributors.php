<?php

include '../php/github-api.php';
use Milo\Github;
$contributorcache = "../cache/contributors.json";
$cachetime = 60; // 5 hours
$contributors;
if(file_exists($contributorcache) && (time() - $cachetime < filemtime($contributorcache))) {
	$cachedata = file_get_contents($contributorcache);
	$contributors = json_decode($cachedata);
}
else {

	$contributors = array();
	$contributorsMin = array();

	try
	{
		$api = new Github\Api;
		$token = new Github\OAuth\Token("{{site.github_key}}");
		$api->setToken($token);
		foreach ($api->paginator('/repos/amplab/tachyon/contributors') as $response) {
		    array_push($contributors, $api->decode($response));
		}
		foreach($contributors as $contributorPage) {
			foreach($contributorPage as $contributor) {
				array_push($contributorsMin, array(
					'login' => $contributor->login,
					'avatar_url' => $contributor->avatar_url,
					'contributions' => $contributor->contributions
				));
			}
		}
		$contributors = $contributorsMin;
	}
	catch(Exception $e)
	{
		//echo $e->getMessage();
	}

	$fp = fopen($contributorcache, "w");
	fwrite($fp, json_encode($contributorsMin));
	fclose($fp);
}


?>
