<?php

require_once('XML/Serializer.php');

class HMS_XML{


    function main(){

        $op = $_REQUEST['op'];

        switch($op){
            case 'get_halls':
            
                HMS_XML::getHalls();
                break;

            case 'get_floors':
            
                if(!isset($_REQUEST['hall_id'])){
                    # TODO: Find a way to throw an error here
                }else{
                    HMS_XML::getFloors($_REQUEST['hall_id']);
                }
                break;
                
            case 'get_rooms':
            
                if(!isset($_REQUEST['floor_id'])){
                    # TODO: Find a way to throw an error here
                }else{
                    HMS_XML::getRooms($_REQUEST['floor_id']);
                }
                break;
                
            default:
                # No such 'op', or no 'op' specified
                # TODO: Find a way to throw an error here
                break;
        }

    }

    function getHalls(){
        
        # Connect to the database
        $db = &new PHPWS_DB('hms_residence_hall');
        #test($db);

        $db->addColumn('id');
        $db->addColumn('hall_name',NULL,'name');
        $db->addWhere('deleted', '1', '!=');
        $result = $db->select();

        #test($result,1);

        if(PEAR::isError($result)){
            exit;
        }

        $serializer_options = array (
            'addDecl' => TRUE,
            'encoding' => 'ISO-8859-1',
            'indent' => '  ',
            'rootName' => 'halls',
            'defaultTagName' => 'hall',); 

        $serializer = &new XML_Serializer($serializer_options);

        $status = $serializer->serialize($result);

        if(PEAR::isError($status)){
                die($status->getMessage());
        }

        header('Content-type: text/xml');
        echo $serializer->getSerializedData();
        exit;
    }

    function getFloors($building_id){

        # Connect to the database
        $db = &new PHPWS_DB('hms_floor');

        $db->addColumn('id');
        $db->addColumn('floor_number',NULL,'floorNum');

        $db->addWhere('building',$building_id,'=');
        $db->addWhere('deleted', '1', '!=');
        $result = $db->select();

        #test($result,1);

        if(PEAR::isError($result)){
            exit;
        }
        
        $serializer_options = array (
            'addDecl' => TRUE,
            'encoding' => 'ISO-8859-1',
            'indent' => '  ',
            'rootName' => 'floors',
            'defaultTagName' => 'floor',);

        $serializer = &new XML_Serializer($serializer_options);

        $status = $serializer->serialize($result);

        if(PEAR::isError($status)){
            die($status->getMessage());
        }

        header('Content-type: text/xml');
        echo $serializer->getSerializedData();
        exit;
    }

    function getRooms($floor_id){
        
        # Connect to the database
        $db = &new PHPWS_DB('hms_room');

        $db->addColumn('id');
        $db->addColumn('room_number',NULL,'roomNum');

        $db->addWhere('floor_id',$floor_id,'=');
        $db->addWhere('deleted', '1', '!=');
        $result = $db->select();

        #test($result,1);

        if(PEAR::isError($result)){
            exit;
        }
        
        $serializer_options = array (
            'addDecl' => TRUE,
            'encoding' => 'ISO-8859-1',
            'indent' => '  ',
            'rootName' => 'rooms',
            'defaultTagName' => 'room',);

        $serializer = &new XML_Serializer($serializer_options);

        $status = $serializer->serialize($result);

        if(PEAR::isError($status)){
            die($status->getMessage());
        }

        header('Content-type: text/xml');
        echo $serializer->getSerializedData();
        exit;
    }
}

?>
