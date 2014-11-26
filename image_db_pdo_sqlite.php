<?php

$field_names = array();
foreach(range(0, (6*6) - 1) as $num) {
	$field_names[] = "p$num";
}

function create_db($dbfile) {
	//creates database and tables if they don't already exist
	//sets up SQLite performance settings
	global $field_names;
	global $db;
	$db = new PDO($dbfile);
	$db->exec("PRAGMA temp_store=MEMORY");
	$db->exec("PRAGMA journal_mode=MEMORY");
    $db->exec("PRAGMA synchronous=OFF");
    $db->exec("PRAGMA count_changes=OFF");
	$query = "CREATE TABLE IF NOT EXISTS 'compare' ('img_id' TEXT(36) UNIQUE, 'ts' INTEGER, 'mean' INTEGER, 'dev' INTEGER," . implode(" INTEGER,", $field_names) . " INTEGER)";
	$db->exec($query);
	$query = "CREATE INDEX IF NOT EXISTS compare_index ON compare (" . implode(",", $field_names) . ")";
	$db->exec($query);
}

function image_insert($img_id, $compare_array) {
	//inserts a single image comparison array, img_id is a user generated id 
	//TODO: Prepared statements
	global $db;
	$query = "INSERT OR IGNORE INTO 'compare' VALUES('" . $img_id . "', DATETIME('now'), '" . "'," . $compare_array['mean'] . "," . $compare_array['dev'] . "," . implode(",",$compare_array['diff']) . ")";
	$db->exec($query);
}

function image_insert_multi($img_id, $compare_array, $queue_size=100) {
	//batches up multiple inserts for better performance, queue_size is the max inserts before a flush
	//to manually flush queue, call with parameters (null, null, 0)
	//TODO: Prepared statements

	global $db;
	static $queue = array();
	$pqarray = array();
	if ($img_id !== null) {
		foreach($compare_array['diff'] as $pv) {
			$pqarray[] = "'$pv'";
		}
		$query = "INSERT OR IGNORE INTO 'compare' VALUES('" . $img_id . "', DATETIME('now'), '" . $compare_array['mean'] . "', '" . $compare_array['dev'] . "', " . implode(", ",$pqarray) . ");";
		$queue[] = $query;
	};
	if ( (count($queue) > 0) && (count($queue) > ($queue_size - 1)) ) {
		$db->exec(implode("",$queue));
		$queue = array();
	}
}

function image_get_similar($image_array, $percent) {
	//returns list of img_ids who's pixel values are within a certain 
	//percentage of supplied image comparison array
	global $db;
	$per = ($percent / 100);
	$per_query = array();

	foreach($image_array['diff'] as $i => $iv) {
		$field_name = "p$i";
		$per_diff = ($per * 255) / 2;
		$hv = ceil($iv + $per_diff);
		$lv = ceil($iv - $per_diff);
		$per_query[] = "$field_name >= '$lv'";
		$per_query[] = "$field_name <= '$hv'";
	}
	$query = "SELECT img_id FROM compare WHERE ". implode(" AND ", $per_query);
	$r = $db->query($query);
	$ra = $r->fetchAll(PDO::FETCH_ASSOC);
	$similar_images = array();
	foreach ($ra as $v) {
		$similar_images[] = $v['img_id'];
	}
	return $similar_images;
}


function image_exists($img_id) {
	//returns true or false if an img_id already exists

	global $db;
	$query = "SELECT img_id FROM compare WHERE img_id='$img_id'";
	$r = $db->query($query);
	$ra = $r->fetchAll(PDO::FETCH_NUM);
	if (count($ra) > 0) return true;
	return false;
}

function image_get($img_id) {
	//returns image comparison array for given img_id
	global $db, $field_names;
	$query = "SELECT * FROM compare WHERE img_id='$img_id' LIMIT 1";
	$r = $db->query($query);
	$ra = $r->fetchAll(PDO::FETCH_ASSOC);
	if (count($ra) < 1) return false;
	$ra = $ra[0];
	$image_array = array();
	$image_array['img_id'] = $ra['img_id'];
	$image_array['mean'] = $ra['mean'];
	$image_array['dev'] = $ra['dev'];
	$image_array['diff'] = array();
	foreach($field_names as $field_name) {
		$image_array['diff'][] = $ra[$field_name];
	}
	return $image_array;	
}