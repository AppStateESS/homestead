<?php

abstract class HMS_Item {
    var $id         = 0;
    var $term       = null;

    var $added_on   = 0;
    var $added_by   = 0;

    var $updated_on = 0;
    var $updated_by = 0;

    public function __construct($id=0)
    {
        if(!is_null($id) && is_numeric($id)){
            $this->id = $id;

            if(!$this->load()) {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    //Override this to return a db object pointing at your table
    //abstract public function getDb();

    public function save(){

        $this->stamp();

        $db = $this->getDb();
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function load(){
        if(is_null($this->id) || !is_numeric($this->id) )
            return false;

        $db = $this->getDb();
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function reset()
    {
        $this->id         = 0;
    }

    public function stamp()
    {
        $now = time();

        if(!$this->id) {
            $this->added_on = & $now;
            $this->added_by = Current_User::getId();
        }
        $this->updated_on = & $now;
        $this->updated_by = Current_User::getId();
    }

    public function delete()
    {
        $db = $this->getDb();
        $db->addWhere('id', $this->id);
        //$db->setTestMode();
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return TRUE;
    }

    public function item_tags()
    {
        $tpl = array();

        $tpl['ADDED_ON']     = strftime('%c', $this->added_on);
        $tpl['UPDATED_ON']   = strftime('%c', $this->updated_on);


        $adder = new PHPWS_User($this->added_by);
        $tpl['ADDED_BY']     = $adder->username;

        $updater = new PHPWS_User($this->updated_by);
        $tpl['UPDATED_BY']     = $updater->username;

        $tpl['TERM']         = Term::toString($this->term, true);

        return $tpl;
    }

    /****************************
     * Accessor/Mutator Methods *
     ****************************/
    public function set_id($id){
        $this->id = $id;
    }

    public function get_id(){
        return $this->id;
    }

}
