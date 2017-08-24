<?php

namespace Homestead;

class OpenWaitingListView extends View {

    public function show()
    {
        Layout::addPageTitle("Open Waiting List");

        return WaitingListApplication::waitingListPager();
    }
}
