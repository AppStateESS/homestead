<?php

class HMS_Bed
{

    var $id;
    var $bedroom_id;
    var $occupant;
    var $bed_letter;
    var $banner_id;

    function get_id()
    {
        return $this->id;
    }

    function set_id($id)
    {
        $this->id = $id;
    }

    function get_bedroom_id()
    {
        return $this->bedroom_id;
    }

    function set_bedroom_id($bedroom_id)
    {
        $this->bedroom_id = $bedroom_id;
    }

    function get_occupant()
    {
        return $this->occupant;
    }

    function set_occupant($occupant)
    {
        $this->occupant = $occupant;
    }

    function get_bed_letter()
    {
        return $this->bed_letter;
    }

    function set_bed_letter($bed_letter)
    {
        $this->bed_letter = $bed_letter;
    }

    function get_banner_id()
    {
        return $this->banner_id;
    }

    function set_banner_id($banner_id)
    {
        $this->banner_id = $banner_id;
    }

    

};
?>
