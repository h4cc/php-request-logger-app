<?php

/*
var_dump($_ENV);
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
*/

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

$conn = new mysqli($server, $username, $password, $db);

var_dump($conn);

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
}
