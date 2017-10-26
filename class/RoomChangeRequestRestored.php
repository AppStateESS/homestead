<?php

namespace Homestead;

/**
 * Subclass for resotring RoomChange objects form the database
 * without calling the actual constructor.
 *
 * @author jbooker
 *
 */
class RoomChangeRequestRestored extends RoomChangeRequest {

    /**
     * Emptry constructor to override parent
     */
    public function __construct()
    {
        //TODO Use the RoomChangeRequestStateFactory to load this room change's current state
    }
}
