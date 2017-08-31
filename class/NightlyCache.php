<?php

namespace Homestead;

use \phpws2\Database;

define('HMS_CACHE_ERROR_THRESHOLD', 30);

/**
 * Refreshes the cache nightly
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';

class NightlyCache
{

    public static function execute()
    {
        session_start();
        \PHPWS_Core::initModClass('users', 'Users.php');
        \PHPWS_Core::initModClass('users', 'Current_User.php');

        $errors = null;
        $term = Term::getSelectedTerm();

        $db1 = Database::newDB();
        $t1 = $db1->addTable('hms_new_application');
        $t1->addFieldConditional('term', $term);
        $t1->addField('username');

        $db2 = Database::newDB();
        $t2 = $db2->addTable('hms_assignment');
        $t2->addFieldConditional('term', $term);
        $t2->addField('asu_username');

        $union = new Database\Union(array($db1, $db2));
        $result = $union->select();

        if (empty($result)) {
            return 'No assignments or applications. Check your database.';
        }

        $count = 0;
        $error_count = 0;

        $_SESSION['User'] = new \PHPWS_User;
        $_SESSION['User']->username= 'nightlycache';
        $_SESSION['User']->display_name = 'Nightly Cache';

        foreach ($result as $row) {
            $count++;
            try {
                //asking for the student updates the cache since the ttl is zero
                StudentFactory::getStudentByUsername($row['username'], $term);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage() . "\n";
                $error_count++;
            }
            if ($error_count >= HMS_CACHE_ERROR_THRESHOLD) {
                throw new \Exception(HMS_CACHE_ERROR_THRESHOLD . ' errors occurred. Shutting down cache prematurely.');
            }
        }

        $message = "$count student records cached.\n";
        if (!empty($errors)) {
            $message .= "Errors occurred:\n";
            $message .= implode("\n", $errors);
        }
        return $message;
    }

}
