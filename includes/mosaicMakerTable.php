<?php
function mosaicMaker($subnet, $year, $month, $day, $hour, $minute, $numhours, $plotsPerRow, $WEBPLOTS) {
	$timenow = now(); #################### KISKA TIME #########################

        # generate the epoch time for the start date/time requested
        #$starttime = mktime($hour, $minute, 0, $month, $day, $year); # get local/utc conversion issues
        $starttime = str2epoch("$year/$month/$day $hour:$minute:00");

       	# work out the difference in seconds
        $starthour = (($timenow - $starttime) / 3600);
        $endhour   = (($timenow - $starttime) / 3600) - $numhours;

	#if ($starthour < 0) {
	#	$starthour = 0;
	#}

	#if ($endhour < 0) {
	#	$endhour = 0;
	#}

	$numMins = 10;

	#$starttime = now() - $starthour * 60 * 60 ;
	#list ($year, $month, $day, $hour, $minute) = epoch2YmdHM($starttime);

	$stoptime = now() - $endhour * 60 * 60 ;
	list ($year_stop, $month_stop, $day_stop, $hour_stop, $minute_stop) = epoch2YmdHM($stoptime);

	list ($year_now, $month_now, $day_now, $hour_now, $minute_now) = epoch2YmdHM($timenow);

	$title = sprintf("%s %4d/%02d/%02d %02d:%02d",$subnet, $year, $month, $day, $hour, $minute );
	printf("<h1 title=\"The start date/time of the spectrogram mosaic (UTC)\" align=\"center\">%s %4d/%02d/%02d %02d:%02d (%dh %02dm ago)</h1>\n",$subnet, $year, $month, $day, $hour, $minute, $starthour, (60*$starthour)%60 );
	echo "<table class=\"center\">\n";

	$c = 0;
	$latestAge = "?";
	$firstRow = 1;

	for ( $time = $starttime + ($numMins * 60); $time < $stoptime; $time += $numMins * 60) {

		# Get the end date and time for the current image
		list ($year, $month, $day, $hour, $minute) = epoch2YmdHM($time);
		$floorminute = floorminute($minute);
		$timestamp = sprintf("%04d%02d%02dT%02d%02d",$year ,$month, $day, $hour, $floorminute) . "00";

		# Create labels for end hour/minute
		$hhmm = sprintf("%02d:%02d", $hour, $floorminute);

		# Get the start date and time for the current image
		list ($syear, $smonth, $sday, $shour, $sminute) = epoch2YmdHM($time - $numMins * 60);
		$floorsminute= floorminute($sminute);

		# Create labels for start hour/minute
		$rowstarthhmm  = sprintf("%02d:%02d", $shour, $floorsminute);
		date_default_timezone_set('UTC');
		$floorepochUTC = mktime($shour,$sminute,0,$smonth,$sday,$syear);
		date_default_timezone_set('US/Alaska');
		$localtime = localtime($floorepochUTC,true); # Cannot just use time (see above vairable) here since it is now "floored"
		$rowstartlocalhhmm = sprintf("%4d/%02d/%02d %02d:%02d",$localtime[tm_year]+1900,$localtime[tm_mon]+1,$localtime[tm_mday],$localtime[tm_hour],$localtime[tm_min]); 
	
		# Set the link to the big image file
		$sgramphplink = "sgram10min.php?year=$year&month=$month&day=$day&hour=$hour&minute=$floorminute&subnet=$subnet&mosaicurl=".urlencode(curPageURL());

		# work out age of this latest data in this image
		if (($timenow - $time) < 24*60*60) {
			$now = strtotime("$year_now-$month_now-$day_now $hour_now:$minute_now:00");
			$tim = strtotime("$year-$month-$day $hour:$floorminute:00");
			$ageSecs = $now - $tim;
			$ageHours = floor($ageSecs / 3600);
			$ageMins = floor(($ageSecs - (3600 * $ageHours)) / 60);
			$ageStr = sprintf("%dh%02dm", $ageHours, $ageMins);

			if ($ageSecs < 0) {
				$ageHours = floor((-$ageSecs) / 3600);
				$ageMins = floor(((-$ageSecs) - (3600 * $ageHours)) / 60);
				$ageStr = sprintf("-%dh%02dm", $ageHours, $ageMins);

			}
		}

		# (ROW STARTS HERE)
		if (($c % $plotsPerRow)==0) {
			$rowFinished = 0;
			#echo "<br/>\n";
			if ($firstRow==0) {
				echo "<tr class=\"mosaicblankrow\"><td>&nbsp;</td></tr>\n";
			} else {
				$firstRow = 0;
			}
			echo "<tr><td title=\"Start time for this row (UTC). Local time is $rowstartlocalhhmm\">$rowstarthhmm</td>\n";
		}

		# CELL STARTS HERE 			
		#$small_sgram = "$WEBPLOTS/sp/$subnet/$year/$month/$day/small_$timestamp.png";
		$small_sgram = "$WEBPLOTS/sp/$subnet/$year/$month/$day/thumb_$timestamp.png";
		if (file_exists($small_sgram)) {
			$latestAge = $ageStr;
			echo "<td class=\"tdimg\"><a href=$sgramphplink><img src=$small_sgram></a></td>\n";
		} else {
			echo "<td class=\"tdimg\"><a href=$sgramphplink>[no data]</a></td>\n";
		}

		# CELL ENDS HERE

		if (($c % $plotsPerRow)==($plotsPerRow-1)) {
			# ROW ENDS HERE
			date_default_timezone_set('UTC');
			$floorepochUTC = mktime($hour,$minute,0,$month,$day,$year);
			date_default_timezone_set('US/Alaska');
			$localtime = localtime($floorepochUTC,true); # Cannot just use time (see above vairable) here since it is now "floored"
			$rowendlocalhhmm = sprintf("%4d/%02d/%02d %02d:%02d",$localtime[tm_year]+1900,$localtime[tm_mon]+1,$localtime[tm_mday],$localtime[tm_hour],$localtime[tm_min]); 
			echo "<td title=\"End time for this row (UTC). Local time is $rowendlocalhhmm\">$hhmm</td>\n";
			$rowFinished = 1;

		}
		

		$c++;
	}

	if ($rowFinished == 0) {
		echo "<td></td></tr>\n";
	}
	echo "</table>\n";


	printf("<h1 title=\"The end date/time of the spectrogram mosaic (UTC)\" align=\"center\">%s %4d/%02d/%02d %02d:%02d (%dh %02dm ago)</h1>\n",$subnet, $year, $month, $day, $hour, $minute, $endhour, (60*$endhour)%60 );
	$title .= sprintf("- %4d/%02d/%02d %02d:%02d",$year, $month, $day, $hour, $minute );

	return $title;

}

