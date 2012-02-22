<?php

# header files
include('./includes/antelope.php');
include('./includes/daysPerMonth.php');
include('./includes/mosaicMakerTable.php');
include('./includes/curPageURL.php');
include('./includes/findprevnextsubnets.php');
include('./includes/scriptname.php');
include('./includes/recentSpectrograms.php');
include('./includes/sgramfilename2parts.php');

# Standard XHTML header
#$subnet = !isset($_REQUEST['subnet'])? $subnets[0] : $_REQUEST['subnet'];
$page_title = "$subnet Spectrogram";
#$css = array( "css/reset2.css", "http://www.avo.alaska.edu/includes/admin/admin_test.css", "css/newspectrograms.css", "css/sgram10min.css" );
#$css = array( "http://www.avo.alaska.edu/includes/admin/admin_test.css", "css/newspectrograms.css", "css/sgram10min.css" );
$css = array( "css/newspectrograms.css", "css/sgram10min.css" );
$googlemaps = 0;
$js = array('toggle_menus.js', 'toggle_visibility.js');
include('./includes/header.php');
 
$mosaicurl = !isset($_REQUEST['mosaicurl'])? "" : $_REQUEST['mosaicurl'];	
$mosaicurl = urlencode($mosaicurl);


?>

<body>


<?php

	$debugging = 0;

	# Set subnet
	$subnet = !isset($_REQUEST['subnet'])? Spurr : $_REQUEST['subnet'];	

	if (  (isset($_REQUEST['year'])) && (isset($_REQUEST['month'])) && (isset($_REQUEST['day'])) && (isset($_REQUEST['hour'])) && (isset($_REQUEST['minute']))   ) {
	
		# Get date/time variables from URL variables, then create sgram filename from them
		$year =  $_REQUEST['year'];
		$month =  $_REQUEST['month'];
		$day =  $_REQUEST['day'];
		$hour = $_REQUEST['hour'];
		$minute = $_REQUEST['minute']; 
		$minute = floorminute($minute);
	
		# For entry from the form, make sure it has correct number of digits
		$year = mkNdigits($year, 4);
		$month = mkNdigits($month, 2);
		$day = mkNdigits($day, 2);
		$hour = mkNdigits($hour, 2);
		$minute = mkNdigits($minute, 2); 

		$sgram =  "$WEBPLOTS/sp/$subnet/$year/$month/$day/".$year.$month.$day."T".$hour.$minute."00.png";	
	}
	else
	{
		# Get latest spectrogram for this subnet, and then form date/time variables from its filename
		$sgramfiles = recentSpectrograms($subnet, $WEBPLOTS, 1, 30);
		$sgram = $sgramfiles[0];
		list ($year, $month, $day, $hour, $minute, $subnet) = sgramfilename2parts($sgram);
	}

		
	# Debugging
	if ($debugging == 1) {
		echo "<p>subnet = $subnet</p>\n";
		echo "<p>year = $year</p>\n";
		echo "<p>month = $month</p>\n";
		echo "<p>day = $day</p>\n";
		echo "<p>hour = $hour</p>\n";
		echo "<p>minute = $minute</p>\n";
		echo "<p>sgram = $sgram</p>\n";
		echo "<p>option = $option</p>\n";
		echo "<p>mosaicurl = $mosaicurl</p>\n";
		echo "<hr/>\n";
	}

	if ($mosaicurl != "") {
		echo '<input type="hidden" value="' . $mosaicurl . '" name="mosaicurl" />';
	}

	# Call up the appropriate spectrogram
	list ($previousSubnet, $nextSubnet) = findprevnextsubnets($subnet, $subnets);

	# make sure the date is valid
	if(!checkdate($month,$day,$year)){
		echo "<p>invalid date</p></body></html>";
	}
	else
	{
		# Time parameters of previous spectrogram and its path
		list ($pyear, $pmonth, $pday, $phour, $pminute, $psecs) = addSeconds($year, $month, $day, $hour, $minute, 0, -600);
		$pminute=floorminute($pminute);
		$previous_sgram = "$WEBPLOTS/sp/$subnet/$pyear/$pmonth/$pday/".$pyear.$pmonth.$pday."T".$phour.$pminute."00.png";
		$previous_sgram_url = "$scriptname?subnet=$subnet&year=$pyear&month=$pmonth&day=$pday&hour=$phour&minute=$pminute&mosaicurl=$mosaicurl";

		# Time parameters of next spectrogram & its path
		list ($nyear, $nmonth, $nday, $nhour, $nminute, $nsecs) = addSeconds($year, $month, $day, $hour, $minute, 0, 600);
		$nminute=floorminute($nminute);
		$next_sgram = "$WEBPLOTS/sp/$subnet/$nyear/$nmonth/$nday/".$nyear.$nmonth.$nday."T".$nhour.$nminute."00.png";
		$next_sgram_url = "$scriptname?subnet=$subnet&year=$nyear&month=$nmonth&day=$nday&hour=$nhour&minute=$nminute&mosaicurl=$mosaicurl";

		######################### THINGS THAT DEPEND ON KISKA TIME, WHICH MAY NOT BE CURRENT TIME ####################### 	
		# The current time - albeit from Kiska which might be slow (or fast)
		#list ($cyear, $cmonth, $cday, $chour, $c1minute) = epoch2YmdHM(now());
		#$cminute = floorminute($c1minute);

		# Age of previous spectrogram
		#$pAge = timeDiff($pyear, $pmonth, $pday, $phour, $pminute, $psecs, $cyear, $cmonth, $cday, $chour, $cminute, 0);

		# Age of current spectrogram
		#$age = $pAge - 600;

		# Age of next spectrogram
		#$nAge = $age - 600;
		##################################################################################################################


		# Add sound file links & imageMap? 
		$numsoundfiles = 0;
		$soundfileroot = str_replace(".png", "", $sgram);
		$soundfilelist = $soundfileroot . ".sound";
		if (file_exists($soundfilelist)) { 
			$soundfiles = array();
			$fh = fopen($soundfilelist, 'r');
			while(!feof($fh)) {
				array_push($soundfiles, $WEBPLOTS . "/" . fgets($fh) );
			}
			fclose($fh);

			#$soundfiles = glob("$soundfileroot*.wav");
			$numsoundfiles = count($soundfiles);

			//echo "<p>Got $numsoundfiles sound files</p>";
			if ($numsoundfiles > 0) {
				$imageSizeX = 576 - 57;
				$imageSizeY = 756;
				$imageTop = 45;
				$imageBottom = 97;
				$stationNum = 0;
				$panelSizeY = ($imageSizeY - $imageTop - $imageBottom) / $numsoundfiles;
				$xUpperLeft = 0;
				$xLowerRight = $imageSizeX;
				echo "<map name=\"mymap\" title=\"Click on spectrogram panels to play seismic data as sound (your web browser must be configured to play WAV files)\">\n";
				foreach ($soundfiles as $soundfile) {
					$yUpperLeft = ($imageTop + $panelSizeY * $stationNum);
					$yLowerRight = ($yUpperLeft + $panelSizeY);
					echo "<area shape=\"rect\" href=\"$soundfile\" coords=\"$xUpperLeft,$yUpperLeft  $xLowerRight,$yLowerRight\" alt=\"$soundfile\" />\n";
					$stationNum++;
				}
				echo "</map>\n";
			}
		}

	}
