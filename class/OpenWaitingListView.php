<?php

PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');

class OpenWaitingListView extends Homestead\View{

    public function show()
    {
        Layout::addPageTitle("Open Waiting List");
        
        return WaitingListApplication::waitingListPager();
    }
}

?>
