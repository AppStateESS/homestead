<?php

  /**
   * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
   */


class HMS_User 
{
    public function main()
    {
        $title = "Welcome to HMS!<br />";
        $content = "Welcome to Housing Management System, or HMS.<br />";
        $content .= "This module is being written for Housing and Residence Life at<br />";
        $content .= "Appalachian State University. You do not have administrative access to HMS.<br />";
        $content .= "Until a student side is written this is the only content you may access.<br /><br />";
        $tpl['CONTENT'] = $content;
        $tpl['TITLE']   = $title;
        $final = PHPWS_Template::process($tpl, 'hms', 'user/main.tpl');
        Layout::add($final);
    }
}

?>
