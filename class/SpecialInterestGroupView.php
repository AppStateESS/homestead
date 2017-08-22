<?php

namespace Homestead;

class SpecialInterestGroupView extends View{

    protected $group;

    public function __construct($group = NULL)
    {
        $this->group = $group;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        $this->setTitle('Special Interest Group');
        javascript('jquery');

        $tpl = array();

        $groups = HMS_Lottery::getSpecialInterestGroupsMap();

        // If a group was selected
        if(!is_null($this->group) && $this->group != 'none'){
            $tpl['GROUP_PAGER'] = LotteryApplication::specialInterestPager($this->group, PHPWS_Settings::get('hms', 'lottery_term'));
            $tpl['GROUP'] = $groups[$this->group];
        }

        // Show the drop down box of groups
        $form = new \PHPWS_Form('special_interest');
        $form->setMethod('get');
        $form->addDropBox('group', $groups);
        $form->setClass('group', 'form-control');

        $form->setMatch('group', $this->group);

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->initForm($form);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/special_interest_approval.tpl');
    }
}
