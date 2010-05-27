<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ApplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $editDate;
    private $endDate;
    private $application;

    public function __construct($term, $startDate, $editDate, $endDate, HousingApplication $application = NULL)
    {
        $this->term               = $term;
        $this->startDate          = $startDate;
        $this->editDate           = $editDate;
        $this->endDate            = $endDate;
        $this->application        = $application;
    }

    public function show()
    {
        $tpl = array();
        // Show availability dates!        

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        if(time() < $this->startDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        } else if(time() > $this->endDate){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        } else if( is_null($this->application) ){
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $appCommand = CommandFactory::getCommand('ShowTermsAgreement');
            $appCommand->setTerm($this->term);
            $cmd = CommandFactory::getCommand('ShowHousingApplicationForm');
            $cmd->setTerm($this->term);
            $appCommand->setAgreedCommand($cmd);
            $tpl['APP_NOW'] = $appCommand->getLink('Apply now!');
        } else {
            $appCommand = CommandFactory::getCommand('ShowApplicationView');
            if(!is_null($this->application)){
                $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Completed"/>';
                $appCommand->setAppId($this->application->id);
            }
            
            $tpl['VIEW_APP'] = $appCommand->getLink('view your application');
            
            if(time() < $this->editDate){
                $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Completed"/>';            
                $newApp = CommandFactory::getCommand('ShowHousingApplicationForm');
                $newApp->setAgreedToTerms(1);
                $newApp->setTerm($this->term);
                $tpl['NEW_APP'] = $newApp->getLink('submit a new application');
            }
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/applicationMenuBlock.tpl');
    }
}

?>
