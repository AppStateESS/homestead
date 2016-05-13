<?php

PHPWS_Core::initModClass('hms', 'RlcFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

/**
 * RlcAssignmentView - View class for assigning students to LearningCommunitites.
 *
 * @author Jeremy Booker
 * @package hms
 */
class RlcAssignmentView extends hms\View
{
    private $term; // The terms we're looking at applications for.
    private $rlc; // the rlc to limit this view to
    private $studentType; // The student type to limit this view to

    /**
     * Constructor
     * @param int $term
     * @param HMS_Learning_Community $rlc
     */

    public function __construct($term, HMS_Learning_Community $rlc = null, $studentType = null)
    {
        $this->term = $term;
        $this->rlc = $rlc;
        $this->studentType = $studentType;
    }

    /**
     * @see View::show()
     */
    public function show()
    {
        $tags = array();
        $tags['TERM'] = Term::toString(Term::getSelectedTerm());

        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }

}
