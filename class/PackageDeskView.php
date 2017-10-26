<?php

namespace Homestead;

/**
 * View for the Package Desk UI
 *
 * @author jbooker
 * @package hms
 */
class PackageDeskView {

    private $packageDesks;

    /**
     * @param array $packageDesks Array of PackageDesk objects
     */
    public function __construct(Array $packageDesks)
    {
        $this->packageDesks = $packageDesks;
    }

    /**
     * Main method for creating the view
     */
    public function show()
    {
        javascript('jquery');
        javascript('jquery_ui');
        javascriptMod('hms', 'jqueryCookie');
        //javascriptMod('packageDesk');

        \Layout::addPageTitle('Package Desk');

        $form = new \PHPWS_Form('pd');

        // Package desk drop down
        $form->addDropBox('desk', array('Select a Package Desk...') + $this->packageDesks);
        $form->setLabel('desk', 'Package Desk');
        $form->addHidden('desk_hidden');

        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/packageDesk.tpl');
    }
}
