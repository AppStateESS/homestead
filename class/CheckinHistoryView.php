<?php

namespace Homestead;

/**
 * CheckinHistoryView class - Genereated the view for a students checkin/checkout history
 * @author jbooker
 * @package hms
 */
class CheckinHistoryView extends View {

    private $checkins;

    public function __construct(Array $checkins)
    {
        $this->checkins = $checkins;
    }

    public function show()
    {
        $historyRows = array();

        foreach ($this->checkins as $checkin) {
            $row = array();

            $bed = new Bed($checkin->getBedId());

            $row['room']     = $bed->where_am_i();
            $row['term']     = Term::toString($checkin->getTerm());
            $row['checkin']  = date("M j, Y g:i:sa", $checkin->getCheckinDate());

            $checkoutDate = $checkin->getCheckoutDate();
            if(isset($checkoutDate)){
                $row['checkout'] = date("M j, Y g:i:sa", $checkoutDate);
            }else{
                $row['checkout'] = '';
            }

            $ricCommand = CommandFactory::getCommand('GenerateInfoCard');
            $ricCommand->setCheckinId($checkin->getId());
            $row['action'] = $ricCommand->getLink('Get RIC');

            $historyRows[] = $row;
        }

        $tpl = array();

        $tpl['HISTORY'] = $historyRows;

        return \PHPWS_Template::process($tpl, 'hms', 'admin/StudentCheckinHistoryView.tpl');
    }
}
