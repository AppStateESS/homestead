<?php

class RoomChangeManageView extends View {

    private $request;

    private $participants;

    public function __construct(RoomChangeRequest $request)
    {
        $this->request = $request;

        // Load each of the participants
        $this->participants = $this->request->getParticipants();
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantView.php');

        $tpl = array();

        $tpl['REQUEST_STATUS'] = $this->request->getState();

        // Make a ParticipantView for each participant and add it to the row repeat
        foreach ($this->participants as $participant) {
            $participantView = new RoomChangeParticipantView($participant);
            $tpl['PARTICIPANT'][]['ROW'] = $participantView->show();
        }

        $tpl['REQUEST_REASON'] = $this->request->getReason();

        if ($this->request->isDenied()) {
            $tpl['DENIED_REASON_PUBLIC'] = $this->request->getDeniedReasonPublic();
            $tpl['DENIED_REASON_PRIVATE'] = $this->request->getDeniedReasonPrivate();
        }

        // Bed selection form
        $form = new PHPWS_Form('bedSelection');
        $form->addDropBox('halls', array('12'=>'blah'));

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeManageView.tpl');
    }
}

?>