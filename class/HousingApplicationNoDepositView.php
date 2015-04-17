<?php

class HousingApplicationNoDepositView extends hms\View {

    private $student;
    private $requiredTerms;

    public function __construct(Student $student, $requiredTerms)
    {
        $this->student      = $student;
        $this->requiredTerms= $requiredTerms;
    }

    public function show()
    {
        $tpl = array();

        $tpl['ENTRY_TERM'] = Term::toString($this->student->getApplicationTerm());
        $tpl['REQUIRED_TERMS'] = array();

        $appsOnFile = HousingApplication::getAllApplications($this->student->getUsername());
        $termsOnFile = array();

        if(isset($appsOnFile) && !is_null($appsOnFile)){
            foreach($appsOnFile as $app) {
                $termsOnFile[] = $app->getTerm();
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

        return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_no_deposit.tpl');
    }
}

?>
