<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class StudentMenuWithdrawnTermBlock {

    private $student;
    private $term;

    public function __construct(Student $student, $term)
    {
        $this->student  = $student;
        $this->term		= $term;
    }

    public function show()
    {
        if(Term::getTermSem($this->term) == TERM_FALL){
            // If it's fall, then it's really the fall & spring terms
            $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        }else{
            $tpl['TERM'] = Term::toString($this->term);
        }

        $contactFormLink = CommandFactory::getCommand('ShowContactForm')->getLink('contact University Housing');

        // In case there are no features enabled for this term
        $tpl['BLOCKS'][] = array('BLOCK'=>'Your application has been cancelled for this term. If this is an error please ' . $contactFormLink . '.');

        return PHPWS_Template::process($tpl, 'hms', 'student/studentMenuTermBlock.tpl');
    }
}

?>