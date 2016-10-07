<?php

/*
var_dump($_ENV);
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
*/

var_dump(sha1('secret'));

define('SECRET', sha1('secret'));
define('TABLE_REQUEST_LOG', 'request_log');

$db = connect_db(getenv("CLEARDB_DATABASE_URL"));

switch($_SERVER["REQUEST_URI"]) {
	case "/":
		echo "Hi there :) <br/> Send logs to '/log' and list logs at '/list'";
		break;
	case "/log":
		echo "Logged";
		break;
	case "/list":
		echo "TODO: List last X logs";
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
