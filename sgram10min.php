<?php

function recentSpectrograms($subnet, $WEBPLOTSPATH)
{
	# Loop from today and over the $MAXDAYS last days, until at least $MINFILES spectrograms found
	$MINFILES = 24;
	$MAXDAYS = 7;
	$filesarray = array();
	$time = now();
	$daysago = 0;
	do {
		list ($year, $month, $day, $hour, $minute) = epoch2YmdHM($time);
		$minute = floorminute($minute);
		$filepath = "$WEBPLOTSPATH/sp/$subnet/$year/$month/$day/2*.png";
		$thisarray = glob($filepath);
		rsort($thisarray);
		if (count($thisarray) > 0) {
			$filesarray = array_merge($filesarray, $thisarray);
		}
		$time = $time - 86400;
		$daysago++;
	} while (count($filesarray) < $MINFILES && $daysago < $MAXDAYS);
	$filesarray = array_slice($filesarray, 0, $MINFILES);
	return $filesarray;
}


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

include('./includes/antelope.inc');
include('./includes/curPageURL.inc');
include('./includes/findprevnextsubnets.inc');

$page_title = 'IceWeb Spectrogram';
$css = array( "css/style2.css" );
$googlemaps = 0;
$js = array();

// Standard XHTML header
include('./includes/header.inc');

?>

<body bgcolor="#FFFFFF">

<!-- JAVASCRIPT TO TOGGLE A DIV -->
<script type="text/javascript">
<!--
    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
//-->
</script>