?>

<!-- Create a menu across the top -->
<div id="nav">
        <ul>
	<li title="Toggle menu to reselect time period based on absolute start time and number of hours" onClick="toggle_visibility('menu_absolutetime')">Jump to time</li>
  	<li class="subnetlink">
		<?php
			echo "<a title=\"Jump to the previous subnet along the arc, same time period\" href=\"$scriptname?subnet=$previousSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&mosaicurl=$mosaicurl\">&#9650 $previousSubnet<a/>\n";
		?>
	</li>
  	<li class="subnetpulldown">
		<?php
			# Subnet widgit
                  	echo "<select title=\"Jump to a different subnet\" onchange=\"window.open('?subnet=' + this.options[this.selectedIndex].value + '&year=$year&month=$month&day=$day&hour=$hour&minute=$minute', '_top')\" name=\"subnet\">\n";
			echo "\t\t\t<option value=\"$subnet\" SELECTED>$subnet</option>\n";
			foreach ($subnets as $subnet_option) {
				print "\t\t\t<option value=\"$subnet_option\">$subnet_option</option>\n";
			}
			print "\t\t</select>\n";
		?>
	</li>
  	<li class="subnetlink">
		<?php
			echo "<a title=\"Jump to the next subnet along the arc, same time period\" href=\"$scriptname?subnet=$nextSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&mosaicurl=$mosaicurl\">&#9660 $nextSubnet<a/>\n";
		?>
	</li>
  	<li>
		<?php
			echo "<a title=\"Jump back in time 10 minutes\" href=\"$previous_sgram_url\">&#9668 Earlier</a>\n";
		?>
	</li>
  	<li>
		<?php
			echo "<a title=\"Jump forward in time 10 minutes\" href=\"$next_sgram_url\">&#9658 Later</a>\n";
		?>
	</li>
  	<li>
		<?php
		        echo "<a title=\"Jump to the most recent spectrogram for $subnet\" href=\"$scriptname?subnet=$subnet&mosaicurl=$mosaicurl\">Latest</a>\n";
		?>
	</li>
	<li title="Return to last spectrogram mosaic. This is useful when performing twice-daily seismicity checks.">
		<?php
                	$sgramtime = str2epoch("$year/$month/$day $hour:$minute:00");
                	list ($syear, $smonth, $sday, $shour, $sminute) = epoch2YmdHM($sgramtime - 3600);
			if ($mosaicurl == "") {
                		print "<a href=\"mosaicMaker.php?subnet=$subnet&year=$syear&month=$smonth&day=$sday&hour=$shour&minute=$sminute&numhours=2\">Mosaic</a>\n";
			} else {
                		print "<a href=\"".urldecode($mosaicurl)."\">Mosaic</a>\n";
			}
		?>
	</li>
	<li title="Permanent link to this spectrogram" onClick="toggle_visibility('show_url')">Permalink</li>
        </ul>
