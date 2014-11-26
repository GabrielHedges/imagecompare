<?php
$script_path = dirname(__FILE__);

require_once($script_path . "/../../image_compare.php");
require_once($script_path . "/../../image_db_pdo_sqlite.php");

create_db("sqlite:" . $script_path . "/db.sqlite3");

$files = glob($script_path . "/images/*.jpg");
foreach($files as $file) {
	$filename = basename($file);
	if (!image_exists($filename)) {
		echo ("Inserting: $filename\n");
		image_insert_multi($filename, image_comparison_array_create($file), 10);
	} else {
		echo ("Exists: $filename\n");
	}
}
image_insert_multi(null, null, 0); // flush
