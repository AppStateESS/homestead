#!/usr/bin/php
<?php

/**
 * Rereport HMS Applications to Banner
 *
 * This script was designed to fix a case where HMS has
 * saved student housing applications, but those applications
 * do not appear in Banner.
 */

define('PHPWS_SOURCE_DIR', '../../../');

require('../class/HMS_SOAP.php');

echo "Rereporting Applications...\n";

# Get the list of usernames
$usernames = file('applications_to_rereport-2007-12-12.txt');

echo sizeof($usernames) . ' applications to report...';

# Connect to the database
$db = pg_connect();


?>
