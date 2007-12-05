<?php

/**
 * Queues up assignments so if we can't SOAP it over to Banner, Housing
 * can still do their jobs
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HMS_Process_Unit {

    var $id = 0;
    var $queued_on = 0;
    var $queued_by = null;

    /**
     * Loads this Unit from the database
     */
    function construct($id=0, $table)
    {
        if(!$id) {
            return;
        }

        $this->id = $id;
        $db = new PHPWS_DB($table);
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
        }
    }

    /**
     * Resets this process item to zero values
     */
    function reset()
    {
        $this->id        = 0;
        $this->queued_on = 0;
        $this->queued_by = null;
    }

    /**
     * Sets up the queuer and the timestamp
     */
    function stamp()
    {
        $this->queued_on = mktime();
        $this->queued_by = Current_User::getId();
    }

    function set_id($id) {
        $this->id = $id;
    }

    function get_id() {
        return $this->id;
    }

?>
