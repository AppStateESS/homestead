<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\HousingApplicationFactory;
use \Homestead\EmergencyContactFormView;

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
        // Make sure we have a valid term
        $term = $context->get('term');

        if(is_null($term) || !isset($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $application = HousingApplicationFactory::getAppByStudent($student, $term);
        $formView = new EmergencyContactFormView($student, $term, $application);

        $context->setContent($formView->show());
    }
}
