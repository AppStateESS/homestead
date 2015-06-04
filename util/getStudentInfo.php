#!/usr/bin/php
<?php

/**
 * Command-line script to get student profile
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('username' => '',
              'term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();
$result = $soap->getStudentInfo($args['username'], $args['term']);

print_r($result);
echo "\n";
