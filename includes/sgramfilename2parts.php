<?php
function sgramfilename2parts($sgram)
{
		$datetime = basename($sgram);
		$year = substr($datetime, 0, 4);
		$month = substr($datetime, 4, 2);
		$day = substr($datetime, 6, 2);
		$hour = substr($datetime, 9, 2);
		$minute = substr($datetime, 11,2);
		$pathParts = explode("/", $sgram);

		# This is all about getting the subnet from the full spectrogram path
		$nextpart = 0;
		foreach ($pathParts as $part) {
			if ($nextpart == 1) {
				$subnet = $part;
				break;
			}		
			if ($part == "sp") {
				$nextpart = 1;
			}
		}
		return array($year, $month, $day, $hour, $minute, $subnet); 
}
?>
