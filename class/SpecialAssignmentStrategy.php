<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;

class SpecialAssignmentStrategy extends AssignmentStrategy
{
    protected $specials;

    public function __construct($term)
    {
        parent::__construct($term);

        $db = new \PHPWS_DB('hms_special_assignment');
        $db->addWhere('term', $this->term);
        $result = $db->getObjects('\Homestead\SpecialAssignment');

        if(\PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->getMessage());
        }

        $this->specials = $result;
    }

    public function doAssignment($pair)
    {

        if(is_null($this->specials)){
            return false;
        }

        foreach($this->specials as $special)
        {
            $student = $pair->get($special->username);
            if(is_null($student)) continue;
            $room = $this->roomSearchPlusCoed($student->getGender(), FALSE, $special->hall,
            (is_null($special->floor) ? FALSE : $special->floor),
            (is_null($special->room)  ? FALSE : $special->room));

            if(is_null($room)){
                continue;
            }
            $this->assign($pair, $room);
            return true;
        }

        return false;
    }
}
