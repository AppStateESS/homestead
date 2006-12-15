<?php

class HMS_Pairer
{

    var $id;
    var $roommate_zero;
    var $roommate_one;
    var $roommate_two;
    var $roommate_three;

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_roommate_zero($rz)
    {
        $this->roommate_zero = $rz;
    }

    function get_roommate_zero()
    {
        return $this->roommate_zero;
    }

    function set_roommate_one($ro)
    {
        $this->roommate_one = $ro;
    }

    function get_roommate_one()
    {
        return $this->roommate_one;
    }

    function set_roommate_two($rt)
    {
        $this->roommate_two = $rt;
    }

    function get_roommate_two 
    {
        return $this->roommate_two;
    }

    function set_roommate_three($rt)
    {
        $this->roommate_three = $rt;
    }

    function get_roommate_three()
    {
        return $this->roommate_three;
    }

    function HMS_Pairer($id = NULL)
    {
        if($id == NULL) {
            $this->set_values_null();
        } else {

        }

        return $this;
    }

    function set_values_null()
    {
        $this->set_id(NULL);
        $this->set_roommate_zero(NULL);
        $this->set_roommate_one(NULL);
        $this->set_roommate_two(NULL);
        $this->set_roommate_three(NULL);
    }
};

?>
