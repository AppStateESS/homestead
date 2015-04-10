<?php
namespace hms;
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

    public function setTitle($title){
        $this->pageTitle = $title;
        \Layout::addPageTitle($title);
    }

    public abstract function show();
}

abstract class HMSView extends View{
    private $main;
    public $sidebar = array();

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

?>
