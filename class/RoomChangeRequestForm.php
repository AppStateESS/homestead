<?php

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

class RoomChangeRequestForm extends hms\View {

    private $student;

    private $term;

    public function __construct(Student $student, $term)
    {
        $this->student  = $student;
        $this->term     = $term;
    }

    public function show()
    {
        javascript('jquery');
        javascriptMod('hms', 'studentRoomChange');

        $form = new PHPWS_Form('room_change_request');

        /* POST location */
        $cmd = CommandFactory::getCommand('SubmitRoomChangeRequest');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        $user = UserStatus::getUsername();
        $student = StudentFactory::getStudentByUsername($user, Term::getSelectedTerm());
        $tpl['CURRENT_USER'] = $user;

        return PHPWS_Template::process($tpl, 'hms', 'student/roomChangeRequestForm.tpl');
    }
}
