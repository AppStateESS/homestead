<?php

namespace Homestead;

class CheckoutStartView extends View
{
    private $halls;
    private $term;

    public function __construct(Array $halls, $term)
    {
        $this->halls = $halls;
        $this->term = $term;
    }

    public function show()
    {
        javascript('jquery');
        javascript('jquery_ui');
        javascript('select2');
        javascriptMod('hms', 'jqueryCookie');
        javascriptMod('hms', 'checkinStart');

        Layout::addPageTitle('Check-out');

        $tpl = array();

        $form = new \PHPWS_Form('checkin_form');

        $submitCmd = CommandFactory::getCommand('StartCheckoutSubmit');
        $submitCmd->initForm($form);

        $form->addDropbox('residence_hall', array(0 => 'Select a hall..') + $this->halls);
        $form->addCssClass('residence_hall', 'form-control');
        $form->setLabel('residence_hall', 'Residence Hall');

        if (count($this->halls) == 1) {
            $keys = array_keys($this->halls);
            $form->addHidden('residence_hall_hidden', $keys[0]);

            setcookie('hms-checkin-hall-id', $keys[0]); // Force the hall selection cookie to the one hall this user has
            setcookie('hms-checkin-hall-name', $this->halls[$keys[0]]);
        } else {
            $form->addHidden('residence_hall_hidden');
        }

        $form->addText('banner_id');
        $form->setLabel('banner_id', 'Resident');
        $form->setExtra('banner_id', 'placeholder = "Swipe AppCard or type Name/Email/Banner ID"');
        $form->addCssClass('banner_id', 'form-control');
        $form->addCssClass('banner_id', 'input-lg');
        $form->addCssClass('banner_id', 'typeahead');

        $form->addSubmit('Begin Check-out');
        $form->setClass('submit', 'btn btn-lg btn-primary');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/checkoutStart.tpl');
    }

}
