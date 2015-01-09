<?php


class TermsAgreementView extends homestead\View {

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

        $tpl = $form->getTemplate();

        $term = new Term($this->term);

        $tpl['TERM'] = Term::toString($this->term);
        $tpl['DOCUSIGN_BEGIN_CMD'] = $this->submitCommand->getURI();
        javascript('jquery');
        Layout::addPageTitle("License Agreement");

        return PHPWS_Template::process($tpl, 'hms', 'student/contract.tpl');
    }
}
?>