</div>
<p/>
<div id="show_url" class="hidden">
	<table class="center" border=0><tr><td align="center">
		<?php
			# Show URL
			$link = curPageURL();
			$loc = strpos($link, "mosaicurl");
			if ($loc !== FALSE) {
				$link = substr($link, 0, $loc - 1);
			}
			echo "The permanent link to this web page is: <br/><font color='blue'>$link</font><br/n> ";
                        $link = urlencode($link);
                        $url = '<p/><table border=0 title="Create an AVO log post with this URL embedded in it"><tr><td><a class="button" href="https://www.avo.alaska.edu/admin/logs/add_post.php?url=' . $link . '" target=\"logs\">Add log post</a></td></tr></table>';
                        echo "$url\n";
		?>
	</td></tr></table>

</div>
<form method="get" id="menu_absolutetime" class="hidden">

        <table class="center" border=0>
                <?php
                        echo "<tr>\n";

                                echo "\t\t\t<td title=\"Enter end time for the spectrogram (UTC)\"><b>End time: </b>";
						
                                                # Year widgit
                                                echo "Year:";
                                                echo "<input type=\"text\" name=\"year\" value=\"$year\" size=\"4\" >";

                                                # Month widgit
                                                echo "Month:";
                                                echo "<input type=\"text\" name=\"month\" value=\"$month\" size=\"2\">";

                                                # Day widgit
                                                echo "Day:";
                                                echo "<input type=\"text\" name=\"day\" value=\"$day\" size=\"2\" >";

                                                # Hour widgit
                                                echo "Hour:";
                                                echo "<input type=\"text\" name=\"hour\" value=\"$hour\" size=\"2\" >";

                                                # Minute widgit
                                                echo "Minute:";
                                                echo "<select name=\"minute\">";
                                                echo "<option value=\"$minute\" SELECTED>$minute</option>";
                                                $minutes = array("00", "10", "20", "30", "40", "50");
                                                foreach ($minutes as $minute_option) {
                                                        print "<option value=\"$minute_option\">$minute_option</option>\n";
                                                }
                                                print "</select>";


                                # end this cell
                                echo "</td>\n";


                        	# Submit button
				echo "<input type=\"hidden\" name=\"subnet\" value=\"$subnet\">\n";
            			print "\t\t\t<td title=\"Redraw spectrogram with end time given here\"><input type=\"submit\" name=\"submit\" value=\"Go\"></td>\n";

                	echo "\t\t</tr>\n";

                ?>
        </table>

