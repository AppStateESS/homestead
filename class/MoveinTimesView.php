<?php

namespace Homestead;

/*
 * MoveinTimesView
 *
 *   Creates the interface for adding/editing movin times.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */
class MoveinTimesView extends View {

    public function show(){
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['TITLE'] = 'Edit Move-in Times';

        $form = new \PHPWS_Form();

        $form->addDropBox('begin_day', HMS_Util::get_days());
        $form->addCssClass('begin_day', 'form-control');

        $form->addDropBox('begin_month', HMS_Util::get_months());
        $form->addCssClass('begin_month', 'form-control');

        $form->addDropBox('begin_year', HMS_Util::get_years_2yr());
        $form->addCssClass('begin_year', 'form-control');

        $form->addDropBox('begin_hour', HMS_Util::get_hours());
        $form->addCssClass('begin_hour', 'form-control');

        $form->addDropBox('end_day', HMS_Util::get_days());
        $form->addCssClass('end_day', 'form-control');

        $form->addDropBox('end_month', HMS_Util::get_months());
        $form->addCssClass('end_month', 'form-control');

        $form->addDropBox('end_year', HMS_Util::get_years_2yr());
        $form->addCssClass('end_year', 'form-control');

        $form->addDropBox('end_hour', HMS_Util::get_hours());
        $form->addCssClass('end_hour', 'form-control');

        $form->addSubmit('submit', 'Create');

        $cmd = CommandFactory::getCommand('CreateMoveinTime');
        $cmd->initForm($form);

        $tpl['MOVEIN_TIME_PAGER'] = HMS_Movein_Time::get_movein_times_pager();

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Move-in Times");

        return \PHPWS_Template::process($tpl, 'hms', 'admin/edit_movein_time.tpl');
    }

}
