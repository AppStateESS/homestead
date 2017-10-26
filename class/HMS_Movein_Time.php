<?php

namespace Homestead;

use \PHPWS_Error;
use \PHPWS_DB;

/**
 * HMS Move-in Time class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package HMS
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
    public function __construct($id = NULL){

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

    public function delete()
    {
        $db = new PHPWS_DB('hms_movein_time');

        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }
        return true;
    }

    public function get_formatted_begin_end()
    {
        // Check for multi-day move in time. If days-of-month aren't equal then it'multi-day move in.
        if(date('d', $this->begin_timestamp) != date('d', $this->end_timestamp)){
            // Multi-day move in time.
            return HMS_Util::get_long_date_time($this->begin_timestamp) . ' through '
                . HMS_Util::get_long_date_time($this->end_timestamp);
        }else{
            // Single day move in time.
            return HMS_Util::get_long_date_time($this->begin_timestamp) . ' - ' . date('gA',$this->end_timestamp);
        }
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

    public static function get_movein_times_array($term = NULL)
    {
        if(!isset($term)){
            $term = Term::getSelectedTerm();
        }

        $db = new PHPWS_DB('hms_movein_time');

        $db->addWhere('term', $term);
        $db->addOrder('begin_timestamp', 'ASC');
        $result = $db->getObjects('\Homestead\HMS_Movein_Time');

        if(\PEAR::isError($result)){
            return false;
        }

        $timestamps = array();

        $timestamps[0] = 'None';

        if(!empty($result)){
           foreach ($result as $movein){
            $timestamps[$movein->id] = $movein->get_formatted_begin_end();
           }
        }

        return $timestamps;
    }

    public static function get_movein_times_pager(){
        $pager = new \DBPager('hms_movein_time', '\Homestead\HMS_Movein_Time');

        $pager->addWhere('term', Term::getSelectedTerm());
        $pager->db->addOrder('begin_timestamp', 'DESC');

        $pager_tags = array();

        $pager_tags['BEGIN_TIMESTAMP_LABEL']    = 'Begin Date & Time';
        $pager_tags['END_TIMESTAMP_LABEL']      = 'End Date & Time';
        $pager_tags['ACTION_LABEL']             = 'Action';

        $pager->setModule('hms');
        $pager->setTemplate('admin/movein_time_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No move-in times found.");
        $pager->addRowTags('getRowTags');
        $pager->addPageTags($pager_tags);

        return $pager->get();
    }
}
