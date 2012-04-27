<?php

class SpecialAssignment
{
    var $id;
    var $term;
    var $username;
    var $hall;
    var $floor;
    var $room;

    public function __construct($id = 0)
    {
        if(!is_null($id) && is_numeric($id)) {
            $this->id = $id;

            if(!$this->load()) {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_special_assignment');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->getMessage());
        }

        return true;
    }

    public function load()
    {
        if(is_null($this->id) || !is_numeric($this->id))
            return false;

        $db = new PHPWS_DB('hms_special_assignment');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->getMessage());
        }

        return true;
    }
}

?>
