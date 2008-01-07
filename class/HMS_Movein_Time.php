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

    function save()
    {
        $db = new PHPWS_DB('hms_movein_time');

        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }
        return true;
    }

    function getRowTags()
    {
        $tags = array();
        $tags['BEGIN_TIMESTAMP']    = HMS_Util::get_long_date_time($this->begin_timestamp);
        $tags['END_TIMESTAMP']      = HMS_Util::get_long_date_time($this->end_timestamp);
        $tags['ACTION']             = PHPWS_Text::secureLink(_('Delete'), 'hms', array('type'=>'movein', 'op'=>'delete_movein_time', 'id'=>$this->id));

        return $tags;
    }

    /******************
     * Static Methods *
     *****************/
     
    function main()
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

    function get_movein_times_array($term = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_selected_term();
        }
        
        $db = new PHPWS_DB('hms_movein_time');

        $db->addWhere('term', $term);
        $db->addOrder('begin_timestamp', 'ASC');
        $result = $db->select();

        if(PEAR::isError($result)){
            return false;
        }

        $timestamps = array();

        foreach ($result as $row){
            $timestamps[$row['id']] = HMS_Util::get_long_date_time($row['begin_timestamp']);
        }

        return $timestamps;
    }

    /*********************
     * Static UI Methods *
     ********************/

    function show_edit_movein_times($success = null, $error = null)
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
        //test(HMS_Movein_Time::get_movein_times_pager());

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

    function create_movein_time()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

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
        $movein_time->term = HMS_Term::get_selected_term();

        $result = $movein_time->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Movein_Time::show_edit_movein_times(NULL, 'There was an error saving the move-in time.');
        }else{
            return HMS_Movein_Time::show_edit_movein_times('Move-in time saved successfully.');
        }
    }

    function delete_movein_time()
    {
        $db = &new PHPWS_DB('hms_movein_time');

        $db->addWhere('id', $_REQUEST['id']);
        //$db->setLimit(1);
        $result = $db->delete();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Movein_Time::show_edit_movein_times(NULL, 'There was an error deleting the move-in time.');
        }else{
            return HMS_Movein_Time::show_edit_movein_times('Move-in time deleted.');
        }
    }
    
    function get_movein_times_pager(){
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $pager = &new DBPager('hms_movein_time', 'HMS_Movein_Time');

        $pager->addWhere('term', HMS_Term::get_selected_term());
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
