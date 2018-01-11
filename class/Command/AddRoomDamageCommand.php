<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\CheckinFactory;
use \Homestead\RoomFactory;
use \Homestead\RoomDamage;
use \Homestead\RoomDamageFactory;
use \Homestead\UserStatus;
use \Homestead\Room;
use \Homestead\Exception\PermissionException;

class AddRoomDamageCommand extends Command {

    private $room;

    public function setRoom(Room $room)
    {
        $this->room = $room;
    }

    public function getRequestVars()
    {
        return array('action'=> 'AddRoomDamage',
                     'roomId'=> $this->room->getPersistentId(),
                 );
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::isLogged())
        {
            throw new PermissionException('You must be logged in first.');
        }

        $term = $context->get('term');

        // Load the room based on the term and room id passed in
        $roomId = $context->get('roomPersistentId');
        $room = RoomFactory::getRoomByPersistentId($roomId, $term);

        $username = UserStatus::getUsername();

        // NB: This command is used from both the student self-reporting side, and the admin side
        // If this user is not an admin (i.e. is a student), then we need to check for a check-in and its deadline
        if(!UserStatus::isAdmin()){
            $student = StudentFactory::getStudentByUsername($username, $term);
            $checkin = CheckinFactory::getCheckinByBannerId($student->getBannerId(), $term);
            $end = strtotime(RoomDamage::SELF_REPORT_DEADLINE, $checkin->getCheckinDate());

            if(time() > $end) {
                echo json_encode(array('status' => 'The period to add room damages have passed, as it has been more than 7 days.'));
                exit;
            }
        }

        $damageType = $context->get('damageType');
        $side = $context->get('side');
        $note = $context->get('description');

        $damage = new RoomDamage($room, $term, $damageType, $side, $note);

        RoomDamageFactory::save($damage);

        echo json_encode(array('status' => 'success'));
        exit;
    }
}
