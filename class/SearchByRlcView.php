<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class SearchByRlcView extends View {

    public function show(){
        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form;
        $form->addDropBox('rlc', HMS_Learning_Community::getRlcList());
        $form->addHidden('module', 'hms');
        $form->addHidden('action', 'ShowSearchByRlc');
        $form->addSubmit('submit', _('Search!'));

        $tags = $form->getTemplate();
        $tags['TITLE'] = "RLC Search";

        Layout::addPageTitle("RLC Search");
        
        $final = PHPWS_Template::processTemplate($tags, 'hms', 'admin/search_by_rlc.tpl');
        return $final;
    }
}

?>
