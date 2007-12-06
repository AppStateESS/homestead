<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS_Process_Unit.php');

class HMS_Process_Remove_Unit extends HMS_Process_Unit {
    var $asu_username = null;
    var $building_code = null;
    var $bed_code = null;
    var $term = 0;

    function HMS_Process_Remove_Unit($id = 0)
    {
        $this->construct($id, 'hms_remove_queue');
    }

    function save()
    {
        $db = new PHPWS_DB('hms_remove_queue');

        $this->stamp();

        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            return false;
        }
        return true;
    }

    function queue_enabled()
    {
        return PHPWS_Settings::get('hms', 'remove_queue_enabled');
    }

    function enable_queue()
    {
        PHPWS_Settings::set('hms', 'remove_queue_enabled', TRUE);
        PHPWS_Settings::save('hms');
    }

    function disable_queue()
    {
        // TODO: If not empty, error
        PHPWS_Settings::set('hms', 'remove_queue_enabled', FALSE);
        PHPWS_Settings::save('hms');
    }

    function queue_remove_assignment($username, $term, $bldg, $bed)
    {
        $entry                = new HMS_Process_Assign_Unit();
        $entry->asu_username  = $username;
        $entry->building_code = $bldg;
        $entry->bed_code      = $bed;
        $entry->term          = $term;

        if(!HMS_Process_Remove_Unit.php::queue_enabled()) {
            return $entry->process();
        }

        $entry->save();

        return 0;
    }

    function process()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $result = HMS_SOAP::remove_room_assignment(
            $this->asu_username,
            $this->term,
            $this->building_code,
            $this->bed_code);

        if($result == 0) {
            HMS_Activity_Log::log_activity($this->asu_username, ACTIVITY_ASSIGNMENT_REPORTED, Current_User::getUsername(), $this->term . ' ' . $this->building_code . ' ' . $this->bed_code);
        }

        return $result;
    }
    
}

?>
