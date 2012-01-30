<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

/* s2Member-only mode. Only load special file `s2member-o.php`, exclude all others. */

if (file_exists (WPMU_PLUGIN_DIR . "/s2member-o.php"))
	include_once WPMU_PLUGIN_DIR . "/s2member-o.php";