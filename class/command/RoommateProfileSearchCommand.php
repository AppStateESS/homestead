<?php

class RoommateProfileSearchCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoommateProfileSearch');
    }

    //TODO update all this
    public function execute(CommandContext $context)
    {
        $tags = array();

        $tags['RESULTS'] = HMS_Student_Profile::profile_search_pager();

        return PHPWS_Template::process($tags, 'hms', 'student/profile_search_results.tpl');
    }
}

?>