<?php

PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');

class OpenWaitingListView extends View {

    public function show()
    {
        return WaitingListApplication::waitingListPager();
    }
}

?>