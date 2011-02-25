<?php

/**
 * HMS View
 * Handles the very basic HMS view.  This has a top-bar to show login status
 * and/or term-awareness, and then whatever child view is appropriate to the
 * user's status (determined by the HMS contoller).
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class View
{
    protected $pageTitle;

    public function setPageTitle($title){
        $this->pageTitle = $title;
        Layout::addPageTitle($title);
    }

    public abstract function show();
}

abstract class HMSView extends View
{
    private $main;

    public function setMain($content)
    {
        $this->main = $content;
    }

    public function getMain()
    {
        return $this->main;
    }

    public function getTerm()
    {
        return 'Housing Management System';
    }

    public function showHMS($content)
    {
        $tpl = array();
        $tpl['MAIN'] = $content;
        $tpl['TERM'] = self::getTerm();
        $tpl['USER'] = UserStatus::getDisplay();

        Layout::addStyle('hms', 'css/hms.css');
        Layout::add(PHPWS_Template::process($tpl, 'hms', 'hms.tpl'));
    }
}

?>
