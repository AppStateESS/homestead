<?php

namespace Homestead;

class RlcReapplicationView extends View {

    private $student;
    private $term;
    private $rlcAssignment;
    private $communities;
    private $reApp;

    public function __construct(Student $student, $term, HMS_RLC_Assignment $rlcAssignment = NULL, Array $communities, HMS_RLC_Application $reApp = null)
    {
        $this->student        = $student;
        $this->term           = $term;
        $this->rlcAssignment  = $rlcAssignment;
        $this->communities    = $communities;
        $this->reApp          = $reApp;
    }

    public function show()
    {
        $this->setTitle('RLC Re-application Form');

        $form = new PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('SubmitRLCReapplicationPageOne');
        $submitCmd->initForm($form);

        $form->addHidden('term', $this->term);

        javascript('jquery');
        javascript('jquery_ui');

        $tpl = array();
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerM($this->term));

        // If the student is already in an RLC, and the student is eligible to reapply (RLC always takes returners,
        // or the RLC is in the list of communities this student is eligible for), then show the 'Re-apply' option.
        if(!is_null($this->rlcAssignment) && (array_key_exists($this->rlcAssignment->getRlcId(), $this->communities) || $this->rlcAssignment->getRlc()->getMembersReapply() == 1)){
            $rlcName = $this->rlcAssignment->getRlcName();
            $form->addRadio('rlc_opt', array('continue', 'new'));
            $form->setLabel('rlc_opt', array('I would like to continue in the ' . $rlcName . '.', 'I would like to apply for a different community.'));

            //TODO remove the RLC they're in from the $communities list, if it exists there
        }

        // Merge the arrays and preserve the keys ('+' operator works for that...)
        $firstCommunity   = array('select'=>'Select..') + $this->communities;
        $otherCommunities = array('none'=>'None') + $this->communities;

        $form->addDropBox('rlc_choice_1', $firstCommunity);
        $form->setLabel('rlc_choice_1', 'First choice:');
        $form->setExtra('rlc_choice_1', 'margin-left: 20px;"');
        $form->addCssClass('rlc_choice_1', 'form-control');

        $form->addDropBox('rlc_choice_2', $otherCommunities);
        $form->setLabel('rlc_choice_2', 'Second choice:');
        $form->setExtra('rlc_choice_2', 'margin-left: 20px;"');
        $form->addCssClass('rlc_choice_2', 'form-control');

        $form->addDropBox('rlc_choice_3', $otherCommunities);
        $form->setLabel('rlc_choice_3', 'Third choice:');
        $form->addCssClass('rlc_choice_3', 'form-control');

        $form->addTextArea('why_this_rlc');
        $form->addCssClass('why_this_rlc', 'form-control');
        $form->addTextArea('contribute_gain');
        $form->addCssClass('contribute_gain', 'form-control');

        // Set values if they exist on the session
        if(isset($this->reApp)){
            $form->grab('why_this_rlc')->setValue($this->reApp->why_specific_communities);
            $form->grab('contribute_gain')->setValue($this->reApp->strengths_weaknesses);
        }

        $form->addSubmit('submit', 'Continue');

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/rlcReapplicationView.tpl');
    }
}
