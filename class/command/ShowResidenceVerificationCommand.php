<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowResidenceVerificationCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowResidenceVerification');
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getCurrentTerm();

        // Get the current student
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Check for an assignment
        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $term);

        // If not assigned, then redirect to the main menu with an error
        if(is_null($assignment)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You do not have a room assignment for the current semester.');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }

        $tpl['NAME'] = $student->getFullName();
        $tpl['ASSIGNMENT'] = $assignment->where_am_i();
        $tpl['TERM'] = Term::toString($term);

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'student/residenceVerification.tpl'));
    }
}
