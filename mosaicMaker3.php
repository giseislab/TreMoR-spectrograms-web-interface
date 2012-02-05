<?php
include('./includes/antelope.inc');
$subnet = !isset($_REQUEST['subnet'])? $subnets[0] : $_REQUEST['subnet'];
$page_title = "$subnet Spectrogram Mosaic";
$css = array( "css/sgrams.css" );
$googlemaps = 0;
$js = array();

// Standard XHTML header
include('./includes/header.inc');

?>

<body bgcolor="#FFFFFF">
<script type="text/javascript">
    var visibleMenuID = '';
    function toggle_visibility(id) {
	//document.getElementById('menu_hoursago').style.display = 'none';
	//document.getElementById('menu_absolutetime').style.display = 'none';
       	visibleMenuID = id;
       	var e = document.getElementById(id);
       	if(e.style.display == 'block'){
          	//e.style.display = 'none';
		document.getElementById('menu_hoursago').style.display = 'none';
		document.getElementById('menu_absolutetime').style.display = 'none';
       	}else{
		document.getElementById('menu_hoursago').style.display = 'none';
		document.getElementById('menu_absolutetime').style.display = 'none';
          	e.style.display = 'block';
      	}
    }
</script>



<?php

	# global variables
	$debugging = 0;

	# header files
	include('./includes/daysPerMonth.inc');
	include('./includes/mosaicMakerTable.inc');	
	include('./includes/curPageURL.inc');
	include('./includes/findprevnextsubnets.inc');
	include('./includes/scriptname.inc');

	# Set date/time now
	$timenow = now();
	$currentYear = epoch2str($timenow, "%Y");
	$currentMonth = epoch2str($timenow, "%m");
	$currentDay = epoch2str($timenow, "%d");
	$currentHour = epoch2str($timenow, "%H");
#	$currentMinute = epoch2str($timenow, "%i);


	# Set convenience variables from CGI parameters

	$plotsPerRow = !isset($_REQUEST['plotsPerRow'])? 6 : $_REQUEST['plotsPerRow'];
	$visibleMenuID = !isset($_REQUEST['visibleMenuID'])? 'none' : $_REQUEST['visibleMenuID'];
	if (isset($_REQUEST['starthour'])) {
		$starthour = $_REQUEST['starthour'];
        	$endhour = !isset($_REQUEST['endhour'])? 0 : $_REQUEST['endhour'];
                if ($starthour < $endhour) {
			$tmphour = $endhour;
			$endhour = $starthour;
			$starthour = $tmphour;
		} 
		$timestart = now() - $starthour * 3600;
        	list($year, $month, $day, $hour, $minute) = epoch2YmdHM($timestart);
        	$minute=floorminute($minute);
        	$numhours = $starthour - $endhour;
		$_REQUEST['year'] = $year;
		$_REQUEST['month'] = $month;
		$_REQUEST['day'] = $day;
		$_REQUEST['hour'] = $hour;
		$_REQUEST['minute'] = $minute;
		$_REQUEST['numhours'] = $numhours;
	} else {
		$year = !isset($_REQUEST['year'])? $currentYear : $_REQUEST['year'];
		$month = !isset($_REQUEST['month'])? $currentMonth : $_REQUEST['month'];
		$day = !isset($_REQUEST['day'])? $currentDay : $_REQUEST['day'];
		$hour = !isset($_REQUEST['hour'])? $currentHour : $_REQUEST['hour'];
		$minute = !isset($_REQUEST['minute'])? "00" : $_REQUEST['minute'];
		$numhours = !isset($_REQUEST['numhours'])? 2 : $_REQUEST['numhours'];
	}
		
	# Degugging
	if ($debugging == 1) {
		echo "<p>subnet=$subnet ";
		echo "year=$year ";
		echo "month=$month ";
		echo "day=$day ";
		echo "hour=$hour ";
		echo "minute=$minute ";
		echo "hour=$hour ";
		echo "numhours=$numhours ";
		echo "starthour=$starthour ";
		echo "endhour=$endhour <p>\n ";
		echo "<hr/>\n";
	}
