#!/usr/bin/php
<?php

/**
 * Command-line script to remove an assignment
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('username' => '',
              'term' => '',
              'building' => '',
              'room' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();
$result = $soap->removeRoomAssignment($args['username'], $args['term'], $args['building'], $args['room']);

print_r($result);
echo "\n";
?>
