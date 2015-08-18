<?php

$cachefile = "cache/ticker.json";
$cachetime = 60 * 60 * 5; // 5 hours

if(file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
	$cachedata = file_get_contents($cachefile);
	$ticker = json_decode($cachedata);

	foreach ($ticker as $item) {
	    $calendar = "";
		$linkStart = "";
		$linkEnd = "";
		$footnote = "";
		$source = "";

		if($item->link !== "") {
			$linkStart = "<a href='" . $item->link . "'>";
			$linkEnd = "</a>";

			if($item->type !== "etc" && $item->type !== 'event') {
				switch ($item->type) {
					case 'release':
						$source = "GitHub";
						break;
					case 'meetup':
						$source = "Meetup.com";
						break;
					case 'media':
						$source = parse_url($item->link)['host'];
						break;
					default:
						break;
				}
				$footnote = "<div class='footnote'>
				via " . $source . "
				</div>";
			}
		}
	    if($item->type == "meetup" || $item->type == "event") {
	        $calendar = "<div class='calendar'>
				            <div class='month'>" .  date('M', $item->date / 1000) ."</div>
				            <div class='date'>" .  date('j', $item->date / 1000) . "</div>
						</div>";
	    }
	    echo $linkStart . "
			<div class='item " . $item->type . "'>
				" . $calendar . "
				<div class='content'>
					<h1>" . $item->title . "</h1>
					<p>
						" . $item->desc . "...
					</p>
					" . $footnote . "
				</div>
			</div>" . $linkEnd;
	}

	exit;
}
ob_start();

require 'php/meetup.php';
include 'php/github-api.php';

use Milo\Github;

$ticker = array();
$month = 60 * 60 * 24 * 30 * 3; // three months
$contributors = array();
$stars = 0;

try
{

	$meetup = new Meetup(array(
	    'key' => '{{site.meetup_key}}'
	));
	try {
		$response = $meetup->getEvents(array(
		    'group_urlname' => 'Tachyon',
		    'status' => 'upcoming,past',
			'only' => 'name,time,event_url,description'
		));
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}

	$events = $response->results;
	foreach ($events as $event) {
		if($event->time / 1000 > microtime(true) - $month) {
			if(strlen($event->name) > 45) {
				$event->name = substr($event->name, 0, 45) . "...";
			}
			array_push($ticker, array(
				"title" => $event->name,
				"date" => $event->time,
				"desc" => substr(strip_tags(html_entity_decode($event->description)), 0, 120),
				"link" => $event->event_url,
				"type" => "meetup"
			));
		}
	}
}
catch(Exception $e)
{
    //echo $e->getMessage();
}

try
{
	$url = "https://api.github.com/repos/amplab/tachyon/releases?access_token={{site.github_key}}";
	$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
	$context  = stream_context_create($options);
	$response = file_get_contents($url, false, $context);
	foreach(json_decode($response) as $release) {
		if(strtotime($release->created_at) > microtime(true) - $month) {
			array_push($ticker, array(
				"title" => $release->name,
				"date" => 1000 * strtotime($release->created_at),
				"desc" => substr($release->body, 0, 150),
				"link" => $release->html_url,
				"type" => "release"
			));
		}
	}
}
catch(Exception $e)
{
	//echo $e->getMessage();
}

try
{
	$spreadsheet_url="https://docs.google.com/spreadsheets/d/1Rd9jr1QD5V1-F4EKK4wsqJVBnWJXgs1g5OQDWXGAANQ/pub?gid=0&single=true&output=csv";

	//if(!ini_set('default_socket_timeout',    15)) echo "<!-- unable to change socket timeout -->";

	if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	            $spreadsheet_data[]=$data;
	    }
	    fclose($handle);
		for ($i = 1; $i < count($spreadsheet_data); $i++) {
			array_push($ticker, array(
				"title" => $spreadsheet_data[$i][1],
				"date" => 1000 * strtotime($spreadsheet_data[$i][0]),
				"desc" => substr($spreadsheet_data[$i][2], 0, 150),
				"link" => $spreadsheet_data[$i][3],
				"type" => $spreadsheet_data[$i][4]
			));
		}

	}
	else {
	    die("Problem reading csv");
	}
}
catch(Exception $e)
{
	//echo $e->getMessage();
}

function sortByDate($a, $b) {
	return $b['date'] - $a['date'];
}
usort($ticker, "sortByDate");
foreach ($ticker as $item) {
    $calendar = "";
	$linkStart = "";
	$linkEnd = "";
	$footnote = "";
	$source = "";

	if($item['link'] !== "") {
		$linkStart = "<a href='" . $item['link'] . "'>";
		$linkEnd = "</a>";

		if($item['type'] !== "etc" && $item['type'] !== 'event') {
			switch ($item['type']) {
				case 'release':
					$source = "GitHub";
					break;
				case 'meetup':
					$source = "Meetup.com";
					break;
				case 'media':
					$source = parse_url($item['link'])['host'];
					break;
				default:
					break;
			}
			$footnote = "<div class='footnote'>
			via " . $source . "
			</div>";
		}
	}
    if($item['type'] == "meetup" || $item['type'] == "event") {
        $calendar = "<div class='calendar'>
			            <div class='month'>" .  date('M', $item['date'] / 1000) ."</div>
			            <div class='date'>" .  date('j', $item['date'] / 1000) . "</div>
					</div>";
    }
    echo $linkStart . "
		<div class='item " . $item['type'] . "'>
			" . $calendar . "
			<div class='content'>
				<h1>" . $item['title'] . "</h1>
				<p>
					" . $item['desc'] . "...
				</p>
				" . $footnote . "
			</div>
		</div>" . $linkEnd;
}

try
{
	$api = new Github\Api;
	$token = new Github\OAuth\Token("{{site.github_key}}");
	$api->setToken($token);
	foreach ($api->paginator('/repos/amplab/tachyon/contributors') as $response) {
	    array_push($contributors, $api->decode($response));
	}
	$response = $api->get('/repos/amplab/tachyon');
	$repoInfo = $api->decode($response);
	$stars = $repoInfo->stargazers_count;

	// $url = "https://api.github.com/repos/amplab/tachyon/contributors?access_token={{site.github_key}}";
	// $options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
	// $context  = stream_context_create($options);
	// $response = file_get_contents($url, false, $context);
	// $p = 2;
	// $results = [$response];
	// while(count(json_decode($response)) > 0) {
	// 	$url = "https://api.github.com/repos/amplab/tachyon/contributors?access_token={{site.github_key}}&page=" . $p;
	// 	$response = file_get_contents($url, false, $context);
	// 	array_push($results, $response);
	// }
	// echo count($results);
}
catch(Exception $e)
{
	//echo $e->getMessage();
}
$fp = fopen($cachefile, "w");
//fwrite($fp, ob_get_contents());
fwrite($fp, json_encode($ticker));
fclose($fp);
ob_end_flush();
?>
