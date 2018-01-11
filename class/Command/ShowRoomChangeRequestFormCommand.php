<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\UserStatus;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\StudentFactory;
use \Homestead\RoomChangeRequestForm;


class ShowRoomChangeRequestFormCommand extends Command {

    private $bannerId;

    private $term;

    public function getRequestVars()
    {
        return array(
                'action' => 'ShowRoomChangeRequestForm',
        );
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getCurrentTerm();

        // Create the student
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);


        // If the student has a pending request load it from the db
        $request = RoomChangeRequestFactory::getPendingByStudent($student, $term);

        $view = new RoomChangeRequestForm($student, $term);

        $context->setContent($view->show());
    }
}
