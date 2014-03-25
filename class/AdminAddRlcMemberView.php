<?php

class AdminAddRlcMemberView extends hms\View{

    private $community;

    public function __construct(HMS_Learning_Community $community)
    {
        $this->community = $community;
    }

    public function show()
    {
        $tpl = array();
        $tpl['COMMUNITY_NAME'] = $this->community->getName();

        $submitCmd = CommandFactory::getCommand('AdminAddRlcMembers');
        $submitCmd->setCommunity($this->community);

        $form = new PHPWS_Form('addToRlcForm');

        $submitCmd->initForm($form);

        $form->addTextArea('banner_id_list');
        $form->addSubmit('submit', 'Add Students');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/adminAddRlcMemberView.tpl');
    }
}