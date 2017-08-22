<?php

namespace Homestead;
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class StudentMenuTermBlock {

    private $student;
    private $term;

    public function __construct(Student $student, $term)
    {
        $this->student  = $student;
        $this->term		= $term;
    }

    public function show()
    {
        // Get the enabled features
        $features = ApplicationFeature::getEnabledFeaturesForStudent($this->student, $this->term);

        $tpl = array();

        if(Term::getTermSem($this->term) == TERM_FALL){
            // If it's fall, then it's really the fall & spring terms
            $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        }else{
            $tpl['TERM'] = Term::toString($this->term);
        }

        // In case there are no features enabled for this term
        if(empty($features)){
            $tpl['BLOCKS'][] = array('BLOCK'=>'There are no options currently available to you for this term.');
        }

        foreach($features as $feat){
            $tpl['BLOCKS'][] = array('BLOCK'=>$feat->getMenuBlockView($this->student)->show());
        }

        return \PHPWS_Template::process($tpl, 'hms', 'student/studentMenuTermBlock.tpl');
    }
}
