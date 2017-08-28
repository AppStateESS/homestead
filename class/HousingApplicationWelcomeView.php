<?php

namespace Homestead;

class HousingApplicationWelcomeView extends View {

    private $student;
    private $submitCmd;
    private $requiredTerms;

    public function __construct(Student $student, Command $submitCmd, $requiredTerms)
    {
        $this->student		= $student;
        $this->submitCmd	= $submitCmd;
        $this->requiredTerms= $requiredTerms;
    }

    public function show()
    {
        $tpl = array();

        $tpl['ENTRY_TERM'] = Term::toString($this->student->getApplicationTerm());
        $tpl['REQUIRED_TERMS'] = array();

        $appsOnFile = HousingApplication::getAllApplicationsForStudent($this->student);

        # Make a list of the terms the student has completed
        $termsOnFile = array();
        if(isset($appsOnFile) && !is_null($appsOnFile)){
            foreach($appsOnFile as $term=>$app) {
                $termsOnFile[] = $term;
            }
        }

        foreach($this->requiredTerms as $t){
            if($t['required'] == 0){
                continue;
            }

            $completed = '';
            if(in_array($t['term'], $termsOnFile)) {
                $completed = ' <span style="color: #0000AA">(Completed)</span>';
            }

            // If the application is cancelled, overwrite the "complete" text with "cancelled"
            if(isset($appsOnFile[$t['term']]) && $appsOnFile[$t['term']]->isCancelled()){
                $completed = ' <span style="color: #F00">(Cancelled)</span>';
            }

            if(Term::getTermSem($t['term']) == TERM_FALL){
                $tpl['REQUIRED_TERMS'][] = array('REQ_TERM'=>Term::toString($t['term']) . ' - ' . Term::toString(Term::getNextTerm($t['term'])),
                                                 'COMPLETED' => $completed);
            }else{
                $tpl['REQUIRED_TERMS'][] = array('REQ_TERM'=>Term::toString($t['term']),
                                                 'COMPLETED' => $completed);
            }
        }

        $contactCmd = CommandFactory::getCommand('ShowContactForm');

        $tpl['CONTACT_LINK'] = $contactCmd->getLink('contact us');

        # Setup the form for the 'continue' button.
        $form = new \PHPWS_Form;
        $this->submitCmd->initForm($form);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        $studentType = $this->student->getType();

        \Layout::addPageTitle("Welcome");

        if(count($appsOnFile) > 0) {
            // User is now past step one.  No longer just welcoming, we are now welcoming back.
            return \PHPWS_Template::process($tpl, 'hms', 'student/welcome_back_screen.tpl');
        }

        if($studentType == TYPE_FRESHMEN || $studentType == TYPE_NONDEGREE || $this->student->isInternational()){
            return \PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_freshmen.tpl');
        }else{
            return \PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_transfer.tpl');
        }
    }
}
