<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class ShowViewByRlc extends View {
    private $rlc;

    public function __construct(HMS_Learning_Community $rlc)
    {
        $this->rlc = $rlc;
    }

    public function show()
    {
        Layout::addPageTitle("View By RLC");
        
        PHPWS_Core::initModClass('hms', 'RlcRosterPager.php');

        $rosterPager = new RlcRosterPager($this->rlc);
        
        return $rosterPager->get();
    }
}
?>
