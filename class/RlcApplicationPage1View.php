<?php

namespace Homestead;

use \Homestead\Command\CommandContext;

/*
 * ShowRlcApplicationPage1View
 *
 *   The first page in the rlc application process, mostly a copy and paste from the HMS_RLC_Application
 * function by the same name.
 *
 */

class RlcApplicationPage1View extends View{

    protected $context;
    private $student;

    public function __construct(CommandContext $context, Student $student)
    {
        $this->context = $context;
        $this->student = $student;
    }

    public function setContext(CommandContext $context)
    {
        $this->context = $context;
    }

    public function show()
    {
        \Layout::addPageTitle("RLC Application");
		javascript('jquery');

        $jsVars = array('ELEMENTS_TO_BIND'=>'"#phpws_form_rlc_first_choice","#phpws_form_rlc_second_choice","#phpws_form_rlc_third_choice"', 'ACTION'=>'AjaxGetRLCExtraInfo');
        javascript('modules/hms/formDialog', $jsVars);

        $context = $this->context;

        $template = array();

        $rlc_form = new \PHPWS_Form();
        $page2Cmd = CommandFactory::getCommand('ShowRlcApplicationPage2View');
        $page2Cmd->setTerm($context->get('term'));
        $page2Cmd->initForm($rlc_form);

        // 1. Rank Your RLC Choices

        // Get the list of RLCs from the database that this student is allowed to apply for and which are not hidden
        $rlcList = HMS_Learning_Community::getRlcList(false, $this->student->getType());

        // Add an inital element to the list.
        $rlc_choices = array("-1" => "Select") + $rlcList;

        // Make a copy of the RLC choices list, replacing "Select" with "None".
        // To be used with the second and third RLC choices
        $rlc_choices_none = array('-1' => "None") + $rlcList;

        $rlc_form->addDropBox('rlc_first_choice', $rlc_choices);
        $rlc_form->addCssClass('rlc_first_choice', 'form-control');
        $rlc_form->setLabel('rlc_first_choice','First Choice: ');
        if(!is_null($context->get('rlc_first_choice'))) {
            $rlc_form->setMatch('rlc_first_choice', $context->get('rlc_first_choice')); // Select previous choice
        } else {
            $rlc_form->setMatch('rlc_first_choice', -1); // Select the default
        }

        $rlc_form->addDropBox('rlc_second_choice', $rlc_choices_none);
        $rlc_form->addCssClass('rlc_second_choice', 'form-control');
        $rlc_form->setLabel('rlc_second_choice','Second Choice: ');
        if(!is_null($context->get('rlc_second_choice'))) {
            $rlc_form->setMatch('rlc_second_choice', $context->get('rlc_second_choice')); // Select previous choice
        } else {
            $rlc_form->setMatch('rlc_second_choice', -1); // Select the default
        }

        $rlc_form->addDropBox('rlc_third_choice', $rlc_choices_none);
        $rlc_form->addCssClass('rlc_third_choice', 'form-control');
        $rlc_form->setLabel('rlc_third_choice','Third Choice: ');
        if(!is_null($context->get('rlc_third_choice'))) {
            $rlc_form->setMatch('rlc_third_choice', $context->get('rlc_third_choice'));
        } else {
            $rlc_form->setMatch('rlc_third_choice', -1); // Select the default
        }

        // 2. About Your Choices

        if(!is_null($context->get('why_specific_communities'))) {
            $rlc_form->addTextarea('why_specific_communities',$context->get('why_specific_communities'));
        } else {
            $rlc_form->addTextarea('why_specific_communities');
        }
        $rlc_form->setLabel('why_specific_communities',
                            'Why are you interested in the specific communities you have chosen?');

        if(!is_null($context->get('strengths_weaknesses'))) {
            $rlc_form->addTextarea('strengths_weaknesses', $context->get('strengths_weaknesses'));
        } else {
            $rlc_form->addTextarea('strengths_weaknesses');
        }
        $rlc_form->setLabel('strengths_weaknesses',
                            'What are your strengths and in what areas would you like to improve?');

        $rlc_form->mergeTemplate($template);
        $template = $rlc_form->getTemplate();

        return \PHPWS_Template::process($template,'hms','student/rlc_signup_form_page1.tpl');
    }
}
