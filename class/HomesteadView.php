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

    public function getMain()
    {
        return $this->main;
    }

    public function showHMS($content)
    {
        $tpl = array();
        $tpl['MAIN'] = $content;

        $navbar = new NavBar();
        $tpl['NAVBAR'] = $navbar->show();


        \Layout::addStyle('hms', 'css/hms.css');

        \Layout::add(\PHPWS_Template::process($tpl, 'hms', 'hms.tpl'));
    }
}
