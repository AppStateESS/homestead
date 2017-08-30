<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\JsonError;
use \Homestead\StudentFactory;
use \Homestead\Term;
use \Homestead\HMS_Util;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\StudentNotFoundException;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONGetStudentCommand
{
    public function getRequestVars()
    {
        return array('action' => 'JSONGetStudent');
    }

    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'search')) {
            throw new PermissionException('You do not have permission to lookup student names!');
        }
        $student = null;
        $error = new JsonError(403);

        $username = $context->get('username');
        $banner_id = (int) $context->get('banner_id');
        try {
            if ($banner_id) {
                $student = StudentFactory::getStudentByBannerID($banner_id, Term::getSelectedTerm());
            } elseif (!empty($username)) {
                $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());
            } else {
                $error->setMessage('Did not receive Banner ID or user name.');
                $context->setContent(json_encode($error));
            }
            $student->gender_string = HMS_Util::formatGender($student->gender);
            $context->setContent(json_encode($student));
        } catch (\StudentNotFoundException $e) {
            $error->setMessage($e->getMessage());
            $context->setContent(json_encode($error));
        }
    }

}
