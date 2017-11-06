<?php

namespace Homestead;

use \Homestead\Exception\StudentNotFoundException;

/**
 * @author jbooker
 * @package hms
 */

class HallOverview extends View{

    private $hall;
    private $nakedDisplay;

    public function __construct(ResidenceHall $hall, $nakedDisplay = FALSE){
        $this->hall = $hall;
        $this->nakedDisplay = $nakedDisplay;
    }

    public function show()
    {
        $tpl = new \PHPWS_Template('hms');

        if(!$tpl->setFile('admin/reports/hall_overview.tpl')){
            return 'Template error.';
        }

        $rlcs       = HMS_Learning_Community::getRlcList();
        $rlcs_abbr  = HMS_Learning_Community::getRLCListAbbr();

        $tpl->setData(array('HALL'=>$this->hall->hall_name, 'TERM'=>Term::getPrintableSelectedTerm()));

        if($this->nakedDisplay) {

            $menuCmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
            $tpl->setData(array('MAINTENANCE'=>$menuCmd->getLink('Main Menu')));
        }

        $class = 'toggle1';

        $this->hall->loadFloors();
        foreach ($this->hall->_floors as $floor) {

            $floor->loadRooms();
            if(!isset($floor->_rooms)){
                continue;
            }

            if($floor->rlc_id != NULL){
                $floor_rlc = $rlcs[$floor->rlc_id];
            }else{
                $floor_rlc = '';
            }

            foreach($floor->_rooms as $room) {
                $extra_attribs = '';

                if($room->isOffline()){
                    $extra_attribs .= 'Offline ';
                }

                if($room->isReserved()){
                    $extra_attribs .= 'Reserved ';
                }

                if($room->isRa()){
                    $extra_attribs .= 'RA ';
                }

                if($room->isPrivate()){
                    $extra_attribs .= 'Private ';
                }

                if($room->isOverflow()){
                    $extra_attribs .= 'Overflow ';
                }

                if($room->isParlor()){
                    $extra_attribs .= 'Parlor ';
                }

                if($room->isADA()){
                    $extra_attribs .= 'ADA';
                }

                if($room->isHearingImpaired()){
                    $extra_attribs .= 'Hearing Impaired';
                }

                if($room->bathEnSuite()){
                    $extra_attribs .= 'Bath en Suite';
                }

                $room->loadBeds();

                if(empty($room->_beds)){
                    $tpl->setCurrentBlock('room_repeat');
                    $tpl->setData(array('EXTRA_ATTRIBS'=>$extra_attribs, 'ROOM_NUMBER'=>$room->getLink('Room')));
                    $tpl->parseCurrentBlock();
                    continue;
                }

                foreach($room->_beds as $bed) {
                    $bed->loadAssignment();
                    $tpl->setCurrentBlock('bed_repeat');

                    $bed_link = $bed->getLink();

                    if(isset($bed->_curr_assignment)){
                        $username = $bed->_curr_assignment->asu_username;
                        try {
                            $student = StudentFactory::getStudentByBannerId($bed->_curr_assignment->getBannerId(), $this->hall->term);
                        }catch(StudentNotFoundException $e){
                            $student = null;
                            \NQ::simple('hms', NotificationView::WARNING, "Could not find data for: $username");
                        }

                        $assign_rlc  = HMS_RLC_Assignment::checkForAssignment($username, $this->hall->term); //false or index
                        if($assign_rlc != FALSE){
                            $rlc_abbr = $rlcs_abbr[$assign_rlc['rlc_id']]; //get the abbr for the rlc
                        }else{
                            $rlc_abbr = '';
                        }

                        // Alternating background colors
                        if($class == 'toggle1'){
                            $class = 'toggle2';
                        }else{
                            $class = 'toggle1';
                        }

                        if(is_null($student)){
                            $tpl->setData(array('BED_LABEL'=>$bed->bedroom_label,'BED'=>$bed_link,'NAME'=>'UNKNOWN', 'USERNAME'=>$username, 'BANNER_ID'=>'', 'TOGGLE'=>$class, 'RLC_ABBR'=>$rlc_abbr));
                        }else{
                            $tpl->setData(array('BED_LABEL'=>$bed->bedroom_label,'BED'=>$bed_link,'NAME'=>$student->getProfileLink(), 'USERNAME'=>$student->getUsername(), 'BANNER_ID'=>$student->getBannerId(), 'TOGGLE'=>$class, 'RLC_ABBR'=>$rlc_abbr));
                        }
                    }else{
                        $tpl->setData(array('BED_LABEL'=>$bed->bedroom_label,'BED'=>$bed_link,'NAME'=>$bed->get_assigned_to_link(), 'VACANT'=>''));
                    }

                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock('room_repeat');
                $tpl->setData(array('EXTRA_ATTRIBS'=>$extra_attribs, 'ROOM_NUMBER'=>$room->getLink('Room')));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('floor_repeat');
            $tpl->setData(array('FLOOR_NUMBER' =>$floor->getLink('Floor'), 'FLOOR_RLC'=>$floor_rlc));
            $tpl->parseCurrentBlock();
        }

        if($this->nakedDisplay) {
            \Layout::nakedDisplay($tpl->get(), 'Building overview for ' . $this->hall->hall_name, TRUE);
        }

        \Layout::addPageTitle("Hall Overview");

        return $tpl->get();
    }
}
