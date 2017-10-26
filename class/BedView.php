<?php

namespace Homestead;

use \Homestead\Exception\PermissionException;
use \phpws2\Database;

class BedView extends View
{
    private $hall;
    private $floor;
    private $room;
    private $bed;

    public function __construct(HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room, HMS_Bed $bed)
    {
        $this->hall = $hall;
        $this->floor = $floor;
        $this->room = $room;
        $this->bed = $bed;
    }

    public function show()
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'bed_view')) {
            throw new PermissionException('You are not allowed to edit or view beds.');
        }

        $tpl = array();
        $tpl['TERM'] = Term::toString($this->bed->getTerm());
        $tpl['HALL_NAME'] = $this->hall->getLink();
        $tpl['FLOOR_NUMBER'] = $this->floor->getLink('Floor');
        $tpl['ROOM_NUMBER_LINK'] = $this->room->getLink();
        $tpl['ROOM_NUMBER'] = $this->room->getRoomNumber();
        $tpl['BED_LABEL'] = $this->bed->getBedroomLabel() . ' ' . $this->bed->getLetter();

        // If the room is reserved for room change, don't show the "assign student here" link
        if($this->bed->isRoomChangeReserved()) {
          $tpl['ROOM_CHANGE_RESERVED'] = 'Room Change';
          $tpl['RESERVE_LINK'] = $this->getRoomChangeReservedLink();
        } else {
          $tpl['ASSIGNED_TO'] = $this->bed->get_assigned_to_link();
        }


        $tpl['HALL_ABBR'] = $this->hall->getBannerBuildingCode();

        $submitCmd = CommandFactory::getCommand('EditBed');
        $submitCmd->setBedId($this->bed->id);



        $form = new \PHPWS_Form();
        $submitCmd->initForm($form);
        $form->addText('bedroom_label', $this->bed->bedroom_label);
        $form->setClass('bedroom_label', 'form-control');
        $form->setLabel('bedroom_label', 'Bedroom Label:');

        $form->addText('phone_number', $this->bed->phone_number);
        $form->setMaxSize('phone_number', 4);
        $form->setLabel('phone_number', 'Phone Number');
        $form->setClass('phone_number', 'form-control');

        $form->addText('banner_id', $this->bed->banner_id);
        $form->setClass('banner_id', 'form-control');
        $form->setLabel('banner_id', 'Banner Bed ID:');

        $form->addCheckBox('ra', 1);
        if ($this->bed->isRa()) {
            $form->setExtra('ra', 'checked');
        }
        $form->setLabel('ra', 'Reserved for RA');

        $form->addCheckBox('ra_roommate', 1);

        if ($this->bed->ra_roommate == 1) {
            $form->setExtra('ra_roommate', 'checked');
        }
        $form->setLabel('ra_roommate', 'Hold Empty for RA Roommate');

        $form->addCheckBox('international_reserved');

        if ($this->bed->international_reserved == 1) {
            $form->setExtra('international_reserved', 'checked');
        }

        $form->addSubmit('submit', 'Submit');

        # if the user has permission to view the form but not edit it
        if (!\Current_User::allow('hms', 'bed_view')
                && !\Current_User::allow('hms', 'bed_attributes')
                && !\Current_User::allow('hms', 'bed_structure')) {
            $form_vars = get_object_vars($form);
            $elements = $form_vars['_elements'];

            foreach ($elements as $element => $value) {
                $form->setDisabled($element);
            }
        }
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        \Layout::addPageTitle("Edit Bed");
        $tpl['HISTORY'] = $this->getBedHistoryContent();
        return \PHPWS_Template::process($tpl, 'hms', 'admin/edit_bed.tpl');
    }

    private function getBedHistoryContent()
    {
        $data = $this->getBedHistoryArray();
        if (empty($data)) {
            $tpl = array('rows' => array(), 'message' => 'No previous history');
        } else {
            $tpl = array('rows' => $data, 'message' => null);
        }
        $template = new \phpws2\Template($tpl);
        $template->setModuleTemplate('hms', 'admin/getBedHistoryContent.html');
        return $template->get();
    }

    /**
     * Pulls the assignment history for the current bed and plugs in the student profile link.
     * Returns null if no history is found.
     * @return array
     */
    private function getBedHistoryArray()
    {
        $db = \phpws2\Database::newDB();
        $t1 = $db->addTable('hms_assignment_history');
        $t1->addFieldConditional('bed_id', $this->bed->id);
        /*
          if (isset($this->bed->_curr_assignment)) {
          $t1->addFieldConditional('banner_id', $this->bed->_curr_assignment->banner_id, '!=');
          }
         *
         */
        $t1->addOrderBy('assigned_on', 'DESC');
        $result = $db->select();
        if (empty($result)) {
            return null;
        }
        foreach ($result as $key => $assignment) {
            $student = StudentFactory::getStudentByBannerID($assignment['banner_id'], $this->bed->getTerm());
            $result[$key]['assigned_on_date'] = HMS_Util::get_short_date_time($assignment['assigned_on']);
            if (empty($assignment['removed_on'])) {
                $result[$key]['removed_on_date'] = 'Never';
            } else {
                $result[$key]['removed_on_date'] = HMS_Util::get_short_date_time($assignment['removed_on']);
            }
            $result[$key]['student'] = $student->getProfileLink();
        }
        return $result;
    }

    private function getRoomChangeReservedLink()
    {
        $roomChange = RoomChangeRequestFactory::getCurrentRequestByBed($this->bed);
        $roomChangeCmd = CommandFactory::getCommand('ShowManageRoomChange');
        $roomChangeCmd->setRequestId($roomChange->getId());

        return $roomChangeCmd->getLink('room change request');
    }

}
