<?php

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class FloorAssignmentView extends hms\View{

    private $floor;

    public function __construct(HMS_Floor $floor){
        $this->floor = $floor;
    }

    public function show(){
        $tpl = new PHPWS_Template('hms');
        $tpl->setFile('admin/floor_assignment.tpl');
        $this->floor->loadRooms();

        javascript('jquery_ui');
        javascript('modules/hms/floor_assignment');
        Layout::addStyle('hms', 'css/autosuggest2.css');

        $this->floor->loadHall();
        $hall = $this->floor->_hall;

        $tpl->setCurrentBlocK('title');
        $tpl->setData(array('TITLE'=>HMS_Util::ordinal($this->floor->getFloorNumber()). ' Floor - ' . $hall->getHallName() . ' - ' . Term::getPrintableSelectedTerm()));

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
