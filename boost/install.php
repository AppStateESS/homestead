<?php

/* * ********************************************************* */
/* Install file for hms, creates local users for the module */
/*                                                          */
/* @author Daniel West                                      */
/* @package mod                                             */
/* @subpackage hms                                          */
/* * ********************************************************* */

function hms_install(&$content)
{
    PHPWS_Core::initModClass('users', 'Users.php');
    $DB = new PHPWS_DB('users');
    $DB->addWhere('username', 'hms_admin');
    $result = $DB->select('one');

    if($result == null)
    {
        $user = new PHPWS_User();
        $user->setUsername('hms_admin');
        $user->setPassword('in the white room, with black curtains');
        $user->save();
    }

    $DB = new PHPWS_DB('users');
    $DB->addWhere('username', 'hms_student');
    $result = $DB->select('one');

    if($result == null)
    {
        $user = new PHPWS_User();
        $user->setUsername('hms_student');
        $user->setPassword('shes my everything, shes my pride and joy');
        $user->save();
    }

    $directory = PHPWS_HOME_DIR . 'files/hms_reports/';
    if (!is_dir($directory)) {
        mkdir($directory);
    }
    return true;
}
