<?php

PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

class LotteryWaitingListView extends View {

    public function show(){
        return LotteryApplication::waitingListPager();
    }
}
?>
