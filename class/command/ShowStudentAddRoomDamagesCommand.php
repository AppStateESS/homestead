<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequestForm.php');


class ShowStudentAddRoomDamagesCommand extends Command {
    private $term;

    public function getRequestVars()
    {
        return array(
                'action' => 'ShowStudentAddRoomDamages',
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

        $view = new StudentAddRoomDamagesView($student, $term);

        $context->setContent($view->show());
    }
}
