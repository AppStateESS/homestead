<?php
/**
  * Wrapper class so the pager works on this table.
  *
  * @author     Daniel West <dwest at tux dot appstate dot edu>
  * @package    modules
  * @subpackage hms
  */

class HMS_Application_Features {

    public function main()
    {
        switch($_REQUEST['op'])
        {
            case 'show_edit_features':
                PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');
                return Application_UI::show_feature_interface();
                break;
            case 'edit_features':
                return HMS_Application_Features::handle_features_submit();
                break;
        }
    }

    public function handle_features_submit()
    {
        # Check permissions
        if( !Current_User::allow('hms', 'edit_features') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'UI/Application_UI.php');

        $result = HMS_Application_Features::save($_REQUEST);

        if($result){
            return Application_UI::show_feature_interface('Feature set updated successfully.');
        }else{
            return Application_UI::show_feature_interface(NULL, 'Error: There was a problem working with the database.');
        }
    }

    public function save($request)
    {
        if(!isset($_REQUEST['selected_term'])){
            return false;
        }

        $features = array(APPLICATION_RLC_APP          => 'RLC Applications',
                          APPLICATION_ROOMMATE_PROFILE => 'Roommate Profile Searching',
                          APPLICATION_SELECT_ROOMMATE  => 'Selecting Roommates');

        for($i = 0; $i < sizeof($features); $i++){
            $db = &new PHPWS_DB('hms_application_features');
            $db->addWhere('term', $request['selected_term']);
            $db->addWhere('feature', $i);
            $result = $db->select();
            $exists = (sizeof($result) > 0 ? true : false);
            unset($result);
            
            $db->reset();
            if(isset($request['feature'][$i])){
                $db->addValue('enabled', 1);
                if($exists){
                    $db->addWhere('term', $request['selected_term']);
                    $db->addWhere('feature', $i);
                    $result = $db->update();
                } else {
                    $db->addValue('term', $request['selected_term']);
                    $db->addValue('feature', $i);
                    $result = $db->insert();
                }
            } else {
                $db->addValue('enabled', 0);
                if($exists){
                    $db->addWhere('term', $request['selected_term']);
                    $db->addWhere('feature', $i);
                    $result = $db->update();
                } else {
                    $db->addValue('term', $request['selected_term']);
                    $db->addValue('feature', $i);
                    $result = $db->insert();
                }
            }
            if(PHPWS_Error::logIfError($result))
                return false;
        }
        return true;
    }

    public function is_feature_enabled($term, $feature){
        $db = &new PHPWS_DB('hms_application_features');
        $db->addWhere('term', $term);
        $db->addWhere('feature', $feature);
        $db->addWhere('enabled', 1);
        $result = $db->select();

        if(PHPWS_Error::logIfError($result) || sizeof($result) == 0){
            return false;
        }

        return true;
    }
}
?>
