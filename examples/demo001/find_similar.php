<?php
$script_path = dirname(__FILE__);

if ($argc < 2) {
	echo "\nUsage: php find_similar.php [filename]\n";
	die();
}


require_once($script_path . "/../../image_compare.php");
require_once($script_path . "/../../image_db_pdo_sqlite.php");

create_db("sqlite:" . $script_path . "/db.sqlite3");

$filename = $argv[1];

if (!file_exists($filename)) die("\nFile does not exist...\n");

$image_comparison_array = image_comparison_array_create($filename);

$similar = image_get_similar($image_comparison_array, 5);

var_dump($similar);



