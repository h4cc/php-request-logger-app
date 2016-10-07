<?php

/*
var_dump($_ENV);
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
*/

define('SECRET', sha1('secret')); // e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4
define('TABLE_REQUEST_LOG', 'request_log');

$db = connect_db(getenv("CLEARDB_DATABASE_URL"));

switch($_SERVER["REQUEST_URI"]) {
	case "/":
		echo "Hi there :) <br/> Send logs to '/log' and list logs at '/list'";
		break;
	case "/log":
		log_request($db, TABLE_REQUEST_LOG);
		echo "Logged";
		break;
	case "/list":
		list_requests($db, TABLE_REQUEST_LOG);
		break;
	case "/create-table/".SECRET:
		create_table($db, TABLE_REQUEST_LOG);
		echo "OK";
		break;
}


die();

//--- Internals ---

function connect_db($url) {
	$url = parse_url($url);

	$server = $url["host"];
	$username = $url["user"];
	$password = $url["pass"];
	$db = substr($url["path"], 1);

	$db = new mysqli($server, $username, $password, $db);
	
	return $db;
}

function create_table($db, $table) {
	$sql = "
		CREATE TABLE `".$table."` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			`request` TEXT NOT NULL ,
			PRIMARY KEY ( `id` )
		);
	";
	$db->query($sql);
}

function log_request($db, $table) {
	$data = [
		'GET' => $_GET,
		'POST' => $_POST,
		'COOKIE' => $_COOKIE,
		'FILES' => $_FILES,
		'SERVER' => $_SERVER,
		'BODY' => file_get_contents('php://input'),
	];
	$sql = "INSERT INTO `".$table."` (`request`) VALUES ('".$db->real_escape_string(json_encode($data))."');";
	$db->query($sql);
}

function list_requests($db, $table) {
	$sql = "SELECT * FROM `".$table."` ORDER BY id DESC LIMIT 100;";
	
	$result = $db->query($sql);
	$rows = $result->fetch_assoc();
	
	if(!$rows) {
		return;
	}

	foreach($rows as $row) {
		var_dump($row);
	}
}
