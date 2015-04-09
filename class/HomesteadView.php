<?php

namespace Homestead;

abstract class HomesteadView extends View {
    private $main;
    public $sidebar = array();
    
    protected $notifications;
    
    public function addNotifications($n)
    {
        $this->notifications = $n;
    }

    public function setMain($content)
    {
        $this->main = $content;
    }

    public function addToSidebar($side)
    {
        $this->sidebar[] = $side;
    }
    
    public function getMain()
    {
        return $this->main;
    }

    public function getTerm()
    {
        return 'Homestead';
    }

    public function showHMS($content)
    {
        $tpl = array();
        $tpl['MAIN'] = $content;
        $tpl['TERM'] = self::getTerm();
        $tpl['USER'] = \UserStatus::getDisplay();

        if(sizeof($this->sidebar) > 0) 
        {
            $tpl['TERMBAR'] = $this->sidebar[0];
            $tpl['MENUBAR'] = $this->sidebar[1];
            $tpl['SEARCHBAR'] = $this->sidebar[2];
        }

        \Layout::addStyle('hms', 'css/hms.css');
        \Layout::addStyle('hms', 'css/tango-icons.css');
        \Layout::addStyle('hms', 'css/bootstrap.css');
        \Layout::add(\PHPWS_Template::process($tpl, 'hms', 'hms.tpl'));
    }
}