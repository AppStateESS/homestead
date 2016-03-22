<?php

class RoommateProfileSearchCommand extends Command {

    private $term;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action' => 'RoommateProfileSearch',
                     'term'   => $this->term);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');

        $term = $context->get('term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $student->getApplicationTerm());

        $tags = array();
        $tags['RESULTS'] = RoommateProfileSearchView::profile_search_pager($term);

        if($student->isHonors())
        {
            $tags['HONORS'] = 'You have been accepted into The Honors College, as a result you will only be able to be roommates with other Honors students.  Your results have been filtered to reflect this.';
        }

        $context->setContent(PHPWS_Template::process($tags, 'hms', 'student/profile_search_results.tpl'));
    }
}
