$db = dblookup("", "/avort/oprun/events/optimised/events", "origin", "", "");
$db = dbsubset($db, "time > $sepoch && time < $eepoch");

