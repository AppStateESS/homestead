#!/usr/bin/php
<?php

/**
 * Manually creates a room assignment and meal plan in Banner
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

require_once('SOAP.php');
require_once('cliCommon.php');

$args = array('username'=>'',
              'term'    =>'',
              'building'=>'',
              'room'    =>'',
              'plan'    =>'',
              'meal'    =>'');
$switches = array();
check_args($argc, $argv, $args, $switches);

$soap = new PhpSOAP();

if($args['meal'] == 'NULL') $args['meal'] = null;

$result = $soap->reportRoomAssignment($args['username'],
                                 $args['term'],
                                 $args['building'],
                                 $args['room'],
                                 $args['plan'],
                                 $args['meal']);

print_r($result);
echo "\n";

echo "Type is: " . gettype($result) . "\n";

if($result == "0"){
    echo "Success!\n";
}else{
    echo "Not a success\n";
}

if ($result != "0"){
    echo "Failure!\n";
}else{
    echo "Not a failure\n";
}

?>
