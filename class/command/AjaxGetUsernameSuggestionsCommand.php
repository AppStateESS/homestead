<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetUsernameSuggestionsCommand extends Command {

    private $username;

    public function getRequestVars(){
        return array('action'=>'AjaxGetUsernameSuggestions');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $username = $context->get('username');

        $db = new PHPWS_DB('hms_new_application');

        $db->addColumn('username');
        $db->addColumn('banner_id');
        $db->addWhere('term', Term::getSelectedTerm());
        $db->addWhere('username', '%' . $username . '%', 'ILIKE');
        $db->addOrder('username', 'ASC');
        $db->setLimit(10);

        $results = $db->select();

        $jsonResult = array();

        for($i = 0; $i < sizeof($results); $i++){
            $jsonResult['results'][] = array('id'=>$i, 'value'=>$results[$i]['username'], 'info'=>$results[$i]['banner_id']);
        }

        $context->setContent(json_encode($jsonResult));
    }
}

?>