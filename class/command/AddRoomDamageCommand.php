<?php

PHPWS_Core::initModClass('hms', 'RoomFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamage.php');

class AddRoomDamageCommand extends Command {

    private $room;

    public function setRoom(HMS_Room $room)
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
        if(UserStatus::isUser())
        {
            $term = $context->get('term');

            // Load the room based on the term and room id passed in
            $roomId = $context->get('roomPersistentId');
            $room = RoomFactory::getRoomByPersistentId($roomId, $term);

            $username = UserStatus::getUsername();
            $student = StudentFactory::getStudentByUsername($username, $term);
            $checkin = CheckinFactory::getCheckinByBannerId($student->getBannerId(), $term);
            $end = strtotime('+7 days', $checkin->getCheckinDate());

            if(time() > $end) {
                echo json_encode(array('status' => 'The period to add room damages have passed, as it has been more than 48 hours.'));
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