<?php

	$debugging = 0;
	$showmenu = 1;
	if (!isset($_REQUEST['subnet'])) {
		$showmenu = 1;
	}
	

	# header files
	include('./includes/daysPerMonth.inc');
	include('./includes/mosaicMakerTable.inc');

	# There should be 4 possibilities
	# 1. sgram is set as a CGI parameter, which means arrows have been clicked on. In this case, other parameters should be ignored.
	# 2. only a subnet (from menu) - which should link to latest sgram
	# 3. subnet & date/time parameters - can work out the sgram
	# 4. no CGI parameters - which should display menu only

	# Test the sgram CGI parameter & set sgram
	$sgram = !isset($_REQUEST['sgram'])? NULL : $_REQUEST['sgram'];	
	if (isset($sgram)) {
		### OPTION 1: sgram CGI parameter is set                                     ###
		### This is a request from clicking a left or right arrow on this page       ###
		### We want to set the date/time parameters for the form from basename sgram ### 
		list ($year, $month, $day, $hour, $minute, $subnet) = sgramfilename2parts($sgram);
	}
	else
	{

		# Set date/time variables from CGI parameters if available, otherwise from current date/time
	 	# set current date/time if there is not one already
		$timenow = now(); 
		list ($year, $month, $day, $hour, $minute) = epoch2YmdHM($timenow);
		$minute = floorminute($minute);

		$year = !isset($_REQUEST['year'])? $year : $_REQUEST['year'];
		$month = !isset($_REQUEST['month'])? $month : $_REQUEST['month'];
		$day = !isset($_REQUEST['day'])? $day : $_REQUEST['day'];
		$hour = !isset($_REQUEST['hour'])? $hour : $_REQUEST['hour'];
		$minute = !isset($_REQUEST['minute'])? $minute : $_REQUEST['minute']; 

		# For entry from the form, make sure it has correct number of digits
		$year = mkNdigits($year, 4);
		$month = mkNdigits($month, 2);
		$day = mkNdigits($day, 2);
		$hour = mkNdigits($hour, 2);
		$minute = mkNdigits($minute, 2); 

		# Lets also set subnet
		$subnet = !isset($_REQUEST['subnet'])? NULL : $_REQUEST['subnet'];	

		if (isset($subnet)) {
			if (isset($_REQUEST['year'])) {
				### OPTION 3: subnet and date/time CGI parameters set ###
				### This is a request from the form on this page      ###
				### We can form sgram from those variables            ###
				$sgram =  "$WEBPLOTS/sp/$subnet/$year/$month/$day/".$year.$month.$day."T".$hour.$minute."00.png";	
			}
			else
			{
				### OPTION 2: subnet CGI parameter set, but not date/time                ###
				### The request has come from a link on the AVO internal page menu       ###
				### In this case, we want to select the latest spectrogram available     ###
				#$sgram = NULL;
				$pattern = "$WEBPLOTS/sp/$subnet/$year/$month/$day/$year*.png";
				$files = glob($pattern);
				sort($files);
				$numfiles = count($files);
				$sgram = end($files);
				list ($year, $month, $day, $hour, $minute, $subnet) = sgramfilename2parts($sgram);

			}
		}
		else
		{
			### OPTION 4: no CGI parameters                                ###
			### Someone has just typed this webpage's URL in a web browser ###
			### In this case, we don't display any spectrogram             ###
			### We could default to the current date/time though           ###
			$subnet = "Spurr";
			$pattern = "$WEBPLOTS/sp/$subnet/$year/$month/$day/$year*.png";
			$files = glob($pattern);
			sort($files);
			$numfiles = count($files);
			$sgram = end($files);
			list ($year, $month, $day, $hour, $minute, $subnet) = sgramfilename2parts($sgram);
		}
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
		echo "<hr/>\n";
	}

	# Start form
	echo "<form method=\"get\"> ";


	# Call up the appropriate spectrogram
	if(isset($subnet))
	{

		list ($previousSubnet, $nextSubnet) = findprevnextsubnets($subnet, $subnets);

		if (isset($year) && isset($month) && isset($day) && isset($hour)  && isset($minute)  ) 
		{
			# make sure the date is valid
			if(!checkdate($month,$day,$year)){
				echo "<p>invalid date</p>";
	 		}
			else
			{

				# Search for files
				$pattern = "$WEBPLOTS/sp/$subnet/$year/$month/$day/$year*.png";
				$files = glob($pattern);
				sort($files);
				$numfiles = count($files);
				
				# Is sgram set?
				if (!isset($sgram)) { # from the side menu
				# if do this, have to fix time also else png does not correspond to titles and links
					$sgram = end($files); 				
					list ($year, $month, $day, $hour, $minute, $subnet) = sgramfilename2parts($sgram);
				}
			
				# The current time
				list ($cyear, $cmonth, $cday, $chour, $c1minute) = epoch2YmdHM(now());
				$cminute = floorminute($c1minute);

				# Time parameters of previous spectrogram and its path
				list ($pyear, $pmonth, $pday, $phour, $pminute, $psecs) = addSeconds($year, $month, $day, $hour, $minute, 0, -600);
				$pminute=floorminute($pminute);
				$previous_sgram = "$WEBPLOTS/sp/$subnet/$pyear/$pmonth/$pday/".$pyear.$pmonth.$pday."T".$phour.$pminute."00.png";

				# Time parameters of next spectrogram & its path
				list ($nyear, $nmonth, $nday, $nhour, $nminute, $nsecs) = addSeconds($year, $month, $day, $hour, $minute, 0, 600);
				$nminute=floorminute($nminute);
				$next_sgram = "$WEBPLOTS/sp/$subnet/$nyear/$nmonth/$nday/".$nyear.$nmonth.$nday."T".$nhour.$nminute."00.png";

				# Age of previous spectrogram
				$pAge = timeDiff($pyear, $pmonth, $pday, $phour, $pminute, $psecs, $cyear, $cmonth, $cday, $chour, $cminute, 0);

				# Age of current spectrogram
				$age = $pAge - 600;

				# Age of next spectrogram
				$nAge = $age - 600;


				# Add sound file links & imageMap? 
				$soundOn = 1;
				if ($soundOn) {
					$soundfileroot = str_replace(".png", "", $sgram);
					$soundfiles = glob("$soundfileroot*.wav");
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
						echo "<map name=\"mymap\">\n";
						foreach ($soundfiles as $soundfile) {
							$yUpperLeft = ($imageTop + $panelSizeY * $stationNum);
							$yLowerRight = ($yUpperLeft + $panelSizeY);
							echo "<area shape=\"rect\" href=\"$soundfile\" coords=\"$xUpperLeft,$yUpperLeft  $xLowerRight,$yLowerRight\">\n";
							$stationNum++;
						}
						echo "</map>\n";
					}
				}

				# Find the most recent image
					
				
		
				####### Output a table including arrows and current spectrogram   
				echo "<table>\n";

				# PREVIOUS SUBNET
				echo "<tr>\n";
				echo "\t<td>&nbsp;</td>\n";
				if ($previousSubnet) {
					echo "\t<td align=\"center\"><a href=\"sgram10min.php?subnet=$previousSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute\"><img height=\"50\" width=\"50\" src=\"images/uparrow.gif\" /></a>$previousSubnet</td>\n";
				}
				echo "\t<td>&nbsp;</td>\n";
				echo "</tr>\n";

				#echo "<tr height=800>\n";
				echo "<tr>\n";

				# PREVIOUS SGRAM
				# This style of link passes the sgram CGI parameter: this is why we don't use <a href=><img src=></a>
				if ($pAge >= 0) {
					#echo "\t<td><input type=\"image\" src=\"images/leftarrow.gif\" name=\"sgram\" value=\"$previous_sgram\"><br/></td>\n";
					echo "\t<td><a href=\"sgram10min.php?sgram=$previous_sgram\"><img height=\"50\" width=\"50\" src=\"images/leftarrow.gif\" /></a></td>\n";
				}

				# CURRENT SGRAM
				if ($age >= 0) {
					#echo "\t<td align=center>";
					echo "<h1>$subnet $year/$month/$day $hour:$minute</h1>\n";	
					if (file_exists($sgram) && filesize($sgram) > 0) {
						echo "\t<td align=center>";
						#echo "<img usemap=\"#mymap\" src=\"$sgram\" height=\"900\" width=\"600\"/>";
						echo "<img usemap=\"#mymap\" src=\"$sgram\" />";

						# Control colorbar - toggle's with the toggle_visiblility function
						#echo "<a href=\"#\" onclick=\"toggle_visibility('colorbar');\">Toggle colorbar</a>";
						#echo "<div id=\"colorbar\">\n";
						#echo "<br/><img width=576 src=\"$WEBPLOTS/sp/TreMoRcolorBar.png\" />";
						#echo "</div>\n";
					}
					else
					{
						echo "\t<td align=center height=\"800\" width=\"576\">";
						# Generate list of recent spectrograms
						$sgramfiles = recentSpectrograms($subnet, $WEBPLOTSPATH);
						if (filesize($sgram)>0) {
							echo "<h3>Sorry, that spectrogram image is not available.</h3><br/>";
						} else {
							echo "<h3>Sorry, that spectrogram image has no data.</h3><br/>";
						}
						echo "<h3>Most recent spectrograms:</h3><br/>\n";
						foreach ($sgramfiles as $sgramfile) {
							list ($ryear, $rmonth, $rday, $rhour, $rminute, $rsubnet) = sgramfilename2parts($sgramfile);
							#$sgramfileurl = str_replace($WEBPLOTSPATH, $WEBPLOTS, $sgramfile);		
							$sgramfileurl="sgram10min.php?subnet=$subnet&year=$ryear&month=$rmonth&day=$rday&hour=$rhour&minute=$rminute";
							$size = filesize($sgramfile);
							if ($size > 0) {
								echo "<a href=\"$sgramfileurl\">$ryear/$rmonth/$rday $rhour:$rminute (size: $size bytes)</a><br/>\n";
							} else {
								echo "$ryear/$rmonth/$rday $rhour:$rminute (size: $size bytes)<br/>\n";
							}
						}	
					}
					echo "</td>\n";	
				}
				else
				{
					echo "<td><h3>The spectrogram date/time you requested is in the future.<br/>Click <a href=\"sgram10min.php?subnet=$subnet\"> here </a> for most recent image</h3></td>";
				}

				# NEXT SGRAM
				# This style of link passes the sgram CGI parameter: this is why we don't use <a href=><img src=></a>
				if ($nAge >= 0) {
					#echo "\t<td><input type=\"image\" src=\"images/rightarrow.gif\" name=\"sgram\" value=\"$next_sgram\"><br/></td>\n";
					echo "\t<td><a href=\"sgram10min.php?sgram=$next_sgram\"><img height=\"50\" width=\"50\" src=\"images/rightarrow.gif\" /></a></td>\n";
				}

				echo "</tr>\n";

				# NEXT SUBNET - bottom row
				echo "<tr>\n";
				echo "\t<td>&nbsp;</td>\n";
				if ($nextSubnet) {
					echo "\t<td align=\"center\"><a href=\"sgram10min.php?subnet=$nextSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute\"><img height=\"50\" width=\"50\" src=\"images/downarrow.gif\" /></a>$nextSubnet<br/>\n";
					# Add to logs link
					$link = curPageURL();
					$link = urlencode($link);
					$url = '<a href="https://www.avo.alaska.edu/admin/logs/add_post.php?url=' . $link . '"><h3><center>Add to logs</center></h3></a>';
					echo $url;
					echo "</td>\n";
					echo "\t<td>&nbsp;</td>\n";


				}
				echo "</tr>\n";


				echo "</table>\n";
				### END TABLE

			}
		}
	}
	
	else
	{

		echo "<h1>Welcome to the IceWeb Spectrogram Mosaic Maker!</h1>";

	}

 
	# Show Menu?
	if ($showmenu) {

		echo "<hr/>\n";

		# Start table
		echo "<table>\n\n";

		# Subnet widgit
		echo "<tr><td>Subnet</td>\n";
		echo "<td><select name=\"subnet\"> ";
		echo "<option value=\"$subnet\" SELECTED>$subnet</option>";
		foreach ($subnets as $subnet_option) {
			print "<option value=\"$subnet_option\">$subnet_option</option> ";
		}
		print "</select>";
		echo "</td></tr>\n";
	
		################## START TIME
		echo "<tr><td>Time (UTC):</td><td>\n";
		# start new table inside this cell
		echo "<table><tr>\n";
	
		# Year widgit
		echo "<td>Year:</td>\n";
		echo "<td><input type=\"text\" name=\"year\" value=\"$year\" size=\"4\"> ";
		echo "</td>\n";
	
		# Month widgit
		echo "<td>Month:</td>\n";
		echo "<td><input type=\"text\" name=\"month\" value=\"$month\" size=\"2\"> ";
		echo "</td>\n";
	
		# Day widgit
		echo "<td>Day:</td>\n";
		echo "<td><input type=\"text\" name=\"day\" value=\"$day\" size=\"2\"> ";
		echo "</td>\n";
	
		# Hour widgit
		echo "<td>Hour:</td>\n";
		echo "<td><input type=\"text\" name=\"hour\" value=\"$hour\" size=\"2\"> ";
		echo "</td>\n";
	
		# Minute widgit
		echo "<td>Minute:</td>\n";
		echo "<td><select name=\"minute\"> ";
		echo "<option value=\"$minute\" SELECTED>$minute</option>";
		$minutes = array("00", "10", "20", "30", "40", "50");
		foreach ($minutes as $minute_option) {
			print "<option value=\"$minute_option\">$minute_option</option> ";
		}
		print "</select>";
		echo "</td>\n";
	
		# end this table
		echo "</tr></table>\n";
	
		# end this cell and row
		echo "</td></tr>\n";
		###################### END OF START TIME
	
		# Submit & Reset buttons
		echo "<tr>\n";
		print "<td><input type=\"submit\" name=\"submit\" value=\"Get Spectrogram\"></td>\n";
		$sgramtime = str2epoch("$year/$month/$day $hour:$minute:00");
		list ($syear, $smonth, $sday, $shour, $sminute) = epoch2YmdHM($sgramtime - 3600);
		print "<td><a href=\"mosaicMaker.php?subnet=$subnet&year=$syear&month=$smonth&day=$sday&hour=$shour&minute=$sminute&numhours=2\">Make Mosaic</a></td>\n";
		# following is only an idea, nothing has been implemented, and it might not be worth it since easy to move up & down the network
		#print "<td><a href=\"mosaicMakerAllNetsThisTime.php?year=$syear&month=$smonth&day=$sday&hour=$shour&minute=$sminute\">Arc at this time</a></td>\n";
		echo "</tr>\n";
	
		# End the table
		echo "</table>\n";
	
	}
	else
	{
		# Hide the variables
		echo "<input type='hidden' name='subnet' value=\"$subnet\">\n";
		echo "<input type='hidden' name='year' value=\"$year\">\n";
		echo "<input type='hidden' name='month' value=\"$month\">\n";
		echo "<input type='hidden' name='day' value=\"$day\">\n";
		echo "<input type='hidden' name='hour' value=\"$hour\">\n";
		echo "<input type='hidden' name='minute' value=\"$minute\">\n";
	}



	if ($numsoundfiles > 0) {
		echo "<p>Click on the spectrogram panels above to listen to the seismic data</p>\n"; 
	}
		


	# End form
	echo "</form>\n";

	# Server time
	echo "<hr/>";
	echo "<p><i><font size=1>";		
	#echo "Server processed your request at: $cyear/$cmonth/$cday $chour:$c1minute";		

	# Add diagnostic information from diary in tremor_winston2mat
	$sgramtxtfile = str_replace("png", "txt", $sgram);
	if ( file_exists($sgramtxtfile) ) {
		printf("<br/><a href=$sgramtxtfile>diagnostic data</a>\n"); 
	};
	echo "</font></i>";		
?>


</body>
</html>

