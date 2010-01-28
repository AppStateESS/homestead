<?php
PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class RlcApplicationReView extends View {
    protected $context;

    public function __construct(CommandContext $context){
        $this->context = $context;
    }

    public function show(){
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        try{
            $student = StudentFactory::getStudentByUsername($this->context->get('username'), Term::getCurrentTerm());
            $tags['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to RLC Applications'), 'hms', array('action'=>'AssignRlcApplicants'));
        } catch(StudentNotFoundException $e){
            $tags['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to Menu'), 'hms', array('action'=>'ShowStudentMenu'));
        }

        $tags['FULL_NAME'] = $student->getFullName();

        $tags['FIRST_CHOICE_LABEL'] = "First choice RLC is: ";
        $tags['SECOND_CHOICE_LABEL'] = "Second choice is: ";
        $tags['THIRD_CHOICE_LABEL'] =  "Third choice is: ";
        
        $tags['WHY_SPECIFIC_LABEL'] = "Specific communities chosen because: ";
        $tags['STRENGTHS_AND_WEAKNESSES_LABEL'] = "Strengths and weaknesses: ";
        $tags['WHY_FIRST_CHOICE_LABEL'] = "First choice selected because: ";
        $tags['WHY_SECOND_CHOICE_LABEL'] = "Second choice selected because: ";
        $tags['WHY_THIRD_CHOICE_LABEL'] = "Third choice selected because: ";

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        $rlc_app = new HMS_RLC_Application($student->getUsername(), $student->getApplicationTerm());
        
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->addColumn('id');
        $db->addColumn('community_name');
        $rlcs_raw = $db->select();
        
        foreach($rlcs_raw as $rlc) {
            $rlcs[$rlc['id']] = $rlc['community_name'];
        }

        $tags['FIRST_CHOICE'] = $rlcs[$rlc_app->rlc_first_choice_id];
        
        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['SECOND_CHOICE'] = $rlcs[$rlc_app->rlc_second_choice_id];
        }else{
            $tags['SECOND_CHOICE'] = 'None';
        }

        if(isset($rlc_app->rlc_third_choice_id)){
            $tags['THIRD_CHOICE'] = $rlcs[$rlc_app->rlc_third_choice_id];
        }else{
            $tags['THIRD_CHOICE'] = 'None';
        }

        $tags['WHY_SPECIFIC'] = $rlc_app->why_specific_communities;
        $tags['STRENGTHS_AND_WEAKNESSES'] = $rlc_app->strengths_weaknesses;
        $tags['WHY_FIRST_CHOICE'] = $rlc_app->rlc_question_0;

        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['WHY_SECOND_CHOICE'] = $rlc_app->rlc_question_1;
        }else{
            $tags['WHY_SECOND_CHOICE'] = 'n/a';
        }
        
        if(isset($rlc_app->rlc_second_choice_id)){
            $tags['WHY_THIRD_CHOICE'] = $rlc_app->rlc_question_2;
        }else{
            $tags['WHY_THIRD_CHOICE'] = 'n/a';
        }

        return PHPWS_Template::process($tags, 'hms', 'student/rlc_application.tpl');
    }
}
?>