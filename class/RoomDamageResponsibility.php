<?php

class RoomDamageResponsibility {

    private $id;

    private $bannerId;
    private $damageId;
    private $state;
    private $amount;

    public function __construct(Student $student, RoomDamage $damage)
    {
        $this->bannerId = $student->getBannerId();
        $this->damageId = $damage->getId();

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
        return $this->damageId;
    }

    public function getBannerId()
    {
        return $this->bannerId;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}
?>