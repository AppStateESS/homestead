<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initCoreClass('DBPager.php');

class EditRlcView extends View {

    function show(){
        $pager = new DBPager('hms_learning_communities', 'HMS_Learning_Community');
        $pager->setModule('hms');
        $pager->setTemplate('admin/rlc_edit_list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('rowTags');

        return $pager->get();
    }
}
?>
