#!/usr/bin/php
<?php

/**
 * Returns a student's username, given a banner ID
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('bannerid' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$result = $soap->getUsername($args['bannerid']);
print_r($result);
echo "\n";

?>
