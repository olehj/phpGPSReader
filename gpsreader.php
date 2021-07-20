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
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    
    This is a PHP-CLI script made to pick up location from cgps.
 
	Configure config.ini to choose which daemon to use.
	
	Requirements: php-cli gpsd gpsd-clients
	
	Last updated: 2018-05-25

*/
$ini_file = "config.ini";
$ini_array = parse_ini_file($ini_file, true);

// put array data into variables
$test = @$ini_array["TEST"];
$daemon = @$ini_array["DAEMON"];

function gpsreader() {
	global $ini_array;
	global $daemon;
	global $test;
	
	if(!$test) { $test = 0; }
	
	// get temperature from sensor
	include("daemons/" . $daemon . ".php");
	
	if($gpsreader) {
		return $gpsreader_array;
	}
	else {
		return false;
	}
}

if(!@$test && @$argc && @is_file("" . @dirname(__FILE__) . "/daemons/" . $argv[1] . ".php")) {
	$use_sensor = $argv[1];
	$gpsreader_cli = gpsreader();
	if(@$argv[2] == "1") {
		$dis_errors = @$argv[2];
		print("Device: " . @$gpsreader_cli["deviceport"] . "\n");
		print("Time: " . @$gpsreader_cli["time"] . ""); if($dis_errors) { print(" (offset: " . @$gpsreader_cli["error"]["timeoffset"] . ")"); } print("\n");
		print("Latitude: " . @$gpsreader_cli["latitude"] . ""); if($dis_errors) { print(" (error: " . @$gpsreader_cli["error"]["latitude"] . ")"); } print("\n");
		print("Longitude: " . @$gpsreader_cli["longitude"] . ""); if($dis_errors) { print(" (error: " . @$gpsreader_cli["error"]["longitude"] . ")"); } print("\n");
		print("Altitude: " . @$gpsreader_cli["altitude"] . ""); if($dis_errors) { print(" (error: " . @$gpsreader_cli["error"]["altitude"] . ")"); } print("\n");
		print("Track: " . @$gpsreader_cli["track"] . ""); if($dis_errors) { print(" (error: " . @$gpsreader_cli["error"]["course"] . ")"); } print("\n");
		print("Speed: " . @$gpsreader_cli["speed"] . ""); if($dis_errors) { print(" (error: " . @$gpsreader_cli["error"]["speed"] . ")"); } print("\n");
		print("Climb: " . @$gpsreader_cli["climb"] . "\n");
		print("Mode: " . @$gpsreader_cli["mode"] . "\n");
	}
	else if(@$argv[2] == "simple_location") {
		print("" . @$gpsreader_cli["latitude"] . "," . @$gpsreader_cli["longitude"] . "");
	}
}

if($test) {
	gpsreader();
}
?>
