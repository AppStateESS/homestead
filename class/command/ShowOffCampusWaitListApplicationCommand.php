<?php

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

        if (isset($result) && $result->getApplicationType == 'offcampus_waiting_list') {
            NQ::simple('hms', hms\NotificationView::ERROR, 'You have already enrolled on the on-campus housing Open Waiting List for this term.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        // Make sure the student agreed to the terms, if not, send them back to the terms & agreement command
        $event = $context->get('event');

        $_SESSION['application_data'] = array();

        // If they haven't agreed, redirect to the agreement
        if(is_null($event) || !isset($event) || ($event != 'signing_complete' && $event != 'viewing_complete')){
            $onAgree = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
            $onAgree->setTerm($term);

            $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
            $agreementCmd->setTerm($term);
            $agreementCmd->setAgreedCommand($onAgree);
            $agreementCmd->redirect();
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        PHPWS_Core::initModClass('hms', 'ReApplicationOffCampusFormView.php');
        $view = new ReApplicationOffCampusFormView($student, $term);

        $context->setContent($view->show());
    }
}

?>