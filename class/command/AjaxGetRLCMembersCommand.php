<?php

namespace Homestead\command;

use \Homestead\Command;

/**
* Controller class for getting membership data in JSON format.
*
* @author Chris Detsch
* @package hms
*/
class AjaxGetRLCMembersCommand {

    public function __construct()
    {

    }

    public function execute()
    {
        if(!Current_User::allow('hms', 'view_rlc_members')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        $rlcId = $_REQUEST['id'];
        $term = Term::getSelectedTerm();
        $memberList = RlcMembershipFactory::getRlcMembersByCommunityId($rlcId, $term);

        $returnData = array();

        foreach ($memberList as $member)
        {
            $rowValues = array();

            $username = $member['username'];
            $rowValues['applicationId'] = $member['application_id'];

            $rowValues['username'] = $username;

            $student = StudentFactory::getStudentByUsername($username, $term);

            $rowValues['name'] = $student->getName();
            $rowValues['bannerId'] = $student->getBannerId();
            $rowValues['gender'] = $student->getPrintableGenderAbbreviation();
            $rowValues['studentType'] = $student->getPrintableType();

            $rlcAssign = HMS_RLC_Assignment::getAssignmentByUsername($username, $term);

            $rowValues['assignmentId'] = $rlcAssign->getId();

            $state = $rlcAssign->getStateName();

            $stateDisplay = '';

            if($state == 'confirmed'){
                $stateDisplay = $state;
            }else if($state == 'declined'){
                $stateDisplay = $state;
            }else if($state == 'new'){
                $stateDisplay = 'not invited';
            }else if($state == 'invited'){
                $stateDisplay = 'pending';
            }else if($state == 'selfselect-invite'){
                $stateDisplay = 'self-select available';
            }else if($state == 'selfselect-assigned'){
                $stateDisplay = 'self-selected';
            }
            $rowValues['status'] = $stateDisplay;

            $roomAssign = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), Term::getSelectedTerm());


            $assignDisplay = 'n/a';

            if(isset($roomAssign)){
                $assignDisplay = $roomAssign->where_am_i();
            }

            $rowValues['assignment'] = $assignDisplay;

            $allRoommates = HMS_Roommate::get_all_roommates($username, $term);
            $roommates = array();

            if(sizeof($allRoommates) > 1) {
                // Don't show all the roommates
                $roommates = "Multiple Requests";
            } elseif(sizeof($allRoommates) == 1) {
                // Get other roommate
                $otherGuy = StudentFactory::getStudentByUsername($allRoommates[0]->get_other_guy($username), $term);

                $profileCmd = CommandFactory::getCommand('ShowStudentProfile');
                $profileCmd->setUsername($otherGuy->getUsername());

                $roommateName = $otherGuy->getName();

                // If roommate is pending then show little status message
                if(!$allRoommates[0]->confirmed) {
                    $roommateName .= " (Pending)";
                }

                $roommate = new \stdClass();
                $roommate->name = $roommateName;
                $roommate->profileUri = $profileCmd->getUri();
                $roommates[] = $roommate;
            }

            $rowValues['roommates'] = $roommates;

            $returnData[] = $rowValues;
        }

        echo json_encode($returnData);
        exit;
    }
}
