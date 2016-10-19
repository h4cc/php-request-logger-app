<?php

/*
var_dump($_ENV);
var_dump($_SERVER);
var_dump($_GET);
var_dump($_POST);
*/

define('SECRET', sha1('secret')); // e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4
define('TABLE_REQUEST_LOG', 'request_log');
define('KEEP_ENTRIES', 100);

$db = connect_db(getenv("CLEARDB_DATABASE_URL"));

remove_old($db, TABLE_REQUEST_LOG, KEEP_ENTRIES);

switch($_SERVER["REDIRECT_URL"]) {
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
	case "/show":
		show_request($db, TABLE_REQUEST_LOG, (int)$_GET['id']);
		break;
	case "/create-table/".SECRET:
		create_table($db, TABLE_REQUEST_LOG);
		echo "OK";
		break;
	case "/drop-table/".SECRET:
		drop_table($db, TABLE_REQUEST_LOG);
		echo "OK";
		break;
	default:
		echo "404";
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

function remove_old($db, $table, $keep = 500) {

    $sql = "
        select id 
        from `".$table."` 
        order by id desc 
        limit ".(int)$keep."
    ";

    $result = $db->query($sql);
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    $ids_keep = [];
    foreach($rows as $row) {
        $ids_keep[] = $row['id'];
    }

    $sql = "
      delete from `".$table."` 
      where id in (".implode(',', $ids_keep).")
    ";
    $db->query($sql);
}

function create_table($db, $table) {
	$sql = "
		CREATE TABLE `".$table."` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
			`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			`request` TEXT NOT NULL ,
			PRIMARY KEY ( `id` )
		);
	";
	$db->query($sql);
}

function drop_table($db, $table) {
	$sql = "
		DROP TABLE `".$table."`;
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
	$sql = "SELECT * FROM `".$table."` ORDER BY id DESC LIMIT 110;";
	
	$result = $db->query($sql);
	$rows = $result->fetch_all(MYSQLI_ASSOC);
	
	html_start();
	echo "<table border=1>";
		echo "
			<tr>
				<th>ID</th>
				<th>Timestamp</th>
				<th>Link</th>
			</tr>
		";
	foreach($rows as $row) {
		echo "
			<tr>
				<td>",$row['id'],"</td>
				<td>",$row['timestamp'],"</td>
				<td>
					<a href=\"/show?id=",$row['id'],"\">Show</a>
				</td>
			</tr>
		";
	}
	
	echo "</table>";
	html_end();
}

function show_request($db, $table, $id) {
	$sql = "SELECT * FROM `".$table."` WHERE id = ".(int)$id.";";
	
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	
	html_start();
	echo "<pre>";
	echo htmlentities(var_export(json_decode($row['request'], true), true));
	echo "</pre>";
	html_end();
}

function html_start() {
	header('Content-Type: text/html');
	echo "<html><head></head><body>";
}

function html_end() {
	echo "</body></html>";
}
