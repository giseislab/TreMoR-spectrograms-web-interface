<?php
$placesdb = "/usr/local/mosaic/AVO/internal/avoseis/dev/data/places/volcanoes";
if (file_exists($placesdb)) {
	$db = ds_dbopen($placesdb, 'r');
	if (file_exists($placesdb.".places")) {
		$db = dblookup($db, "", "places", "", "");
		$nrecs=dbnrecs($db);
		#print "Records = $nrecs\n";
		if ($nrecs>0) {
			for ($db[3] = 0; $db[3] < $nrecs; $db[3]++) {
				$subnets[$db[3]] = dbgetv($db, "place");
			}
		} else {
			#print "No records found\n";
		}
	} else {
		#print "places table not found\n";
	}
	ds_dbclose($db);
} else {
	#print "$placesdb not found\n";
}

?>
