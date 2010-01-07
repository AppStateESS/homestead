<?php

PHPWS_Core::initModClass('hms', 'View.php');

class HallOverview extends View {

	private $hall;
	private $nakedDisplay;

	public function __construct(HMS_Residence_Hall $hall, $nakedDisplay = FALSE){
		$this->hall = $hall;
		$this->nakedDisplay = $nakedDisplay;
	}

	public function show()
	{
		PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
		PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
		PHPWS_Core::initModClass('hms', 'StudentFactory.php');

		$tpl = new PHPWS_Template('hms');

		if(!$tpl->setFile('admin/reports/hall_overview.tpl')){
			return 'Template error.';
		}

		$rlcs       = HMS_Learning_Community::getRLCList();
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

				if($room->ra_room){
					$extra_attribs .= 'RA ';
				}

				if($room->private_room){
					$extra_attribs .= 'Private ';
				}

				if($room->is_overflow){
					$extra_attribs .= 'Overflow ';
				}

				if($room->is_medical){
					$extra_attribs .= 'Medical ';
				}

				if($room->is_reserved){
					$extra_attribs .= 'Reserved ';
				}

				if(!$room->is_online){
					$extra_attribs .= 'Offline ';
				}

				$room->loadBeds();
				$bed_labels = array();


				foreach($room->_beds as $bed) {
					$bed->loadAssignment();
					$tpl->setCurrentBlock('bed_repeat');

					$bed_link = $bed->getLink();

					if(isset($bed->_curr_assignment)){
						$username = $bed->_curr_assignment->asu_username;
                        try {
						$student = StudentFactory::getStudentByUsername($username, $this->hall->term);
                        }catch(StudentNotFoundException $e){
                            $student = null;
                            NQ::simple('hms', HMS_NOTIFICATION_WARNING, "Could not find data for: $username");
                        }

						$assign_rlc  = HMS_RLC_Assignment::check_for_assignment($username, $this->hall->term); //false or index
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
						    $tpl->setData(array('BED_LABEL'=>$bed->bedroom_label,'BED'=>$bed_link,'NAME'=>$student->getFullNameProfileLink(), 'USERNAME'=>$student->getUsername(), 'BANNER_ID'=>$student->getBannerId(), 'TOGGLE'=>$class, 'RLC_ABBR'=>$rlc_abbr));
                        }
					}else{
						$tpl->setData(array('BED_LABEL'=>$bed->bedroom_label,'BED'=>$bed_link,'NAME'=>$bed->get_assigned_to_link(), 'TOGGLE'=>'vacant'));
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
			Layout::nakedDisplay($tpl->get(), 'Building overview for ' . $this->hall->hall_name, TRUE);
		}
		return $tpl->get();
	}
}