</form>
<p/>
<div id="spectrogram">
<?php
 
	# CURRENT SGRAM
	echo "<table class=\"center\" border=0>\n";
        $utchhmm_start  = sprintf("%02d:%02d", $phour, $pminute);
        date_default_timezone_set('UTC');
        $utcepoch_start = mktime($phour,$pminute,0,$pmonth,$pday,$pyear);
        $utcepoch_end = mktime($hour,$minute,0,$month,$day,$year);
        date_default_timezone_set('US/Alaska');
        $localtime = localtime($utcepoch_start, true); # Cannot just use t
        $localtime_end = localtime($utcepoch_end, true); # Cannot just use t
        $localtimelabel = sprintf("%4d/%02d/%02d %02d:%02d - %02d:%02d",$localtime[tm_year]+1900,$localtime[tm_mon]+1,$localtime[tm_mday],$localtime[tm_hour],$localtime[tm_min],$localtime_end[tm_hour],$localtime_end[tm_min]);

	echo "\t<tr><td title=\"Spectrogram time range in UTC. Equivalent local time range: $localtimelabel\"><h1>$subnet $pyear/$pmonth/$pday $phour:$pminute - $hour:$minute</h1></td></tr>\n";	
	$sgramFound = 0;
	if (file_exists($sgram)) {
		if (filesize($sgram) > 0) {
			$sgramFound = 1;
			echo "\t<tr>";
			echo "<td>\n";
			echo "<img usemap=\"#mymap\" src=\"$sgram\" />";
			echo "</td>\n";

			# Colorbar div
			echo "<td>\n";
			echo "<div id=\"colorbar\" class=\"hidden\">\n";
			echo "<br/><img src=\"images/colorbar3.png\" />";
			echo "</div>\n";
			echo "</td>\n";
			echo "</tr>\n";

			# Buttons
			echo "<tr>\n";
			echo "<td>\n";

				# Here is the colorbar button
				echo "<a class=\"button\" href=\"#\" onclick=\"toggle_visibility('colorbar');\">Toggle colorbar</a>";

				# Diagnostic data		
				$sgramtxtfile = str_replace("png", "txt", $sgram);
				if ( file_exists($sgramtxtfile) ) {
					printf("<a class=\"button\" href=$sgramtxtfile>Diagnostic data</a>\n"); 
				};

			echo "</td>\n";
			echo "</tr>\n";
		}
	}

	if ($sgramFound == 0) {
		echo "\t<tr><td>\n";
		echo "<h3>Sorry, that spectrogram image is not available.</h3><br/>";

		# Generate list of recent spectrograms
		$sgramfiles = recentSpectrograms($subnet, $WEBPLOTS, 24, 7);
		echo "<h3>The most recent spectrograms are:</h3><br/>\n";
		foreach ($sgramfiles as $sgramfile) {
			list ($ryear, $rmonth, $rday, $rhour, $rminute, $rsubnet) = sgramfilename2parts($sgramfile);
			$sgramfileurl="$scriptname?subnet=$subnet&year=$ryear&month=$rmonth&day=$rday&hour=$rhour&minute=$rminute&mosaicurl=$mosaicurl";
			$size = filesize($sgramfile);
			if ($size > 0) {
				echo "<a href=\"$sgramfileurl\">$ryear/$rmonth/$rday $rhour:$rminute (size: $size bytes)</a><br/>\n";
			} else {
				echo "$ryear/$rmonth/$rday $rhour:$rminute (size: $size bytes)<br/>\n";
			}
		}	
		echo "</td></tr>\n";
	}

	echo "</table>\n";
?>

<br/>

<script language="Javascript" src="includes/hitcounter.php?page=sgram10min"><!--
//--></script>
<script language="Javascript" src="includes/hitcounter_unique.php?page=sgram10min"><!--
//--></script>



</body>
</html>

