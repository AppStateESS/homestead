<?php

PHPWS_Core::initModClass('hms', 'DamageTypeFactory.php');

/**
 * Model class for representing a room damage
 *
 * @author jbooker
 * @package hms
 */
class RoomDamage {

    public $id;
    public $room_persistent_id; // The room the damage occured in
    public $term;               // The term the damage occured in

    public $damage_type; // Reference to the damage type table
    public $side;
    public $note;        // Notes, further description

    public $repaired;    // True if the damage has been fixed

    public $reported_by; // The user who reported the damage
    public $reported_on; // The unix timestamp of when the damage was reported


    // Static var for holding damage_type descriptions
    private static $damageTypes;

    /**
     * Constructor
     *
     * @param HMS_Room $room
     * @param integer $damageType
     * @param string $note
     */
    public function __construct(HMS_Room $room, $damageType, $side, $note)
    {
        $this->id                    = null;
        $this->room_persistent_id    = $room->getPersistentId();
        $this->term                  = $room->getTerm();
        $this->damage_type           = $damageType;
        $this->side                  = $side;
        $this->repaired              = false;
        $this->note                  = $note;
        $this->reported_by           = Current_User::getUsername();
        $this->reported_on           = time();
    }

    public function getRowTags()
    {
        // Get the damage types, if we don't already have them
        if(!isset(self::$damageTypes))
        {
            self::$damageTypes = DamageTypeFactory::getDamageTypeAssoc();
        }

        $row = array();

        $row['CATEGORY']    = $row['DAMAGE_TYPE'] = self::$damageTypes[$this->getDamageType()]['category'];
        $row['DESCRIPTION'] = self::$damageTypes[$this->getDamageType()]['description'];
        $row['SIDE']        = $this->getSide();
        $row['TERM']        = Term::toString($this->getTerm());
        $row['REPORTED_ON'] = HMS_Util::get_long_date($this->getReportedOn());

        return $row;
    }

    /******************************
     * Accessor / Mutator Methods
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getRoomPersistentId()
    {
        return $this->room_persistent_id;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getDamageType()
    {
        return $this->damage_type;
    }

    public function getSide()
    {
        return $this->side;
    }

    public function isRepaired()
    {
        if ($this->repaired) {
            return true;
        } else {
            return false;
        }
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getReportedBy()
    {
        return $this->reported_by;
    }

    public function getReportedOn()
    {
        return $this->reported_on;
    }
}

class RoomDamageDb extends RoomDamage {
    public function __construct(){}
}
?>