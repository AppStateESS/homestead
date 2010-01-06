<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ApplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;

    public function __construct($term, $startDate, $endDate, HousingApplication $application = NULL)
    {
        $this->term               = $term;
        $this->startDate          = $startDate;
        $this->endDate            = $endDate;
        $this->application        = $application;
    }

    public function show()
    {
        $tpl = array();


        if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        } else if(time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        } else if( is_null($this->application) ){
            $appCommand = CommandFactory::getCommand('ShowTermsAgreement');
            $appCommand->setTerm($this->term);
            $cmd = CommandFactory::getCommand('ShowHousingApplicationForm');
            $cmd->setTerm($this->term);
            $appCommand->setAgreedCommand($cmd);
            $tpl['APP_NOW'] = $appCommand->getLink('Apply now!');
        } else {
            $appCommand = CommandFactory::getCommand('ShowApplicationView');
            if(!is_null($this->application)){
                $appCommand->setAppId($this->application->id);
            }
            $tpl['VIEW_APP'] = $appCommand->getLink('View your application');
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/applicationMenuBlock.tpl');
    }
}

?>
