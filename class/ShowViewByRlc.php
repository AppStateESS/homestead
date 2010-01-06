<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

class ShowViewByRlc extends View {
    private $rlcId;

    public function __construct($id)
    {
        $this->rlcId = $id;
    }

    public function show()
    {
        $tpl['RLC_PAGER'] = HMS_RLC_Assignment::view_by_rlc_pager($this->rlcId);
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink(_('Return to previous'), 'hms', array('action'=>'ShowSearchByRlc'));

        return PHPWS_Template::processTemplate($tpl, 'hms', 'admin/rlc_roster.tpl');
    }
}
?>
