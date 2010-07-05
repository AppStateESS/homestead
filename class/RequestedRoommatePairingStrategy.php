<?php

define('LO_SINGLE_GENDER', 1);
define('LO_COED', 2);

class RequestedRoommatePairingStrategy extends RoommatePairingStrategy{

    public function __construct($term)
    {
        parent::__construct($term);
    }

    function doPairing(&$applications, &$pairs)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('term', $this->term);
        $db->addWhere('confirmed', 1);
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        $roommates = $db->select();

        if(PHPWS_Error::logIfError($roommates)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($roommates->toString());
        }

        foreach($roommates as $pair)
        {
            if(!isset($applications[$pair['requestor']]) || !isset($applications[$pair['requestee']])) {
                // Not to be assigned anyway.
                if(isset($applications[$pair['requestor']])) {
                    echo "Weird: {$pair['requestee']} not in the list but {$pair['requestor']} is. Skipping.\n";
                    unset($applications[$pair['requestor']]);
                }
                if(isset($applications[$pair['requestee']])) {
                    echo "Weird: {$pair['requestor']} not in the list but {$pair['requestee']} is. Skipping.\n";
                    unset($applications[$pair['requestee']]);
                }
                continue;
            }

            $requestor = $applications[$pair['requestor']];
            $requestee = $applications[$pair['requestee']];

            if(!$this->pairAllowed($requestor, $requestee)) {
                echo "Weird: {$pair['requestor']} and {$pair['requestee']} are confirmed roommates but different gender.  Skipping.\n";
                unset($applications[$pair['requestor']]);
                unset($applications[$pair['requestee']]);
                continue;
            }

            $pairs[] = $this->createPairing($requestor, $requestee);

            unset($applications[$pair['requestor']]);
            unset($applications[$pair['requestee']]);
        }
    }
}

?>
