<?php
/*
    phpGPSReader - reading GPS locations from cgps and put them in arrays.
    Copyright (C) 2017-2018  Ole-Henrik Jakobsen

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
    
    
    This is a PHP-CLI script made to pick up location from the gpsd via gpspipe.
*/
// start the code
$gpsreader = false;
$daemon_name = "gpsd";
$path = "";
$contents = "";

//$query_age = (isset($_GET['query_age']) ? $_GET['query_age'] : null);
$time_end = (!isset($time_end) ? time() + ($ini_array[$daemon_name]["SENSORMAXRETRIES"] * 60) : null);
$time_delay = (!isset($time_delay) ? $ini_array[$daemon_name]["SENSORRETRYDELAY"] : null);

if(@$ini_array[$daemon_name]["GPSPIPE_PATH"]) { $path = "" . $ini_array[$daemon_name]["GPSPIPE_PATH"] . "/"; }

if($test) {
	print("Script started. Will fail and exit at " . date('Y-m-d H:i:s', $time_end) . "\n");
}

while(time()<$time_end) {
	$gpspipe = popen("" . $path . "gpspipe -w -n 10", "r"); // run at least 10 times so we are sure we get the data we need
	$daemon_input = stream_get_contents($gpspipe);
	pclose($gpspipe);

	// get the locations
	$contents_arr = array_filter(explode("\n", $daemon_input));
	
	for($i=0;$i<count($contents_arr);$i++) {
		if(preg_match("/(TPV)/", $contents_arr[$i])) {
			$contents = $contents_arr[$i]; // just overwrite the matches so we get the latest data
		}
	}
	$daemon_gpsd = @json_decode($contents, true);
	
	$gpsreader_array = @array(
		"deviceport"	=> $daemon_gpsd["device"],	// device port, ex: /dev/ttyS0
		"time"		=> $daemon_gpsd["time"],	// GPS time
		"latitude"	=> $daemon_gpsd["lat"],		// Latitude
		"longitude"	=> $daemon_gpsd["lon"],		// Longitude
		"altitude"	=> $daemon_gpsd["alt"],		// Altitude
		"track"		=> $daemon_gpsd["track"],	// Heading (deg), direction
		"speed"		=> $daemon_gpsd["speed"],	// Speed (kph)
		"climb"		=> $daemon_gpsd["climb"],	// Climb (m/min)
		"mode"		=> $daemon_gpsd["mode"],	// Mode - (Status: 2D/3D)?
		"error" => @array(
			"timeoffset"	=> $daemon_gpsd["ept"],		// Time offset
			"latitude"	=> $daemon_gpsd["epy"],		// Latitude error +/- (m)
			"longitude"	=> $daemon_gpsd["epx"],		// Longitude error +/- (m)
			"altitude"	=> $daemon_gpsd["epv"],		// Altitude error +/- (m)
			"course"	=> $daemon_gpsd["epc"],		// Course error (?)
			"speed"		=> $daemon_gpsd["eps"]		// Speed error +/- (kph)
		)
	);
	
	if(@$daemon_gpsd["lat"] && @$daemon_gpsd["lon"]) {
		break;
	}
	else {
		if($test) {
			print("Could not get GPS data, retrying in $time_delay seconds.\n");
		}
		sleep($time_delay);
	}
}

if($test) {
	print("\nGPSReader TEST MODE [" . $daemon_name . "]:\nCheck your data if it looks OK, disable test mode in the configuration file.\n\n");
	print("GPS Time: " . @$daemon_gpsd["time"] . "\n"); // NB! Not used in script, only for testing
	print("Device: " . @$daemon_gpsd["device"] . "\n"); // NB! Not used in script, only for testing
	print("Latitude: " . @$daemon_gpsd["lat"] . "\nLongitude: " . @$daemon_gpsd["lon"] . "\n");
	print("********************************************************************************\n");
	//print("DEBUG: TIME: " . time() . " | TIME_END: " . $time_end . " | TIME_DELAY: " . $time_delay . " | PIPE_PATH: " . $path . "\n");
}
else if(!$test && @$daemon_gpsd["lat"] && @$daemon_gpsd["lon"]) {
	$gpsreader = true;
}
else {
	die("\nError [" . $daemon_name . "]: could not find GPS data, please check the configuration file and fill out everything missing or fix/replace the GPS module.\n\n");
}
?>
