#!/usr/bin/php
<?php

/**
 * Command-line script to set the freshmen housing waiver on
 * a freshmen's housing application in Banner.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('username' => '',
              'term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();
$result = $soap->setHousingWaiver($args['username'], $args['term']);

var_dump($result);
