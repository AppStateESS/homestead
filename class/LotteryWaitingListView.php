<?php

PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

class LotteryWaitingListView extends Homestead\View{

    public function show(){
        Layout::addPageTitle("Lottery Waiting List");
        return LotteryApplication::waitingListPager();
    }
}
?>