?>

<script type="text/javascript">
	visibleMenuID = <?php echo json_encode($visibleMenuID); ?>;
</script>

<?php
	$scriptname = scriptname();
	if ($debugging == 1) {
        	echo "scriptname = $scriptname, subnet=$subnet, $year/$month/$day $hour:$minute:00, previous=$previousSubnet, next=$nextSubnet\n";
	}
		
        list ($previousSubnet, $nextSubnet) = findprevnextsubnets($subnet, $subnets);
        $numseconds = $numhours * 3600;
        list ($pyear, $pmonth, $pday, $phour, $pminute, $psecs) = addSeconds($year, $month, $day, $hour, $minute, 0, -$numseconds);
        $pminute=floorminute($pminute);

        list ($nyear, $nmonth, $nday, $nhour, $nminute, $nsecs) = addSeconds($year, $month, $day, $hour, $minute, 0, $numseconds);
        $nminute=floorminute($nminute);
?>	

<!-- Create a menu across the top -->
<div id="nav">
        <ul>
	<li onClick="toggle_visibility('menu_hoursago')">Hours ago</li>
	<li onClick="toggle_visibility('menu_absolutetime')">Start time</li>
	<!-- <li onClick="toggle_visibility('arrows')">Arrows</li> -->
  	<li>
		<?php
			echo "<a href=\"$scriptname?subnet=$previousSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours&plotsPerRow=$plotsPerRow\">&#9650 $previousSubnet</a>\n";
		?>
	</li>
  	<li>
		<?php
			# Subnet widgit
			#echo "<select name=\"subnet\"> ";
                  	echo "<select onchange=\"window.open('?subnet=' + this.options[this.selectedIndex].value + '&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours&plotsPerRow=$plotsPerRow', '_top')\" name=\"subnet\">";

			echo "<option value=\"$subnet\" SELECTED>$subnet</option>";
			foreach ($subnets as $subnet_option) {
				print "<option value=\"$subnet_option\">$subnet_option</option> ";
			}
			print "</select>";
		?>
	</li>
  	<li>
		<?php
			echo "<a href=\"$scriptname?subnet=$nextSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours&plotsPerRow=$plotsPerRow\">&#9660 $nextSubnet</a>\n";
		?>
	</li>
  	<li>
		<?php
         		#echo "<a href=\"$scriptname?subnet=$subnet&year=$pyear&month=$pmonth&day=$pday&hour=$phour&minute=$pminute&numhours=$numhours\">&#9668 $pyear/$pmonth/$pday $phour:$pminute</a>\n";
         		echo "<a href=\"$scriptname?subnet=$subnet&year=$pyear&month=$pmonth&day=$pday&hour=$phour&minute=$pminute&numhours=$numhours&plotsPerRow=$plotsPerRow\">&#9668 Earlier</a>\n";
		?>
	</li>
  	<li>
		<?php
         		#echo "<a href=\"$scriptname?subnet=$subnet&year=$nyear&month=$nmonth&day=$nday&hour=$nhour&minute=$nminute&numhours=$numhours\">&#9658 $nyear/$nmonth/$nday $nhour:$nminute</a>\n";
         		echo "<a href=\"$scriptname?subnet=$subnet&year=$nyear&month=$nmonth&day=$nday&hour=$nhour&minute=$nminute&numhours=$numhours&plotsPerRow=$plotsPerRow\">&#9658 Later</a>\n";
		?>
	</li>
  	<li>
		<?php
		        echo "<a href=\"$scriptname?subnet=$subnet&starthour=$numhours&endhour=0&plotsPerRow=$plotsPerRow\">&#9658&#9658Now</a>\n";
		?>
	</li>
	<li>
		<?php
			# Add to logs link
			$link = curPageURL();
			#$link = "<a href=\"$link\">Spectrogram mosaic $subnet $year/$month/$day $hour:$minute hours=$numhours</a>"; # Logs do not understand HTML apparently
			$link = urlencode($link);
			$url = '<a href="https://www.avo.alaska.edu/admin/logs/add_post.php?url=' . $link . '">Add to logs</a>';
			echo $url;
		?>
	</li>
	<li>
		<?php
			# plots per row widgit
                  	echo "Plots/Row<select onchange=\"window.open('?subnet=$subnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours&plotsPerRow=' + this.options[this.selectedIndex].value, '_top')\" name=\"plotsPerRow\">";

			echo "<option value=\"$plotsPerRow\" SELECTED>$plotsPerRow</option>";
			foreach (array(1,2,3,4,5,6,7,8,9,10,11,12) as $ppr_option) {
				print "<option value=\"$ppr_option\">$ppr_option</option> ";
			}
			print "</select>";
		?>	
	</li>	

        </ul>
