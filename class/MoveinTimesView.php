<?php
PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');

/*
 * MoveinTimesView
 *
 *   Creates the interface for adding/editing movin times.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */


class MoveinTimesView extends hms\View{
    
    public function show(){
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl['TITLE'] = 'Edit Move-in Times';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        $form = new PHPWS_Form();
        
        $form->addDropBox('begin_day', HMS_Util::get_days());
        $form->addDropBox('begin_month', HMS_Util::get_months());
        $form->addDropBox('begin_year', HMS_Util::get_years_2yr());
        $form->addDropBox('begin_hour', HMS_Util::get_hours());
        
        $form->addDropBox('end_day', HMS_Util::get_days());
        $form->addDropBox('end_month', HMS_Util::get_months());
        $form->addDropBox('end_year', HMS_Util::get_years_2yr());
        $form->addDropBox('end_hour', HMS_Util::get_hours());
        
        $form->addSubmit('submit', 'Create');

        $cmd = CommandFactory::getCommand('CreateMoveinTime');
        $cmd->initForm($form);

        $tpl['MOVEIN_TIME_PAGER'] = HMS_Movein_Time::get_movein_times_pager();

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        Layout::addPageTitle("Move-in Times");

        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_movein_time.tpl');
    }

}

?>
