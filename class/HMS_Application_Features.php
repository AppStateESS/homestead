<?php
/**
  * Wrapper class so the pager works on this table.
  *
  * @author     Daniel West <dwest at tux dot appstate dot edu>
  * @package    modules
  * @subpackage hms
  */

class HMS_Application_Features {

    function save($request)
    {
        $features = array(APPLICATION_RLC_APP          => 'RLC Applications',
                          APPLICATION_ROOMMATE_PROFILE => 'Roommate Profile Searching',
                          APPLICATION_SELECT_ROOMMATE  => 'Selecting Roommates');

        for($i = 0; $i < sizeof($features); $i++){
            $db = &new PHPWS_DB('hms_application_features');
            $db->addWhere('term', $request['term']);
            $db->addWhere('feature', $i);
            $result = $db->select();
            $exists = (sizeof($result) > 0 ? true : false);
            unset($result);
            
            $db->reset();
            if(isset($request['feature'][$i])){
                $db->addValue('enabled', 1);
                if($exists){
                    $db->addWhere('term', $request['term']);
                    $db->addWhere('feature', $i);
                    $result = $db->update();
                } else {
                    $db->addValue('term', $request['term']);
                    $db->addValue('feature', $i);
                    $result = $db->insert();
                }
            } else {
                $db->addValue('enabled', 0);
                if($exists){
                    $db->addWhere('term', $request['term']);
                    $db->addWhere('feature', $i);
                    $result = $db->update();
                } else {
                    $db->addValue('term', $request['term']);
                    $db->addValue('feature', $i);
                    $result = $db->insert();
                }
            }
            if(PHPWS_Error::logIfError($result))
                return false;
        }
        return true;
    }
}
?>
