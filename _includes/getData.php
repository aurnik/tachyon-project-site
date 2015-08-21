<?php
require 'php/meetup.php';
include 'php/github-api.php';

use Milo\Github;
$eventscache = "cache/events.json";
$newscache = "cache/news.json";
$contributorcache = "cache/contributors.json";
$statscache = "cache/stats.json";
$cachetime = 60 * 5; // 5 minutes
$contributors = array();
$stats = array();
$news = array();

if(file_exists($eventscache) && (time() - $cachetime < filemtime($eventscache))) {
	// $eventscachedata = file_get_contents($eventscache);
	// $events = json_decode($eventscachedata);
	// $newscachedata = file_get_contents($newscache);
	// $news = json_decode($newscachedata);
	// $contributorcachedata = file_get_contents($contributorcache);
	// $contributors = json_decode($contributorcachedata);
	// $statscachedata = file_get_contents($statscache);
	// $stats = json_decode($statscachedata);

	// foreach ($events as $item) {
	// 	$linkStart = "";
	// 	$linkEnd = "";
	// 	$footnote = "";
	// 	$source = "";
	//
	// 	if($item->link !== "") {
	// 		$linkStart = "<a target='_blank' href='" . $item->link . "'>";
	// 		$linkEnd = "</a>";
	//
	// 		if($item->type == "meetup") {
	// 			$footnote = "<div class='footnote'>via Meetup.com</div>";
	// 		}
	// 	}
    //     $calendar = "<div class='calendar'>
	// 		            <div class='month'>" .  date('M', $item->date / 1000) ."</div>
	// 		            <div class='date'>" .  date('j', $item->date / 1000) . "</div>
	// 				</div>";
	//     echo $linkStart . "
	// 		<div class='item " . $item->type . "'>
	// 			" . $calendar . "
	// 			<div class='content'>
	// 				<h1>" . $item->title . "</h1>
	// 				<p>
	// 					" . $item->desc . "...
	// 				</p>
	// 				" . $footnote . "
	// 			</div>
	// 		</div>" . $linkEnd;
	// }
}
else {

	$events = array();
	$pastNewsLimit = 60 * 60 * 24 * 30 * 3; // three months
	$contributors = array();
	$contributorsMin = array();
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

		$res = $response->results;
		foreach ($res as $event) {
			if($event->time / 1000 > microtime(true) - $pastNewsLimit) {
				if(strlen($event->name) > 45) {
					$event->name = substr($event->name, 0, 45) . "...";
				}
				array_push($events, array(
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

		$response = $api->get('/repos/amplab/tachyon');
		$repoInfo = $api->decode($response);
		$stars = $repoInfo->stargazers_count;
		$commits = 0;
		foreach ($contributorsMin as $contributor) {
			$commits += $contributor['contributions'];
		}
		array_push($stats, array(
			'stars' => $stars,
			'commits' => $commits,
			'contributors' => count($contributorsMin)
		));

		$response = $api->get('/repos/amplab/tachyon/releases');
		$releases = $api->decode($response);
		foreach ($releases as $release) {
			if(strtotime($release->created_at) > microtime(true) - $pastNewsLimit) {
				array_push($news, array(
					"title" => "[RELEASE] " . $release->name,
					"date" => 1000 * strtotime($release->created_at),
					"link" => $release->html_url,
					"type" => "github"
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
		$events_spreadsheet_url = "https://docs.google.com/spreadsheets/d/1Rd9jr1QD5V1-F4EKK4wsqJVBnWJXgs1g5OQDWXGAANQ/pub?output=csv";
		$news_spreadsheet_url = "https://docs.google.com/spreadsheets/d/1R4sQuR4vS_mqIJ67mpoNaYuHno6N_gTJb3g9KadoVEs/pub?output=csv";

		if (($handle = fopen($events_spreadsheet_url, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		            $events_spreadsheet_data[]=$data;
		    }
		    fclose($handle);
			for ($i = 1; $i < count($events_spreadsheet_data); $i++) {
				if(strtotime($events_spreadsheet_data[$i][0]) > microtime(true) - $pastNewsLimit) {
					array_push($events, array(
						"title" => $events_spreadsheet_data[$i][1],
						"date" => 1000 * strtotime($events_spreadsheet_data[$i][0]),
						"desc" => substr($events_spreadsheet_data[$i][2], 0, 150),
						"link" => $events_spreadsheet_data[$i][3],
						"type" => "event"
					));
				}
			}

		}
		else {
		    die("Problem reading csv");
		}

		if (($handle = fopen($news_spreadsheet_url, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		            $news_spreadsheet_data[]=$data;
		    }
		    fclose($handle);
			for ($i = 1; $i < count($news_spreadsheet_data); $i++) {
				if(strtotime($news_spreadsheet_data[$i][0]) > microtime(true) - $pastNewsLimit) {
					array_push($news, array(
						"title" => $news_spreadsheet_data[$i][1],
						"date" => 1000 * strtotime($news_spreadsheet_data[$i][0]),
						"link" => $news_spreadsheet_data[$i][2],
						"type" => "news"
					));
				}
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
	usort($news, "sortByDate");
	usort($events, "sortByDate");
	// foreach ($events as $item) {
	// 	$linkStart = "";
	// 	$linkEnd = "";
	// 	$footnote = "";
	// 	$source = "";
	//
	// 	if($item['link'] !== "") {
	// 		$linkStart = "<a target='_blank' href='" . $item['link'] . "'>";
	// 		$linkEnd = "</a>";
	//
	// 		if($item['type'] == "meetup") {
	// 			$footnote = "<div class='footnote'>via Meetup.com</div>";
	// 		}
	// 	}
    //     $calendar = "<div class='calendar'>
	// 		            <div class='month'>" .  date('M', $item['date'] / 1000) ."</div>
	// 		            <div class='date'>" .  date('j', $item['date'] / 1000) . "</div>
	// 				</div>";
	//     echo $linkStart . "
	// 		<div class='item " . $item['type'] . "'>
	// 			" . $calendar . "
	// 			<div class='content'>
	// 				<h1>" . $item['title'] . "</h1>
	// 				<p>
	// 					" . $item['desc'] . "...
	// 				</p>
	// 				" . $footnote . "
	// 			</div>
	// 		</div>" . $linkEnd;
	// }

	$fp = fopen($eventscache, "w");
	fwrite($fp, json_encode($events));
	fclose($fp);
	$fp = fopen($newscache, "w");
	fwrite($fp, json_encode($news));
	fclose($fp);
	$fp = fopen($contributorcache, "w");
	fwrite($fp, json_encode($contributorsMin));
	fclose($fp);
	$fp = fopen($statscache, "w");
	fwrite($fp, json_encode($stats));
	fclose($fp);
}
?>
