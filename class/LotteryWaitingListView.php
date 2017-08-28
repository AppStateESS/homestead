<?php

namespace Homestead;

class LotteryWaitingListView extends View {

    public function show(){
        \Layout::addPageTitle("Lottery Waiting List");
        return LotteryApplication::waitingListPager();
    }
}
