<?php

require_once 'SecureHandler.php';
session_start();

// Load config
if (file_exists('../includes/config/tp.config.php')) {
    include_once '../includes/config/tp.config.php';
} elseif (file_exists('./includes/config/tp.config.php')) {
    include_once './includes/config/tp.config.php';
} else {
    throw new Exception("Error file '/includes/config/tp.config.php' not exists", 1);
}

require_once $SETTINGS['cpassman_dir'].'/includes/config/settings.php';
require_once $SETTINGS['cpassman_dir'].'/includes/config/tp.config.php';

include_once $SETTINGS['cpassman_dir'].'/install/tp.functions.php';
require_once 'main.functions.php'; //Comment hacking attemp on file mail.functions.php

// connect to the server
require_once $SETTINGS['cpassman_dir'].'/includes/libraries/Database/Meekrodb/db.class.php';

header('Content-Type: application/json; charset=utf-8');
echo $server, $user, $pass, $database, $port;

$pass = 'DBPASSWORD'; //Insert DB Password
DB::$host = $server;
DB::$user = $user;
DB::$password = $pass;
DB::$dbName = $database;
DB::$port = $port;
DB::$encoding = $encoding;
DB::$error_handler = 'db_error_handler';

$link = mysqli_connect($server, $user, $pass, $database, $port);

function uuid() {
	$data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
	$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
	$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
	return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$sql = "
SELECT
	items.id_tree,
	items.label,
	items.description,
	items.pw,
	coalesce(nullif(items.login, ''), items.email, '') AS login,
	items.pw_iv
FROM teampass_items as items
ORDER BY items.label
";

$rows = DB::query($sql);

$result = [];
$collections = [];
$organizationId = 'b0d60ffb-79fc-4933-8c60-2dbbb0a8d811';

function get_full_title($treeId) {
	$name = '';

	// mysql 5.5 не поддерживает recursive cte (да и cte вообще)

	while ($treeId) {
		$rows = DB::query("
			SELECT parent_id, title
			FROM teampass_nested_tree
			WHERE id = $treeId
		");
		if (empty($rows)) {
			break;
		}
		$row = $rows[0];
		$treeId = $row['parent_id'];
		$name = trim($row['title']) . '/' . $name;
	}

	return utf8(trim('TeamPass/' . $name, '/'));
}

function extract_uris($text) {
	$uris = [];
	$pattern = '/\b((?:https?|ftp):\/\/|www\.)[^\s\/$.?#].[^\s]*|\b(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,6}\b|\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?:\d+)?\b/';

	preg_match_all($pattern, $text, $matches);

	foreach ($matches[0] as $match) {
		$uris[] = [
			'match' => null,
			'uri' => $match,
		];
	}

	return $uris;
}

function br2nl($text) {
	$tags = ['<br>', '<br/>', '<br />'];
	return str_replace($tags, PHP_EOL, $text);
}

function utf8($text) {
	return trim(iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($text, 'UTF-8', 'UTF-8')));
}

foreach ($rows as $row) {
	$pw = cryption($row['pw'], "", "decrypt");

	$collection = get_full_title($row['id_tree']);

	if (!$collections[$collection]) {
		$collections[$collection] = [
			'id' => uuid(),
			'organizationId' => $organizationId,
			'name' => $collection,
		];
	}

	$result[] = [
		'type' => 1,
		'organizationId' => $organizationId,
		'collectionIds' => [$collections[$collection]['id']],
		'name' => utf8($row['label']),
		'notes' => utf8(strip_tags(html_entity_decode(br2nl($row['description'])))),
		'login' => [
			'uris' => extract_uris($row['label'] . ' ' . $row['description']),
			'username' => utf8($row['login']),
			'password' => utf8($pw['string']),
		],
	];
}

echo json_encode([
	'items' => $result,
	'collections' => array_values($collections),
]);

