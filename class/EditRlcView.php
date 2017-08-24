<?php

namespace Homestead;

\PHPWS_Core::initCoreClass('DBPager.php');

class EditRlcView extends View {

    public function show(){
        $pager = new DBPager('hms_learning_communities', 'HMS_Learning_Community');
        $pager->db->addOrder('community_name ASC');
        $pager->setModule('hms');
        $pager->setTemplate('admin/rlc_edit_list.tpl');
        $pager->addRowTags('rowTags');

        $addCmd = CommandFactory::getCommand('ShowAddRlc');

        $pageTags = array();

        $pageTags['ADD_LINK'] = $addCmd->getURI();

        $pager->addPageTags($pageTags);

        $this->setTitle('Edit Learning Communities');

        return $pager->get();
    }
}
