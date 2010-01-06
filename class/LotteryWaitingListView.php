<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

class LotteryWaitingListView extends View {

    public function show(){
        PHPWS_Core::initCoreClass('DBPager.php');

        $tpl = array();

        return LotteryApplication::waitingListPager();
    }
}
?>
