<?php

/**
 * HMS Pricing Tier class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Pricing_Tier
{

    var $id             = 0;
    var $tier_value     = null;

    public function HMS_Pricing_Tier($id = 0)
    {
        if (!$id) {
            return;
        }

        $this->id = $id;
        $db = new PHPWS_DB('hms_pricing_tiers');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if (!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
        }
    }

    /*******************
    * Static Functions *
    *******************/

    public function get_pricing_tiers()
    {
        $db = new PHPWS_DB('hms_pricing_tiers');
        $db->addOrder('tier_value', 'ASC');

        $db->loadClass('hms', 'HMS_Pricing_Tier.php');
        $result = $db->getObjects('HMS_Pricing_Tier');

        if(PHPWS_Error::logIfError($result)){
            return false;
        } else {
            return $result;
        }
    }

    public function get_pricing_tiers_array()
    {
        $tiers_array = array();
        
        $tiers = HMS_Pricing_Tier::get_pricing_tiers();

        foreach ($tiers as $tier){
            $tiers_array[$tier->id] = $tier->tier_value;
        }

        return $tiers_array;
    }
}

?>
