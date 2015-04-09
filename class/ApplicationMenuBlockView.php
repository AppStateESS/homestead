<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ApplicationMenuBlockView extends Homestead\View{

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
        $tpl['STATUS'] = "";

        if(time() < $this->startDate){
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        } else if(time() > $this->endDate){
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        } else if( is_null($this->application) ){
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $cmd = CommandFactory::getCommand('ShowHousingApplicationForm');
            $cmd->setTerm($this->term);
            $tpl['APP_NOW'] = $cmd->getLink('Apply now!');
        } else {
            $appCommand = CommandFactory::getCommand('ShowApplicationView');
            if(!is_null($this->application)){
                $tpl['ICON'] = FEATURE_COMPLETED_ICON;
                $appCommand->setAppId($this->application->id);
            }

            $tpl['VIEW_APP'] = $appCommand->getLink('view your application');

            if(time() < $this->editDate){
                $tpl['ICON'] = FEATURE_COMPLETED_ICON;
                $newApp = CommandFactory::getCommand('ShowHousingApplicationForm');
                $newApp->setTerm($this->term);
                $tpl['NEW_APP'] = $newApp->getLink('submit a new application');
            }
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/applicationMenuBlock.tpl');
    }
}

?>
