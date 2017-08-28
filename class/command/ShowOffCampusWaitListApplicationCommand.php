<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * Command class to show the off-campus (open) waiting list housing application.
 *
 * @author jbooker
 * @package Hms
 */
class ShowOffCampusWaitListApplicationCommand extends Command {

    private $term;

    /**
     * @param integer $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        $vars = array('action'=>'ShowOffCampusWaitListApplication', 'term'=>$this->term);

        return $vars;
    }

    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context){

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $term = $context->get('term');

        // Check if the student has already applied. If so, redirect to the student menu
        $app = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term);

        if (isset($app) && $app->getApplicationType() == 'offcampus_waiting_list') {
            \NQ::simple('hms', NotificationView::ERROR, 'You have already enrolled in the Open Waiting List for this term.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        PHPWS_Core::initModClass('hms', 'ReApplicationOffCampusFormView.php');
        $view = new ReApplicationOffCampusFormView($student, $term);

        $context->setContent($view->show());
    }
}
