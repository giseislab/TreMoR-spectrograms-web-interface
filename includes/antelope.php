<?php
$_ENV{'ANTELOPE'} = "/opt/antelope/5.0-64";
#$_ENV{'ANTELOPE'} = "/opt/antelope/4.11";
#
if( !extension_loaded( "Datascope" ) ) { 
        dl( "Datascope.so" ) or die( "Failed to dynamically load Datascope.so" ) ; 
}
$cwd = getcwd();

clearstatcache();

# global variables	
#$pfdir = '../pf';
$pfdir = '/usr/local/mosaic/AVO/avoseis/TreMoR/pf';
$parameterspf = $pfdir . '/parameters.pf';
$WEBPLOTSPATH = "/usr/local/mosaic/AVO/internal/avoseis/bronco/plots";
$WEBPLOTS = "../plots"; # URL
$subnets = pfget($parameterspf, 'subnetnames');

?>
