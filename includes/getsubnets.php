<?php
$cwd = getcwd();

clearstatcache();

$WEBPLOTS = "plots"; # URL
$fp = @fopen("$WEBPLOTS/subnetslist.d");
if ($fp) {
	$subnets = explode("\n", fread($fp, filesize($filename)));
}

?>
