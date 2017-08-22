<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');

/**
 * View for showing/editing Learning Communities
 * (despite the poor name, it *can* edit an existing community too)
 *
 * @author jbooker
 * @package HMS
 */
class AddCommunityView extends View {

    private $community;

    /**
     * Constructor
     *
     * @param HMS_Learning_Community $rlc
     */
    public function __construct(HMS_Learning_Community $rlc = NULL){
        $this->community = $rlc;
    }

    /**
     *
     * @see View::show()
     */
    public function show()
    {
        $tpl = array();

        if(!is_null($this->community)){
            $tpl['COMMUNITY'] = $this->community->get_community_name();
        }

        $form = new PHPWS_Form('add_learning_community');

        $submitCommand = CommandFactory::getCommand('SaveRlc');
        $submitCommand->initForm($form);

        $form->addText('community_name', !is_null($this->community)?$this->community->get_community_name():'');
        $form->setClass('community_name', 'form-control');
        $form->setExtra('community_name', 'autofocus');

        $form->addText('abbreviation', !is_null($this->community)?$this->community->get_abbreviation():'');
        $form->setClass('abbreviation', 'form-control');
        $form->addText('capacity', !is_null($this->community)?$this->community->get_capacity():'');
        $form->setSize('capacity', 5);
        $form->setClass('capacity', 'form-control');

        /*** Move-in Times ***/
        $moveinTimes = HMS_Movein_Time::get_movein_times_array(Term::getSelectedTerm());

        $form->addDropBox('f_movein_time', $moveinTimes);
        $form->setLabel('f_movein_time', 'Freshmen Move-in Time');
        $form->setClass('f_movein_time', 'form-control');

        $form->addDropBox('t_movein_time', $moveinTimes);
        $form->setLabel('t_movein_time', 'Transfer Move-in Time');
        $form->setClass('t_movein_time', 'form-control');

        $form->addDropBox('c_movein_time', $moveinTimes);
        $form->setLabel('c_movein_time', 'Continuing Move-in Time');
        $form->setClass('c_movein_time', 'form-control');

        if(!is_null($this->community)){
            $form->setMatch('f_movein_time', $this->community->getFreshmenMoveinTime());
            $form->setMatch('t_movein_time', $this->community->getTransferMoveinTime());
            $form->setMatch('c_movein_time', $this->community->getContinuingMoveinTime());
        }

        $form->addText('student_types', !is_null($this->community)?$this->community->getAllowedStudentTypes():'');
        $form->setClass('student_types', 'form-control');

        $form->addText('reapplication_student_types', !is_null($this->community)?$this->community->getAllowedReApplicationStudentTypes():'');
        $form->setClass('reapplication_student_types', 'form-control');

        $form->addCheckAssoc('members_reapply', array('reapply'=>'Current Members Can Always Re-apply'));
        $form->setStyle('members_reapply', 'transform: scale(1.5);-webkit-transform: scale(1.5);');

        // Set match on the members_reapply checkbox
        if(!is_null($this->community) && $this->community->getMembersReapply() == 1){
            $form->setMatch('members_reapply', 'reapply');
        }

        $form->addTextArea('freshmen_question');
        $form->setLabel('freshmen_question', 'Freshmen Question:');
        $form->setClass('freshmen_question', 'form-control');

        $form->addTextArea('returning_question');
        $form->setLabel('returning_question', 'Returning Question:');
        $form->setClass('returning_question', 'form-control');

        $form->addTextArea('terms_conditions');
        $form->setLabel('terms_conditions', 'Terms &amp; Conditions:');
        $form->setClass('terms_conditions', 'form-control');

        if(!is_null($this->community)){
            $form->setValue('freshmen_question', $this->community->getFreshmenQuestion());
            $form->setValue('returning_question', $this->community->getReturningQuestion());
            $form->setValue('terms_conditions', $this->community->getTermsConditions());
        }

        $form->addHidden('hide', 0);

        if(!is_null($this->community) && !is_null($this->community->get_id())){
            $form->addHidden('id', $this->community->get_id());
        }

        $form->addSubmit('submit', 'Save');
        $form->setClass('submit', 'btn btn-primary');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $this->setTitle("Add/Edit RLC");

        return PHPWS_Template::process($tpl, 'hms', 'admin/editLearningCommunity.tpl');
    }
}
