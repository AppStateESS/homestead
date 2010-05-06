<?php
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

class ListAllowedHallsCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $term = Term::getSelectedTerm();
        $db = new PHPWS_DB('hms_residence_hall');
        $db->addWhere('term', $term);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            exit;
        }
        $permission = new HMS_Permission();

        $data = array();
        foreach($results as $result){
            $somethingEnabled = false;

            $hall        = new HMS_Residence_Hall($result['id']);
            $floors      = $hall->get_floors();
            unset($obj);
            $obj;
            $obj->name   = $hall->getHallName();
            $obj->id     = $hall->getId();
            $obj->floors = array();
            if($permission->verify(Current_User::getUsername(), $hall, 'email')){
                $obj->enabled = true;
                $somethingEnabled = true;
                foreach($floors as $floor){
                    unset($floor_obj);
                    $floor_obj;
                    $floor_obj->name    = "Floor: ".$floor->getFloorNumber();
                    $floor_obj->id      = $floor->getId();
                    $floor_obj->enabled = true;
                    $obj->floors[]      = $floor_obj;
                }
            } else {
                $obj->enabled = false;
                foreach($floors as $floor){
                    unset($floor_obj);
                    $floor_obj;
                    $floor_obj->name    = "Floor: ".$floor->getFloorNumber();
                    $floor_obj->id      = $floor->getId();
                    $floor_obj->enabled = $permission->verify(Current_User::getUsername(), $floor, 'email');
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
?>
