<?php
error_reporting(0);
stream_context_set_default([
	'http' => [
		// 'proxy' => 'localhost:8888',
		'protocol_version' => 1.1
	]
]);

$config = json_decode(file_get_contents('config.json'));

$boardsUrl = file_get_contents('https://api.trello.com/1/members/me/boards?key=' . $config->apiKey . '&token=' . $config->apiToken);
$boards = json_decode($boardsUrl);

$items = [];

for ($i=0; $i < count($boards); $i++) { 

	$filename = downloadBoardThumb($boards[$i]);
	if (is_null($filename)) {
		$filename = "8E73397D-32ED-4967-9597-8389C8573230.png";
	}

	$items[$i]->title = $boards[$i]->name;
	$items[$i]->subtitle = $boards[$i]->url;
	$items[$i]->arg = $boards[$i]->url;
	$items[$i]->uid = $boards[$i]->id;
	$items[$i]->icon->path = $filename;

	if ($boards[$i]->closed == true) {
		$items[$i]->title = "[Closed] " . $items[$i]->title;
	}
}

$result->items = $items;

$result = json_encode($result);

echo $result;


function downloadBoardThumb($board) {
	$thumbUrl = NULL;
	$filename = NULL;
	

	// Check if there is a background and get its URL
	if (!is_null($board->prefs->backgroundImage)) {

		$images = $board->prefs->backgroundImageScaled;
		for ($i=0; $i < count($images); $i++) { 
			if ($images[$i]->width >= 100) {
				$thumbUrl = $images[$i]->url;
				break;
			}
		}

		$filename = getFilename($thumbUrl);

		if (!file_exists($filename)) {
			copy($thumbUrl, $filename);
		}
	}

	return $filename;

}

function getFilename($url) {
	$filename = 'boardthumbs/' . basename($url);
	return $filename;
}

?>