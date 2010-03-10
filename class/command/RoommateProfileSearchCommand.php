<?php

class RoommateProfileSearchCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoommateProfileSearch');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');
        
        $tags = array();
        $tags['RESULTS'] = RoommateProfile::profile_search_pager();
        
        $context->setContent(PHPWS_Template::process($tags, 'hms', 'student/profile_search_results.tpl'));
    }
}

?>