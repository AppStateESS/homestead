<?php

namespace Homestead\Command;

use \Homestead\HMS_Permission;
use \Homestead\Term;
use \Homestead\UserStatus;

class ListAllowedHallsCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $term = Term::getSelectedTerm();

        $messageAll = \Current_User::allow('hms', 'email_all');

        $db = new \PHPWS_DB('hms_residence_hall');
        $db->addWhere('term', $term);
        $results = $db->getObjects('\Homestead\HMS_Residence_Hall');

        if(\PHPWS_Error::logIfError($results) || is_null($results)){
            $errorMsg = array();

            if(is_null($results)){
                $errorMsg['error'] = 'You do not have permission to message any halls, sorry.';
            } else {
                $errorMsg['error'] = 'There was a problem reading the database, please try reloading the page.  If the problem persists contact ESS.';
            }

            echo json_encode($errorMsg);
            exit;
        }

        $permission = new HMS_Permission();
        $data = array();

        foreach($results as $hall){
            $somethingEnabled = false;

            $floors      = $hall->get_floors();
            unset($obj);
            $obj = new \stdClass();
            $obj->name   = $hall->getHallName();
            $obj->id     = $hall->getId();
            $obj->floors = array();
            //$blah = 'Verify: ' . ($permission->verify(UserStatus::getUsername(), $hall, 'email') ? 'true' : 'false');
            if($permission->verify(UserStatus::getUsername(), $hall, 'email') || $messageAll){
                $obj->enabled = true;
                $somethingEnabled = true;
                foreach($floors as $floor){
                    unset($floor_obj);
                    $floor_obj = new \stdClass();
                    $floor_obj->name    = "Floor: ".$floor->getFloorNumber();
                    $floor_obj->id      = $floor->getId();
                    $floor_obj->enabled = true;
                    $obj->floors[]      = $floor_obj;
                }
            } else {
                $obj->enabled = false;
                foreach($floors as $floor){
                    unset($floor_obj);
                    $floor_obj = new \stdClass();
                    $floor_obj->name    = "Floor: ".$floor->getFloorNumber();
                    $floor_obj->id      = $floor->getId();
                    $floor_obj->enabled = $permission->verify(\Current_User::getUsername(), $floor, 'email');
                    $obj->floors[]      = $floor_obj;

                    if($floor_obj->enabled)
                        $somethingEnabled = true;
                }
            }
            if($somethingEnabled)
                $data[] = $obj;
        }

        echo json_encode($data);
        exit;
    }
}
