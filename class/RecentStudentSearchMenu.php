<?php

class RecentStudentSearchMenu {

    const LIST_SIZE = 10;

    private $searchList;

    public function __construct(RecentStudentSearchList $searchList)
    {
        $this->searchList = $searchList;
    }

    public function show()
    {
        javascript('jquery_ui');

        $tpl = array();

        $tpl['USER']	= $this->processList($this->searchList->getList());
        $tpl['GLOBAL']	= $this->processList($this->searchList->getGlobalList());

        return PHPWS_Template::process($tpl, 'hms', 'admin/RecentStudentSearchMenu.tpl');
    }

    private function processList(Array $list)
    {
        $tpl['searchListItemRepeat'] = array();

        if(count($list) == 0){
            $tpl['EMPTY_MSG'] = 'None yet.';
        }

        for($i = 0; $i < sizeof($list) && $i < self::LIST_SIZE; $i++){

            $profileCmd = CommandFactory::getCommand('ShowStudentProfile');
            $profileCmd->setBannerId($list[$i]->getBannerId());

            $tpl['searchListItemRepeat'][] = array('NAME'=>$list[$i]->getName(),
															'BANNER_ID'	 => $list[$i]->getBannerId(),
															'USERNAME' 	 => $list[$i]->getUsername(),
															'PROFILE_URI'=> $profileCmd->getURI(),
															'BG_CLASS'	 => 'bg'. ($i%2));
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/RecentStudentSearchList.tpl');
    }
}

?>