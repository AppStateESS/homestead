<?php

PHPWS_Core::initModClass('hms', 'View.php');

class SpecialInterestGroupView extends View {
    protected $group;

    public function __construct($group='none')
    {
        $this->group = is_null($group) ? 'none' : $group;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        javascript('jquery');

        $tpl = array();

        $groups = HMS_Lottery::get_special_interest_groups();

        $form = new PHPWS_Form('special_interest');
        $form->setMethod('get');
        $form->addDropBox('group', $groups);

        $form->setMatch('group', $this->group);

        $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
        $cmd->initForm($form);
        
        $tpl = $form->getTemplate();

        if($this->group != 'none' && !is_null($this->group)){
            $tpl['GROUP_PAGER'] = LotteryApplication::specialInterestPager($this->group, PHPWS_Settings::get('hms', 'lottery_term'));
            $tpl['GROUP'] = $groups[$this->group];
        }
        
        Layout::addPageTitle("Special Interest Group");

        return PHPWS_Template::process($tpl, 'hms', 'admin/special_interest_approval.tpl');
    }
}

?>
