<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class EmergencyContactFormSubmitCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'EmergencyContactFormSubmit', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        //$term		= $context->get('term');

        //$errorCmd = CommandFactory::getCommand('ShowEmergencyContactForm');
        //$errorCmd->setTerm($term);

        /* Emergency Contact Sanity Checking */
        //TODO

        /* Missing Persons Sanity Checking */
        //TODO

        // This command grabs the current context and passes the data forward
        //$reviewCmd = CommandFactory::getCommand('ShowEmergencyContactReview');
        //$reviewCmd->setTerm($term);

        // Don't show a confirmation page, just jump to saving the new data
        $reviewCmd = CommandFactory::getCommand('EmergencyContactConfirm');
        $reviewCmd->setVars($_REQUEST);
        $reviewCmd->redirect();
    }
}

?>
