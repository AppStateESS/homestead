<?php

/**
 * HMS Ajax ViewController
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS.php');

class AjaxHMS extends HMS
{
    public function process()
    {
        parent::process();

        echo $this->context->getContent();
        HMS::quit();
    }
}

?>