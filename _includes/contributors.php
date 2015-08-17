<?php
include '../php/github-api.php';
use Milo\Github;

$contributors = array();

try
{
	$api = new Github\Api;
	$token = new Github\OAuth\Token("{{site.github_key}}");
	$api->setToken($token);
	foreach ($api->paginator('/repos/amplab/tachyon/contributors') as $response) {
	    array_push($contributors, $api->decode($response));
	}
}
catch(Exception $e)
{
	//echo $e->getMessage();
}

?>
