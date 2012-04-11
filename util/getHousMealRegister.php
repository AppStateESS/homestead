#!/usr/bin/php
<?php

/**
 * Returns a student's housing assignment, meal plan, and application status
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('username' => '',
              'term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

$result = $soap->getHousMealRegister($args['username'], $args['term'], 'All');
print_r($result);
echo "\n";

?>
