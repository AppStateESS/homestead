<?php
/**
  * Wrapper class for the hms_term_applications table.
  * TODO: give this class/table a better name
  *
  * @author     Daniel West <dwest at tux dot appstate dot edu>
  * @package    mod
  * @subpackage hms
  */
class HMS_Term_Applications {
    var $app_term;
    var $term;
    var $required;

    function getPager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = &new DBPager('hms_term_applications', 'HMS_Term_Applications');
        $pager->setModule('hms');
        $pager->addRowTags('get_row_tags');
        $pager->setTemplate('admin/set_application_terms.tpl');
        $pager->setEmptyMessage('No rows returned.');
        $pager->setOrder('app_term', 'desc');

        return $pager;
    }

    function get_row_tags()
    {
        $tpl['APP_TERM']    = HMS_Term::term_to_text($this->app_term,   true);
        $tpl['TERM']        = HMS_Term::term_to_text($this->term,       true);
        $tpl['REQUIRED']    = ($this->required == 1 ? 'yes' : 'no'); 
        $tpl['DELETE']      = '<a href=index.php?module=hms&type=term&op=show_term_association&delete='.$this->app_term.'>Delete</a>';

        return $tpl;
    }

    function remove($app_term)
    {
        $db = &new PHPWS_DB('hms_term_applications');
        $db->addWhere('app_term', $app_term);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }
}
?>
