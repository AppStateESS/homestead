<?php
/**
  *  _   _       _
  * | \ | | ___ | |_ ___ _
  * |  \| |/ _ \| __/ _ (_)
  * | |\  | (_) | ||  __/_
  * |_| \_|\___/ \__\___(_)
  *
  * This file must be copied to the top level of your PHPWS install directory,
  * in order to function.  The core classes expect files to be relative to that
  * directory and this script requires those classes so there doesn't seem to 
  * be a way around that.
  **/

// copy this file in your phpwebsite root directory as
// test.php. Use only for development.

header('Content-Type: text/html; charset=UTF-8');
include 'config/core/config.php';
require_once 'core/class/Init.php';

PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initCoreClass('Template.php');

require_once 'inc/Functions.php';

/****************************************************/
require_once 'mod/hms/inc/defines.php';
PHPWS_Core::initModClass('hms', 'HMS_Consistancy_Checker.php');

$results = Consistancy_checker::check();

foreach($results as $hall => $floors){
    echo "Error in Hall: ".$hall."\n";

    //if it's not an array we don't have any floors so echo out the error msg
    if(!is_array($floors)){ 
        echo "\t" . $floors . "\n";
    } else {
        foreach($floors as $floor => $rooms){
            echo "\tError in Floor: " . $floor."\n";
            if(!is_array($rooms)){
                echo "\t" . $rooms . "\n";
            } else {
                foreach($rooms as $room){
                    echo "\t" . $room . "\n";
                }
            }
        }
    }
    echo "\n";
}
?>
