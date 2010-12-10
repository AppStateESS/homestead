<?php

class RlcReapplicationView extends View {

    private $student;
    private $term;
    private $rlcAssignment;

    public function __construct(Student $student, $term, HMS_RLC_Assignment $rlcAssignment = NULL)
    {
        $this->student        = $student;
        $this->term           = $term;
        $this->rlcAssignment  = $rlcAssignment;
    }

    public function show()
    {
        $form = new PHPWS_Form();

        Layout::addPageTitle("RLC Re-application Form");
        javascript('jquery');
        javascript('jquery_ui');

        $tpl = array();
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerM($this->term));

        // Change the view based on whether or not the student is already assigned to an RLC
        if(!is_null($this->rlcAssignment)){
            $rlcName = $this->rlcAssignment->getRlcName();
            $form->addRadio('rlc_opt', array('continue', 'new'));
            $form->setLabel('rlc_opt', array('Continue in ' . $rlcName . '.', 'Apply for a different community.'));
        }

        $form->addDropBox('rlc_choice_1', array('crap'=>'my community'));
        $form->setLabel('rlc_choice_1', 'First choice:');
        $form->setExtra('rlc_choice_1', 'margin-left: 20px;"');

        $form->addDropBox('rlc_choice_2', array('crap'=>'my community'));
        $form->setLabel('rlc_choice_2', 'Second choice:');
        $form->setExtra('rlc_choice_2', 'margin-left: 20px;"');

        $form->addDropBox('rlc_choice_3', array('crap'=>'my community'));
        $form->setLabel('rlc_choice_3', 'Third choice:');

        $form->addTextArea('why_this_rlc');
        $form->addTextArea('contribute_gain');

        $form->addSubmit('submit', 'Submit Application');

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/rlcReapplicationView.tpl');
    }
}

?>