<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class AddCommunityView extends View {
    private $community;

    public function __construct(HMS_Learning_Community $rlc = NULL){
        $this->community = $rlc;
    }

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
        $var = array('ELEMENT' => $form->getId('community_name'));
        javascript('modules/hms/autoFocus', $var);
        
        $form->addText('abbreviation', !is_null($this->community)?$this->community->get_abbreviation():'');
        $form->addText('capacity', !is_null($this->community)?$this->community->get_capacity():'');
        $form->setSize('capacity', 5);

        $form->addText('student_types', !is_null($this->community)?$this->community->getAllowedStudentTypes():'');
        $form->addText('reapplication_student_types', !is_null($this->community)?$this->community->getAllowedReApplicationStudentTypes():'');

        $form->addCheckAssoc('members_reapply', array('reapply'=>'Current Members Can Always Re-apply:'));

        // Set match on the members_reapply checkbox
        if(!is_null($this->community) && $this->community->getMembersReapply() == 1){
            $form->setMatch('members_reapply', 'reapply');
        }

        $form->addTextArea('freshmen_question');
        $form->setLabel('freshmen_question', 'Freshmen Question:');
        $form->setValue('freshmen_question', $this->community->getFreshmenQuestion());
        
        $form->addTextArea('returning_question');
        $form->setLabel('returning_question', 'Returning Question:');
        $form->setValue('returning_question', $this->community->getReturningQuestion());
        
        $form->addHidden('hide', 0);

        if(!is_null($this->community) && !is_null($this->community->get_id())){
            $form->addHidden('id', $this->community->get_id());
        }
        
        $form->addSubmit('Save');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $this->setTitle("Add/Edit RLC");

        return PHPWS_Template::process($tpl, 'hms', 'admin/editLearningCommunity.tpl');
    }
}
?>
