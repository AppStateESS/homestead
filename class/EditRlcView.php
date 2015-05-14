<?php

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initCoreClass('DBPager.php');

class EditRlcView extends hms\View {

    function show(){
        $pager = new DBPager('hms_learning_communities', 'HMS_Learning_Community');
        $pager->db->addOrder('community_name ASC');
        $pager->setModule('hms');
        $pager->setTemplate('admin/rlc_edit_list.tpl');
        $pager->addRowTags('rowTags');

        $addCmd = CommandFactory::getCommand('ShowAddRlc');

        $pageTags = array();

        $pageTags['ADD_LINK'] = $addCmd->getLink('<i class="fa fa-plus"></i> Add a Community', null, 'btn btn-success');

        $pager->addPageTags($pageTags);

        $this->setTitle('Edit Learning Communities');

        return $pager->get();
    }
}
?>
