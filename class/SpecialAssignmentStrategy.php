<?php

PHPWS_Core::initModClass('hms', 'SpecialAssignment.php');

class SpecialAssignmentStrategy extends Assignmentstrategy
{
    protected $specials;

    public function __construct($term)
    {
        parent::__construct($term);

        $db = new PHPWS_DB('hms_special_assignment');
        $db->addWhere('term', $this->term);
        $result = $db->getObjects('SpecialAssignment');

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->getMessage());
        }

        $this->specials = $result;
    }

    public function init(&$pairs)
    {
        $offset = 0;

        // Find these kids and bubble them to the top to ensure assignment
        foreach($this->specials as $special) {
            for($i = $offset; $i < count($pairs); $i++) {
                $pair = $pairs[$i];
                if(!is_null($pair->get($special->username))) {
                    $temporary = $pairs[$offset];
                    $pairs[$offset] = $pairs[$i];
                    $pairs[$i] = $temporary;
                    $offset++;
                    break;
                }
            }
        }
    }

    public function doAssignment($pair)
    {
        foreach($this->specials as $special)
        {
            $student = $pair->get($special->username);
            if(is_null($student)) continue;

            $room = $this->roomSearchPlusCoed($student->getGender(), FALSE, $special->hall,
                (is_null($special->floor) ? FALSE : $special->floor),
                (is_null($special->room)  ? FALSE : $special->room));
            $this->assign($pair, $room);
            return true;
        }

        return false;
    }
}

?>
