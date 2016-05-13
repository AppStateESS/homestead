<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetCommunitiesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetCommunities');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

        $term = Term::getSelectedTerm();

        $communities = RlcFactory::getRlcList($term);

        $keys = array_keys($communities);

        $communityNodes = array();


        foreach($keys as $cId)
        {
            $communityName = $communities[$cId];
            $node = array('cId'     => $cId,
                          'cName'   => $communityName);
            $communityNodes[] = $node;
        }

        echo json_encode($communityNodes);
        exit;
    }
}
