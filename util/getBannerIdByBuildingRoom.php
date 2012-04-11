#!/usr/bin/php
<?php

/**
 * Command-line script to get the BannerId of the student assigned to a given bed
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('hallCode' => '',
              'bedCode' => '',
              'term' => '');
$switches = array();

check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();
$result = $soap->getBannerIdByBuildingRoom($args['hallCode'], $args['bedCode'], $args['term']);

echo print_r($result, true);
echo "\n";
?>
