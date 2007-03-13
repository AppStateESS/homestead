<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

class HMS_RLC_Assignment{

    var $id;

    var $asu_username;
    var $rlc_id;
    var $assigned_by_user;
    var $assigned_by_initals;

    /**
     * Constructor
     *
     */
    function HMS_RLC_Assignment($user_id = NULL)
    {
        if(isset($user_id)){
            $this->setUserID($user_id);
        }else{
            $this->setUserID($_SESSION['asu_username']);
        }

        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Assignment()','Caught error from init'); 
            return $result;
        }
    }

    function init()
    {

    }

    /**
     * Saves the current Assignment object to the database.
     */
    function save()
    {

    }


}

?>
