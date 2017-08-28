<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamageResponsibilityFactory.php');
PHPWS_Core::initModClass('hms', 'RoomFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');


class GetRoomDamagesToAssessCommand extends Command {

    public function getRequestVars()
    {
        return array(
                'action' => 'GetRoomDamagesToAssess'
        );
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        if (!isset($term)) {
            throw new \InvalidArgumentException('Missing term.');
        }

        // Get the list of floors which the current user has permission to assess

        // Get the list of role memberships this user has
        $hms_perm = new HMS_Permission();
        $memberships = $hms_perm->getMembership('assess_damage', NULL, UserStatus::getUsername());

        if (empty($memberships)) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException("You do not have permission to assess damages on any residence halls or floors.");
        }

        // Use the roles to instantiate a list of floors this user has access to
        $floors = array();

        foreach ($memberships as $member) {
            if ($member['class'] == 'hms_residence_hall') {
                $hall = new HMS_Residence_Hall($member['instance']);
                if(!is_array($floors)){
                    $floors = array();
                }
                $hallFloors = $hall->getFloors();
                if(!is_array($hallFloors)){
                    $hallFloors = array();
                }
                $floors = array_merge($floors, $hallFloors);
            } else if ($member['class'] == 'hms_floor') {
                $floors[] = new HMS_Floor($member['instance']);
            } else {
                throw new \Exception('Unknown object type.');
            }
        }

        // Remove duplicate floors
        $uniqueFloors = array();
        foreach ($floors as $floor) {
            $uniqueFloors[$floor->getId()] = $floor;
        }

        // Filter the list of floors for just the term we're interested in
        foreach ($uniqueFloors as $k => $f) {
            if ($f->getTerm() != $term) {
                unset($uniqueFloors[$k]);
            }
        }

        // Get the list of damages with pending assessments on those floors
        $damages = RoomDamageFactory::getDamagesToAssessByFloor($uniqueFloors, $term);

        $roomList = array();

        // For each damage, get the list of responsible students
        foreach ($damages as &$dmg) {
            $pId = $dmg->getRoomPersistentId();
            $dmg->responsibilities = RoomDamageResponsibilityFactory::getResponsibilitiesByDmg($dmg);

            foreach ($dmg->responsibilities as &$resp){
                $student = StudentFactory::getStudentByBannerId($resp->getBannerId(), $term);
                $resp->studentName = $student->getName();
            }

            $roomList[$dmg->getRoomPersistentId()][] = $dmg;
        }

        $rooms = array();

        foreach ($roomList as $pId => $dmgList) {
            $roomObj = RoomFactory::getRoomByPersistentId($pId, $term);
            $roomObj->hallName   = $roomObj->get_parent()->get_parent()->getHallName();
            $roomObj->damages = $dmgList;
            $rooms[] = $roomObj;
        }

        // JSON enocde it all and send it to Angular
        $context->setContent(json_encode($rooms));
    }
}
