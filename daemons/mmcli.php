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
    

    This is a PHP-CLI script made to pick up location from the mmcli, used in some 3G devices.
*/
// start the code
$gpsreader = false;
$daemon_name = "mmcli";

if(@$ini_array[$daemon_name]["MMCLI_PATH"]) { $path = "" . $ini_array[$daemon_name]["MMCLI_PATH"] . "/"; }

$gpsmmcli = popen("" . $path . "mmcli -m " . $ini_array[$daemon_name]["MODEM"] . " --location-get-gps-raw", "r");
$gpsinput = fread($gpsmmcli, 1024);
pclose($gpsmmcli);

// get the locations
$latitude = preg_replace("/(.*)Latitude\: \'([-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?))\'(.*)/s", "$2", $gpsinput);
$longitude = preg_replace("/(.*)Longitude\: \'([-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?))'(.*)/s", "$2", $gpsinput);

if($test) {
	print("\nGPSReader TEST MODE [" . $daemon_name . "]:\nCheck your data if it looks OK, disable test mode in the configuration file.\n\n");
	print("Latitude: " . $latitude . "\nLongitude: " . $longitude . "\n");
	print("********************************************************************************\n");
}
else if(!$test && $latitude && $longitude) {
	$gpsreader = true;
}
else {
	die("\nError [" . $daemon_name . "]: please check the configuration file and fill out everything missing.\n\n");
}
?>