</div>
<?php
	$plotMosaic = 0; 
	if(isset($_REQUEST["subnet"])) {
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"]) && isset($_REQUEST["day"]) && isset($_REQUEST["hour"])  && isset($_REQUEST["numhours"])  ) {

			# make sure the date is valid
			if(!checkdate($month,$day,$year)){
				echo "<p>invalid date</p>";
 			}
			else
			{

				# generate the epoch time now
				$timenow   = time(); # seconds # get utc/local conversion issues
				$timenow = now();
	
				# generate the epoch time for the start date/time requested
				#$starttime = mktime($hour, $minute, 0, $month, $day, $year); # get local/utc conversion issues
				$starttime = str2epoch("$year/$month/$day $hour:$minute:00");
			#	echo "<p>Starttime: $starttime, timenow: $timenow</p>";	
				if ($timenow > $starttime) {
				
					# work out the difference in seconds
					$starthour = (($timenow - $starttime) / 3600);
					$endhour   = (($timenow - $starttime) / 3600) - $numhours;
					$plotMosaic = 1;
				}
				else
				{
					echo "<p>Date/Time entered must be in the past</p>\n";
				}
			}
		} 
	}
	else
	{
		echo "<h1>Welcome to the Spectrogram Mosaic Maker!</h1><p>This page provides links to PNG files of 10-minute spectrograms pre-generated by the \"TreMoR\" system.</p>";
	}
