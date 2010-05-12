<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

class FloorAssignmentView extends View {
    protected $floor;

    public function __construct($floor=0){
        $this->floor = new HMS_Floor($floor);
    }

    public function show(){
        $tpl = new PHPWS_Template('hms');
        $tpl->setFile('admin/floor_assignment.tpl');
        $this->floor->loadRooms();

        javascript('jquery_ui');
        javascript('modules/hms/floor_assignment');
        Layout::addStyle('hms', 'css/autosuggest2.css');

        foreach($this->floor->_rooms as $room){
            $room->loadBeds();
            foreach($room->_beds as $bed){
                $tpl->setCurrentBlock('bed-list');
                $tpl->setData(array('BED_ID'=>$bed->id));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('room-list');
            $tpl->setData(array('ROOM'=>$room->room_number));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }
}
?>
