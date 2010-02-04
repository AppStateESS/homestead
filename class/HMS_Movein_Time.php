<?php

/**
 * HMS Move-in Time class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Movein_Time
{
    var $id;
    var $begin_timestamp;
    var $end_timestamp;
    var $term;

    /********************
     * Instance Methods *
     *******************/
    public function HMS_Movein_Time($id = NULL){
        
        if(!isset($id) || is_null($id)){
            return;
        }

        $db = new PHPWS_DB('hms_movein_time');
        $db->addWhere('id', $id);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            $this->id = 0;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_movein_time');

        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }
        return true;
    }

    public function get_formatted_begin_end()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        return HMS_Util::get_long_date_time($this->begin_timestamp) . ' - ' . date('gA',$this->end_timestamp);
    }

    public function getRowTags()
    {
        $delete_cmd = CommandFactory::getCommand('DeleteMoveinTime');
        $delete_cmd->setId($this->id);

        $tags = array();
        $tags['BEGIN_TIMESTAMP']    = HMS_Util::get_long_date_time($this->begin_timestamp);
        $tags['END_TIMESTAMP']      = HMS_Util::get_long_date_time($this->end_timestamp);
        $tags['ACTION']             = $delete_cmd->getLink('Delete');

        return $tags;
    }

    /******************
     * Static Methods *
     *****************/
     
    public function main()
    {
        switch($_REQUEST['op'])
        {
            case 'show_edit_movein_times':
                return HMS_Movein_Time::show_edit_movein_times();
                break;
            case 'create_movein_time':
                return HMS_Movein_Time::create_movein_time();
                break;
            case 'delete_movein_time':
                return HMS_Movein_Time::delete_movein_time();
                break;
            default:
                echo "Unknown movein-time op: {$_REQUEST['op']}";
                return;
        }
    }

    public function get_movein_times_array($term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'Term.php');
            $term = Term::getSelectedTerm();
        }
        
        $db = new PHPWS_DB('hms_movein_time');

        $db->addWhere('term', $term);
        $db->addOrder('begin_timestamp', 'ASC');
        $result = $db->select();

        if(PEAR::isError($result)){
            return false;
        }

        $timestamps = array();

        $timestamps[0] = 'None';

        foreach ($result as $row){
            $timestamps[$row['id']] = HMS_Util::get_long_date_time($row['begin_timestamp']) . ' - ' . date('gA',$row['end_timestamp']);
        }

        return $timestamps;
    }

    /*********************
     * Static UI Methods *
     ********************/

    public function show_edit_movein_times($success = null, $error = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['TITLE'] = 'Edit Move-in Times';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        $form = new PHPWS_Form();
        
        $form->addDropBox('begin_day', HMS_Util::get_days());
        $form->addDropBox('begin_month', HMS_Util::get_months());
        $form->addDropBox('begin_year', HMS_Util::get_years_2yr());
        $form->addDropBox('begin_hour', HMS_Util::get_hours());
        
        $form->addDropBox('end_day', HMS_Util::get_days());
        $form->addDropBox('end_month', HMS_Util::get_months());
        $form->addDropBox('end_year', HMS_Util::get_years_2yr());
        $form->addDropBox('end_hour', HMS_Util::get_hours());
        
        $form->addSubmit('submit', 'Create');

        $form->addHidden('type', 'movein');
        $form->addHidden('op', 'create_movein_time');

        $tpl['MOVEIN_TIME_PAGER'] = HMS_Movein_Time::get_movein_times_pager();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_movein_time.tpl');
    }

    public function create_movein_time()
    {
        # Create the timestamp
        $begin_timestamp    = mktime($_REQUEST['begin_hour'], 0, 0, $_REQUEST['begin_month'], $_REQUEST['begin_day'], $_REQUEST['begin_year']);
        $end_timestamp      = mktime($_REQUEST['end_hour'], 0, 0, $_REQUEST['end_month'], $_REQUEST['end_day'], $_REQUEST['end_year']);

        if($end_timestamp <= $begin_timestamp){
            return HMS_Movein_Time::show_edit_movein_times(NULL, 'Error: The ending time must be after the beginning time.');
        }
        
        # Create the new movein time object
        $movein_time = &new HMS_Movein_Time();
        $movein_time->begin_timestamp = $begin_timestamp;
        $movein_time->end_timestamp   = $end_timestamp;
        $movein_time->term = Term::getSelectedTerm();

        $result = $movein_time->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Movein_Time::show_edit_movein_times(NULL, 'There was an error saving the move-in time.');
        }else{
            return HMS_Movein_Time::show_edit_movein_times('Move-in time saved successfully.');
        }
    }

    public function delete()
    {
        $db = &new PHPWS_DB('hms_movein_time');

        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }
        return true;
    }
    
    public function get_movein_times_pager(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = &new DBPager('hms_movein_time', 'HMS_Movein_Time');

        $pager->addWhere('term', Term::getSelectedTerm());
        $pager->db->addOrder('begin_timestamp', 'DESC');

        $pager_tags['BEGIN_TIMESTAMP_LABEL']    = 'Begin Date & Time';
        $pager_tags['END_TIMESTAMP_LABEL']      = 'End Date & Time';
        $pager_tags['ACTION_LABEL']             = 'Action';

        $pager->setModule('hms');
        $pager->setTemplate('admin/movein_time_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No move-in times found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getRowTags');
        $pager->addPageTags($pager_tags);

        return $pager->get();
    }

}

?>
