<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

class ListRolesCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $db = new PHPWS_DB('hms_role');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            echo json_encode(array());
        } else {
            echo json_encode($result);
        }
        exit;
    }
}


