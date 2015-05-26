<?php

class RoomDamageResponsibility {

    public $id;

    public $banner_id;
    public $damage_id;
    public $state;
    public $amount;
    public $assessed_on;
    public $assessed_by;

    public function __construct(Student $student, RoomDamage $damage)
    {
        $this->banner_id = $student->getBannerId();
        $this->damage_id = $damage->getId();

        $this->state = 'new';
        $this->amount = null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDamageId()
    {
        return $this->damage_id;
    }

    public function getBannerId()
    {
        return $this->banner_id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($name)
    {
        $this->state = $name;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAssessedOn()
    {
        return $this->assessed_on;
    }

    public function setAssessedOn($timestamp)
    {
        $this->assessed_on = $timestamp;
    }

    public function getAssessedBy()
    {
        return $this->assessed_by;
    }

    public function setAssessedBy($username)
    {
        $this->assessed_by = $username;
    }
}

class RoomDamageResponsibilityRestored extends RoomDamageResponsibility {
    public function __construct(){}
}
