<?php

namespace Homestead\report\SingleGenderVsCoedPref;

use \PHPWS_Error;
use \PHPWS_DB;

/**
 * SingleGenderVsCoedPref
 *
 * Report class which calculates the number of male and female students
 * that preferred single-gender or coed housing assignments (as listed
 * on thier housing applications).
 *
 * @author jbooker
 * @package HMS
 */
class SingleGenderVsCoedPref extends Report{

    const friendlyName = 'Single Gender Vs Coed Preference';
    const shortName = 'SingleGenderVsCoedPref';

    private $term;

    // Member variables
    private $maleSingle;
    private $maleCoed;
    private $femaleSingle;
    private $femaleCoed;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        // Initialize member variables
        //TODO
    }

    public function execute()
    {
        $semester = Term::getTermSem(Term::getSelectedTerm());

        if($semester != TERM_FALL && $semester != TERM_SPRING){
            throw new InvalidArgumentException('Term must be Fall or Spring.');
        }

        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');

        $table2 = $semester == TERM_FALL ? 'hms_fall_application' : 'hms_spring_application';

        /*
         * Male Coed total
        */
        $db = new PHPWS_DB('hms_new_application');
        $db->addJoin('left', 'hms_new_application', $table2, 'id', 'id');
        $db->addWhere($table2.'.lifestyle_option', COED);

        $db->addWhere('term', $this->term);
        $db->addWhere('gender', MALE);
        $db->addWhere('student_type', TYPE_FRESHMEN);
        $db->addColumn('id', null, 'total', TRUE);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        $this->maleCoed = $result['total'];

        /*
         * Male Single Gender total
        */
        $db = new PHPWS_DB('hms_new_application');
        $db->addJoin('left', 'hms_new_application', $table2, 'id', 'id');
        $db->addWhere($table2.'.lifestyle_option', COED, '<>'); // <> == '!=';

        $db->addWhere('term', $this->term);
        $db->addWhere('gender', MALE);
        $db->addWhere('student_type', TYPE_FRESHMEN);
        $db->addColumn('id', null, 'total', TRUE);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        $this->maleSingle = $result['total'];

        /*
         * Female Coed total
        */
        $db = new PHPWS_DB('hms_new_application');
        $db->addJoin('left', 'hms_new_application', $table2, 'id', 'id');
        $db->addWhere($table2.'.lifestyle_option', COED);

        $db->addWhere('term', $this->term);
        $db->addWhere('gender', FEMALE);
        $db->addWhere('student_type', TYPE_FRESHMEN);
        $db->addColumn('id', null, 'total', TRUE);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        $this->femaleCoed = $result['total'];

        /*
         * Female Single Gender
        */
        $db = new PHPWS_DB('hms_new_application');
        $db->addJoin('left', 'hms_new_application', $table2, 'id', 'id');
        $db->addWhere($table2.'.lifestyle_option', COED, '<>'); // <> == '!=';

        $db->addWhere('term', $this->term);
        $db->addWhere('gender', FEMALE);
        $db->addWhere('student_type', TYPE_FRESHMEN);
        $db->addColumn('id', null, 'total', TRUE);
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        $this->femaleSingle = $result['total'];
    }

    /****************************
     * Accessor/Mutator Methods *
    ****************************/

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function getMaleSingle(){
        return $this->maleSingle;
    }

    public function getMaleCoed(){
        return $this->maleCoed;
    }

    public function getFemaleSingle(){
        return $this->femaleSingle;
    }

    public function getFemaleCoed(){
        return $this->femaleCoed;
    }
}
