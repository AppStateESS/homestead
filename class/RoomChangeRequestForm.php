<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

class RoomChangeRequestForm extends View {

    private $student;

    private $term;

    public function __construct(Student $student, $term)
    {
        $this->student  = $student;
        $this->term     = $term;
    }

    public function show()
    {
        $form = new \PHPWS_Form('room_change_request');

        /* Cell phone */
        $form->addText('cell_num');
        $form->setLabel('cell_num', 'Cell phone Number');
        $form->addCssClass('cell_num', 'form-control');

        $form->addCheck('cell_opt_out');

        /* Preferences */
        $halls = array(0=>'Choose from below...');
        $halls = $halls+HMS_Residence_Hall::get_halls_array(Term::getSelectedTerm());

        $form->addRadioAssoc('type', array('switch'=>'I want to change to an open bed.', 'swap'=>'I want to swap beds with someone I know.'));

        /* Swap */
        $form->addText('swap_with');
        $form->setLabel('swap_with', 'ASU Email Address');
        $form->addCssClass('swap_with', 'form-control');

        /* Switch */
        $form->addDropBox('first_choice', $halls);
        $form->setLabel('first_choice', 'First Choice');
        $form->addCssClass('first_choice', 'form-control');

        $form->addDropBox('second_choice', $halls);
        $form->setLabel('second_choice', 'Second Choice');
        $form->addCssClass('second_choice', 'form-control');

        /* Reason */
        $form->addTextArea('reason');
        $form->setLabel('reason', 'Reason');
        $form->addCssClass('reason', 'form-control');
        $form->setRows('reason', 5);

        /* POST location */
        $cmd = CommandFactory::getCommand('SubmitRoomChangeRequest');
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'student/roomChangeRequestForm.tpl');
    }
}
