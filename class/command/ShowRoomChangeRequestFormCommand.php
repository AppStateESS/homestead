<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequestForm.php');


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
?>