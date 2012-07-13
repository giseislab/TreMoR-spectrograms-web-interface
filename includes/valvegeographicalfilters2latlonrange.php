<?php
function valvegeographicalfilters2latlonrange($subnet)  {
	$minlon = 0.0; $maxlon = 0.0; $minlat = 0.0; $maxlat = 0.0;
	$filterslink = "http://avosouth.wr.usgs.gov/valve3.4/filters.txt";
	$lines = file($filterslink);
	$matchlines = preg_grep("/$subnet/", $lines);
	if (count($matchlines)==1) {
		$linenum = 0;
		foreach ($matchlines as $line) {
			$linenum++;
			list ($subnet, $lonlatstr) = explode(":", $line);
			list ($minlon, $maxlon, $minlat, $maxlat) = explode(",", $lonlatstr);
			$maxlat = rtrim($maxlat);
		}
	} else {
	}
	return array($minlon, $maxlon, $minlat, $maxlat);
}
?>
