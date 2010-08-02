<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class AddCommunityView extends View {
    private $community;

    public function __construct(HMS_Learning_Community $rlc){
        $this->community = $rlc;
    }

    public function show()
    {
        $form = new PHPWS_Form('add_learning_community');

        $form->addText('community_name', $this->community->get_community_name());
        $form->addText('abbreviation', $this->community->get_abbreviation());
        $form->addText('capacity', $this->community->get_capacity());
        $form->setSize('capacity', 5);

        $form->addHidden('module', 'hms');
        $form->addHidden('hide', 0);
        $form->addHidden('action', 'SaveRlc');
        if(!is_null($this->community->get_id())){
            $form->addHidden('id', $this->community->get_id());
        }

        $var = array('ELEMENT' => $form->getId('community_name'));
        javascript('/modules/hms/autoFocus', $var);
        $form->addSubmit('Save');

        $tpl = $form->getTemplate();
        $tpl['COMMUNITY'] = $this->community->get_community_name();
        $tpl['TITLE']     = 'Add/Edit a learning Community';
        $tpl['MESSAGE']   = ''; //TODO: use NQ here

        Layout::addPageTitle("Add RLC");

        return PHPWS_Template::process($tpl, 'hms', 'admin/display_learning_community_data.tpl');
    }
}
?>
