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

        $tpl = array();

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'assignByFloor');

        return \PHPWS_Template::process($tpl, 'hms', 'admin/AssignByFloor.tpl');
    }

}
