<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class ShowEmergencyContactFormCommand extends Command {

    private $term;
    private $vars;

    public function setTerm($term){
        $this->term = $term;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    public function getRequestVars()
    {
        $vars = $this->vars;

        $vars['action'] = 'ShowEmergencyContactForm';
        unset($vars['module']);

        if(isset($this->term)){
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'EmergencyContactFormView.php');

        // Make sure we have a valid term
        $term = $context->get('term');

        if(is_null($term) || !isset($term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $application = HousingApplication::getApplicationByUser($student->getUsername(), $term);
        $formView = new EmergencyContactFormView($student, $term, $application);

        $context->setContent($formView->show());
    }
}


