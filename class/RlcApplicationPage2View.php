<?php
PHPWS_Core::initModClass('hms', 'Student.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

/*
 * ShowRlcApplicationPage2View
 *
 *   The second page in the rlc application process, mostly a copy and paste from the HMS_RLC_Application
 * function by the same name.
 *
 */
class RlcApplicationPage2View extends hms\View{

    protected $context;

    public function __construct(CommandContext $context)
    {
        $this->context = $context;
    }

    public function setContext(CommandContext $context)
    {
        $this->context = $context;
    }

    public function show()
    {

        //Seriously php?  Can't resolve context without this?  Fail.
        $context = $this->context;

        $template = array();

        $cmd = CommandFactory::getCommand('SubmitRlcApplication');
        $rlc_form2 = new PHPWS_Form();
        $cmd->initForm($rlc_form2);

        // Add hidden fields for fields from page 1
        $rlc_form2->addHidden('first_name',  $context->get('first_name'));
        $rlc_form2->addHidden('middle_name', $context->get('middle_name'));
        $rlc_form2->addHidden('last_name',   $context->get('last_name'));
        $rlc_form2->addHidden('rlc_first_choice',  $context->get('rlc_first_choice'));
        $rlc_form2->addHidden('rlc_second_choice', $context->get('rlc_second_choice'));
        $rlc_form2->addHidden('rlc_third_choice',  $context->get('rlc_third_choice'));
        $rlc_form2->addHidden('why_specific_communities', $context->get('why_specific_communities'));
        $rlc_form2->addHidden('strengths_weaknesses', $context->get('strengths_weaknesses'));
        $rlc_form2->addHidden('term', $context->get('term'));

        $rlcIds = array($context->get('rlc_first_choice'), $context->get('rlc_second_choice'), $context->get('rlc_third_choice'));

        for($i = 0; $i < 3; $i++) {
            # Skip the question lookup if "none" was selected
            if($rlcIds[$i] == -1) {
                continue;
            }

            $rlc = new HMS_Learning_Community($rlcIds[$i]);
            
            // If we're missing a question... send them back. We might could throw an exception here.
            $question = $rlc->getFreshmenQuestion();
            if(!isset($question)) {
              NQ::simple('hms', HMS_NOTIFICATION_ERROR, "There was an error looking up the community questions.");
              $cmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
              $cmd->setTerm($context->get('term'));
              $cmd->redirect();
            }

            $rlc_form2->addTextArea("rlc_question_$i");
            $rlc_form2->setLabel("rlc_question_$i", $rlc->getFreshmenQuestion());
            $rlc_form2->setMaxSize("rlc_question_$i", 4096);
            $rlc_form2->setCols("rlc_question_$i", 50);
            $rlc_form2->setRows("rlc_question_$i", 15);
        }

        $rlc_form2->addSubmit('submit', 'Submit Application');

        $rlc_form2->addButton('cancel', 'Cancel');
        $rlc_form2->setExtra('cancel', 'onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        $rlc_form2->mergeTemplate($template);
        $template = $rlc_form2->getTemplate();

        Layout::addPageTitle("RLC Application");
		javascript('jquery');
        return PHPWS_Template::process($template, 'hms', 'student/rlc_signup_form_page2.tpl');
    }
}
?>
