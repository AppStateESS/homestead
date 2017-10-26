<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\StudentFactory;
use \Homestead\StudentAddRoomDamagesView;

class ShowStudentAddRoomDamagesCommand extends Command {

    private $term;

    public function getRequestVars()
    {
        return array(
                'action' => 'ShowStudentAddRoomDamages',
                'term' => $this->term
                );
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        // Create the student
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $view = new StudentAddRoomDamagesView($student, $term);

        $context->setContent($view->show());
    }
}
