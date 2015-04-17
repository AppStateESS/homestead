<?php

namespace hms;

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
    
    public function getMain()
    {
        return $this->main;
    }

    public function showHMS($content)
    {
        $tpl = array();
        $tpl['MAIN'] = $content;
        $tpl['USER'] = \UserStatus::getDisplay();
        
        \PHPWS_Core::initModClass('hms', 'NavBar.php');
        $navbar = new NavBar();
        $tpl['NAVBAR'] = $navbar->show();


        //\Layout::addStyle('hms', 'css/hms.css');
        \Layout::addStyle('hms', 'css/tango-icons.css');
        //\Layout::addStyle('hms', 'css/bootstrap.css');
        \Layout::add(\PHPWS_Template::process($tpl, 'hms', 'hms.tpl'));
    }
}