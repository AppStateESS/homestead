<?php


class TermsAgreementView extends View {

    private $term;
    private $submitCommand;

    public function __construct($term, Command $submitCommand)
    {
        $this->term = $term;
        $this->submitCommand = $submitCommand;
    }

    public function show()
    {

        $form = new PHPWS_Form;
        $this->submitCommand->initForm($form);

        $form->addSubmit('begin', _('I Agree'));
        $form->setExtra('begin', 'class="hms-application-submit-button"');

        $form->addButton('quit', _('I Disagree'));
        $form->setExtra('quit', 'onclick="javascript:window.location.href='."'".'index.php?module=users&action=user&command=logout'."'".';"');

        $tpl = $form->getTemplate();

        $term = new Term($this->term);

        $tpl['TERM'] = Term::toString($this->term);

        $tpl['TXT_CONTRACT'] = file_get_contents($term->getTxtTerms());
        $tpl['PDF_CONTRACT'] = $term->getPdfTerms();

        Layout::addPageTitle("License Agreement");

        return PHPWS_Template::process($tpl, 'hms', 'student/applications/contract.tpl');
    }
}
?>
