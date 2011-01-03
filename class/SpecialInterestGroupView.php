<?php

PHPWS_Core::initModClass('hms', 'View.php');

class SpecialInterestGroupView extends View {
    protected $group;

    public function __construct($group = NULL)
    {
        $this->group = $group;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

        Layout::addPageTitle("Special Interest Group");
        javascript('jquery');

        $tpl = array();

        // If a group was selected
        if(!is_null($this->group)){
            $tpl['GROUP_PAGER'] = LotteryApplication::specialInterestPager($this->group, PHPWS_Settings::get('hms', 'lottery_term'));
            $tpl['GROUP'] = $this->group;
        }else{
            // If no group was selected, show the drop down box of groups
            $groups = array('none'=>'Select...', 'honors'=>'Honors','watauga'=>'Watauga Global', 'tf'=>'Teaching Fellows', 'sorority'=>'Sororities');

            $form = new PHPWS_Form('special_interest');
            $form->setMethod('get');
            $form->addDropBox('group', $groups);

            $form->setMatch('group', $this->group);

            $cmd = CommandFactory::getCommand('ShowSpecialInterestGroupApproval');
            $cmd->initForm($form);

            $tpl = $form->getTemplate();
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/special_interest_approval.tpl');
    }
}

?>