function epoch2YmdHM($e) {
	$numMins=10;
	$year = epoch2str($e, "%Y", "UTC");
	$month = epoch2str($e, "%m", "UTC");
	$day = epoch2str($e, "%d", "UTC");
	$hour = epoch2str($e, "%H", "UTC");
	$minute = epoch2str($e, "%M", "UTC");

	return array($year, $month, $day, $hour, $minute);
}

function floorMinute($minute) {
	$numMins=10;
	$floorminute = floor($minute / $numMins) * $numMins;
	$floorminute = mkNdigits($floorminute, 2);
	return $floorminute;
} 

function mkNdigits($str, $N) {
	while (strlen($str) < $N) 
	{
		$str = "0".$str;
	}
	return $str;
}
function addSeconds($y,$m,$d,$h,$i,$s,$secsToAdd) {
	$t = strtotime("$y/$m/$d $h:$i:$s");
	$t = $t + $secsToAdd;
	$y = date('Y', $t);
	$m = date('m', $t);
	$d = date('d', $t);
	$h = date('H', $t);
	$i = date('i', $t);
	$s = date('s', $t);
	return array($y, $m, $d, $h, $i, $s);
}
function timeDiff($y1, $m1, $d1, $h1, $i1, $s1, $y2, $m2, $d2, $h2, $i2, $s2) {
	$t1 = strtotime("$y1/$m1/$d1 $h1:$i1:$s1");
	$t2 = strtotime("$y2/$m2/$d2 $h2:$i2:$s2");
	return ($t2 - $t1);


}
?>
	
