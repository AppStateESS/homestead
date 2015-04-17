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
