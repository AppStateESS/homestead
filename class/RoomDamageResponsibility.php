<?php

namespace Homestead;

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

    public function reportToStudentAccount()
    {
        $soap = SOAP::getInstance(UserStatus::getUsername(), SOAP::ADMIN_USER);

        $damage = RoomDamageFactory::getDamageById($this->getDamageId());

        $term = $damage->getTerm();
        $description = $damage->getShortDescription();

        $damageType = $damage->getDamageType();

        // Figure out which detail code to use based on the damage type
        if($damageType == 105) {
            // Improper checkout
            $detailCode = 8129;
        } else if($damageType == 78 || $damageType == 79 || $damageType == 80) {
            // Lock recomb
            $detailCode = 8139;
        } else {
            // Regular room damage
            $detailCode = 8138;
        }


        $soap->addRoomDamageToStudentAccount($this->getBannerId(), $term, $this->getAmount(), $description, $detailCode);
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
