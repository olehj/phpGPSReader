<?php
/*
    phpGPSReader - reading GPS locations from cgps and put them in arrays.
    Copyright (C) 2017  Ole-Henrik Jakobsen

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, sHow many digits to shift to get a standard readout like 20.500Cee <http://www.gnu.org/licenses/>.
    
    
    This is a PHP-CLI script made to pick up location from the dummy daemon.
    This is just for testing in case you haven't got any GPS yet.
    Dummy is based upon the data from gpspipe (gpspipe -w).
*/
// start the code
$gpsreader = false;
$daemon_name = "Dummy";
$daemon_input = "daemons/dummy.txt";

// get the locations
$handle = fopen($daemon_input, "r");
$contents = fread($handle, filesize($daemon_input));
$contents_arr = array_filter(explode("\n", $contents));
$daemon_dummy = json_decode($contents_arr["" . count($contents_arr)-1 . ""], true);

$gpsreader_array = array(
	"device" => $daemon_dummy["device"],
	"time" => $daemon_dummy["time"],
	"latitude" => $daemon_dummy["lat"],
	"longitude" => $daemon_dummy["lon"]
);

if($test) {
	print("\nGPSReader TEST MODE [" . $daemon_name . "]:\nCheck your data if it looks OK, disable test mode in the configuration file.\n\n");
	print("GPS Time: " . $daemon_dummy["time"] . "\n"); // NB! Not used in script, only for testing
	print("Device: " . $daemon_dummy["device"] . "\n"); // NB! Not used in script, only for testing
	print("Latitude: " . $latitude . "\nLongitude: " . $longitude . "\n");
	print("********************************************************************************\n");
}
else if(!$test && $daemon_dummy["device"]) {
	$gpsreader = true;
}
else {
	die("\nError [" . $daemon_name . "]: please check the configuration file and fill out everything missing.\n\n");
}
?>