?>


			<form method="get" id="menu_hoursago" class="hidden">
				<table>
					<?php
						# Subnet widgit
						echo "<tr>\n";
							echo "<td>Subnet</td>\n";
							echo "<td><select name=\"subnet\"> ";
							echo "<option value=\"$subnet\" SELECTED>$subnet</option>";
							foreach ($subnets as $subnet_option) {
								print "<option value=\"$subnet_option\">$subnet_option</option> ";
							}
							print "</select>";
							echo "</td>\n";
						echo "</tr>\n";

						# Start hour widgit
						echo "<tr>\n";
						        echo "<tr><td>Start</td>\n";
						        printf("<td><input type=\"text\" name=\"starthour\" value=\"%.0f\" size=\"4\"> ",$starthour);
						        echo " hours ago </td>\n";
						echo "</tr>\n";
	
						# End hour widgit
						echo "<tr>\n";
						        echo "<td>End</td>\n";
						        printf("<td><input type=\"text\" name=\"endhour\" value=\"%.0f\" size=\"4\"> ",$endhour);
						        echo " hours ago</td>\n";
						echo "</tr>\n";
	
						# Submit & Reset buttons
						echo "<tr>\n";
							print "<td><input type=\"submit\" name=\"submit\" value=\"Make Mosaic\"></td>\n";
						echo "</tr>\n";
					?>
				</table>
			</form>

			<form method="get" id="menu_absolutetime" class="hidden">
				<table>
					<?php
						echo "<tr>\n";

							# Subnet widgit
							echo "<td>Subnet</td>\n";
							echo "<td><select name=\"subnet\"> ";
							echo "<option value=\"$subnet\" SELECTED>$subnet</option>";
							foreach ($subnets as $subnet_option) {
								print "<option value=\"$subnet_option\">$subnet_option</option> ";
							}
							print "</select>";
							echo "</td>\n";

						echo "</tr>\n";


						################## START TIME
						echo " <tr>\n";

							echo "<td>Start time (UTC):</td>\n";
							echo "<td>\n";
								# start new table inside this cell
								echo "<table>\n";
	
									# Year widgit
									echo "<td>Year:</td>\n";
									echo "<td><input type=\"text\" name=\"year\" value=\"$year\" size=\"4\" > ";
									echo "</td>\n";
	
									# Month widgit
									echo "<td>Month:</td>\n";
									echo "<td><input type=\"text\" name=\"month\" value=\"$month\" size=\"2\"> ";
									echo "</td>\n";
	
									# Day widgit
									echo "<td>Day:</td>\n";
									echo "<td><input type=\"text\" name=\"day\" value=\"$day\" size=\"2\" > ";
									echo "</td>\n";
	
									# Hour widgit
									echo "<td>Hour:</td>\n";
									echo "<td><input type=\"text\" name=\"hour\" value=\"$hour\" size=\"2\" > ";
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

							# end this cell
							echo "</td>\n";

						# end row
						echo "</tr>\n";
						###################### END OF START TIME

						# Number of hours widgit
						echo "<tr>\n";
							echo "<td>Number of hours:</td>\n";
							echo "<td><input type=\"text\" name=\"numhours\" value=\"$numhours\" size=\"2\"> ";
							echo "</td>\n";
						echo "</tr>\n";

						# Submit & Reset buttons
						echo "<tr>\n";
							print "<td><input type=\"submit\" name=\"submit\" value=\"Make Mosaic\"></td>\n";
						echo "</tr>\n";
					?>
				</table>
				<script type="text/javascript">
					document.write("<input type='hidden' name='visibleMenuID' value=" + visibleMenuID + ">");
				</script>

			</form>

			<div id="arrows" class="hidden">

				<table border=1><td>
					<table>
						<tr>
							<td>&nbsp;</td>

				                	<td>
								<?php
                							echo "<a href=\"$scriptname?subnet=$previousSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours\"><img height=50 width=50 src=images/uparrow.gif></a>\n";
								?>
							</td>

							<td>&nbsp;</td>
						</tr>
					
						<tr>
							<td>
								<?php	
                							echo "<a href=\"$scriptname?subnet=$subnet&year=$pyear&month=$pmonth&day=$pday&hour=$phour&minute=$pminute&numhours=$numhours\"><img height=50 width=50 src=images/leftarrow.gif></a>\n";
								?>
							</td>

                					<td align=center>
								<?php	
							                echo "<a href=\"$scriptname?subnet=$subnet&starthour=$numhours&endhour=0\">Now</a>\n";
								?>
							</td>
							<td>
								<?php	
                							echo "<a href=\"$scriptname?subnet=$subnet&year=$nyear&month=$nmonth&day=$nday&hour=$nhour&minute=$nminute&numhours=$numhours\"><img height=50 width=50 src=images/rightarrow.gif></a>\n";
								?>
							</td>
						</tr>

						<tr>
							<td>&nbsp;</td>

				                	<td>
								<?php
                							echo "<a href=\"$scriptname?subnet=$nextSubnet&year=$year&month=$month&day=$day&hour=$hour&minute=$minute&numhours=$numhours\"><img height=50 width=50 src=images/downarrow.gif></a>\n";
								?>
							</td>

							<td>&nbsp;</td>
						</tr>
					</table>
				</td></table>
			</div>
<hr/>

<?php
	if ($plotMosaic==1) {
		mosaicMaker($subnet, $starthour, $endhour, $plotsPerRow, $WEBPLOTS);
	}
	else
	{
		echo "<h1>Welcome to the Spectrogram Mosaic Maker!</h1><p>This page provides links to PNG files of 10-minute spectrograms pre-generated by the \"TreMoR\" system.</p>";
	}
?>



</body>
</html>
