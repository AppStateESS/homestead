<?php
PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'Student.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

/*
 * ShowRlcApplicationPage1View
 *
 *   The first page in the rlc application process, mostly a copy and paste from the HMS_RLC_Application
 * function by the same name.
 *
 */

class RlcApplicationPage1View extends View {
    protected $context;

    public function __construct(CommandContext $context){
        $this->context = $context;
    }

    public function setContext(CommandContext $context){
        $this->context = $context;
    }

    public function show(){
        //Seriously php?  Can't resolve context without this?  Fail.
        $context = $this->context;
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        
        $student = StudentFactory::getStudentByUsername(Current_User::getUsername(), Term::getCurrentTerm());
 
        $template = array();

        $rlc_form = new PHPWS_Form();
        CommandFactory::getCommand('ShowRlcApplicationPage2View')->initForm($rlc_form);


        # Make sure the user is eligible for an RLC
        if($student->getCreditHours() > 15){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, you are not eligible for a Unique Housing Option for Underclassmen. Please visit the <a href="http://housing.appstate.edu/index.php?module=pagemaster&PAGE_user_op=view_page&PAGE_id=293" target="_blank">Unique Housing Options for Upperclassmen website</a> for information on applying for Unique Housing Options for Upperclassmen.');
            $cmd     = CommandFactory::getCommand('ShowRlcApplicationPage1View');
            $cmd->redirect();
        }

        # 1. About You Section
        $first_name  = $student->getFirstName();
        $middle_name = $student->getMiddleName();
        $last_name   = $student->getLastName();
        
        # Check for error in SOAP communication. isset doesn't work to check these, for some reason
        if( !isset($first_name) || !isset($last_name) ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Error: There was a problem communicating with the student information server. Please try again later.");
            return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
        }

        $template['APPLENET_USERNAME']       = $student->getUsername();
        $template['APPLENET_USERNAME_LABEL'] = 'Applenet User Name: ';

        $template['FIRST_NAME']        = $first_name;
        $template['FIRST_NAME_LABEL']  = 'First Name: ';
        
        $template['MIDDLE_NAME']       = $middle_name;
        $template['MIDDLE_NAME_LABEL'] = 'Middle Name: ';
        
        $template['LAST_NAME']         = $last_name;
        $template['LAST_NAME_LABEL']   = 'Last Name: ';

        $rlc_form->addHidden('first_name',  $first_name);
        $rlc_form->addHidden('middle_name', $middle_name);
        $rlc_form->addHidden('last_name',   $last_name);

        # 2. Rank Your RLC Choices

        # Get the list of RLCs from the database that this student is allowed to apply for and which are not hidden
        $rlc_choices = HMS_Learning_Community::getRLCList(FALSE, $student->getType());
       
        # Add an inital element to the list.
        $rlc_choices[-1] = "Select";
        
        # Make a copy of the RLC choices list, replacing "Select" with "None".
        # To be used with the second and third RLC choices
        $rlc_choices_none = $rlc_choices;
        $rlc_choices_none[-1] = "None";

        $rlc_form->addDropBox('rlc_first_choice', $rlc_choices);
        $rlc_form->setLabel('rlc_first_choice','First Choice: ');
        if(!is_null($context->get('rlc_first_choice'))){
            $rlc_form->setMatch('rlc_first_choice', $context->get('rlc_first_choice')); # Select previous choice
        }else{
            $rlc_form->setMatch('rlc_first_choice', -1); # Select the default
        }
        
        $rlc_form->addDropBox('rlc_second_choice', $rlc_choices_none);
        $rlc_form->setLabel('rlc_second_choice','Second Choice: ');
        if(!is_null($context->get('rlc_second_choice'))){
            $rlc_form->setMatch('rlc_second_choice', $context->get('rlc_second_choice')); # Select previous choice
        }else{
            $rlc_form->setMatch('rlc_second_choice', -1); # Select the default
        }
        
        $rlc_form->addDropBox('rlc_third_choice', $rlc_choices_none);
        $rlc_form->setLabel('rlc_third_choice','Third Choice: ');
        if(!is_null($context->get('rlc_third_choice'))){
            $rlc_form->setMatch('rlc_third_choice', $context->get('rlc_third_choice'));
        }else{
            $rlc_form->setMatch('rlc_third_choice', -1); # Select the default
        }

        # 3. About Your Choices

        if(!is_null($context->get('why_specific_communities'))){
            $rlc_form->addTextarea('why_specific_communities',$context->get('why_specific_communities'));
        }else{
            $rlc_form->addTextarea('why_specific_communities');
        }
        $rlc_form->setLabel('why_specific_communities',
                            'Why are you interested in the specific communities you have chosen?');
        $rlc_form->setMaxSize('why_specific_communities',2048);

        if(!is_null($context->get('strengths_weaknesses'))){
            $rlc_form->addTextarea('strengths_weaknesses', $context->get('strengths_weaknesses'));
        }else{
            $rlc_form->addTextarea('strengths_weaknesses');
        }
        $rlc_form->setLabel('strengths_weaknesses',
                            'What are your strengths and in what areas would you like to improve?');
        $rlc_form->setMaxSize('strengths_weaknesses',2048);

        $rlc_form->addButton('cancel','Cancel');
        $rlc_form->setExtra('cancel','onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        $rlc_form->addSubmit('submit', 'Continue'); 
    
        $rlc_form->mergeTemplate($template);
        $template = $rlc_form->getTemplate();
                
        return PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
  }
}

?>
