<?php
function findprevnextsubnets($subnet, $subnets)  {
	# set previous subnet & next subnet
	$i = array_search($subnet, $subnets);
	if ($i > 0) {
		$previousSubnet = $subnets[$i - 1];
	}		
	else	
	{ 
		$previousSubnet = end($subnets);
	}
		
	if ($i < count($subnets) - 1) {
		$nextSubnet = $subnets[$i + 1];
	}
	else
	{
		$nextSubnet = $subnets[0];
	}
	return array($previousSubnet, $nextSubnet);
}
?>
