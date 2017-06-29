<?php

class AssignByFloorView extends hms\View
{
    public function __construct()
    {
    }

    public function show()
    {
        javascript('jquery');
        $home_http = PHPWS_SOURCE_HTTP;

        /**
         * Uncomment below for DEVELOPMENT
         * Comment out for PRODUCTION
         */
        Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/react.js'></script>");
        Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/JSXTransformer.js'></script>");
        Layout::addJSHeader("<script type='text/jsx' src='{$home_http}mod/hms/javascript/AssignByFloor/src/AssignByFloor.jsx'></script>");

        /**
         * Uncomment below for PRODUCTION
         * Comment out for DEVELOPMENT
         */
        //Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/react/build/react.min.js'></script>");
        //Layout::addJSHeader("<script src='{$home_http}mod/hms/javascript/AssignByFloor/build/AssignByFloor.js'></script>");

        /**
         * Remainder of code is untouched regardless of development status
         */

        Layout::addJSHeader("<script type='text/javascript'>var sourceHttp = '{$home_http}';</script>");
        ob_start();
        include PHPWS_HOME_HTTP . 'mod/hms/templates/admin/AssignByFloor.html';
        return ob_get_clean();
    }

}
