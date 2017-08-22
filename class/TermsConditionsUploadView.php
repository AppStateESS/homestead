<?php

namespace Homestead;

class TermsConditionsUploadView extends View{

    private $term;
    private $type;

    public function __construct($term, $type)
    {
        if(is_null($term)) {
            throw new InvalidArgumentException('term cannot be null.');
        }
        if($type != 'txt' && $type != 'pdf') {
            throw new InvalidArgumentException('type must be either txt or pdf');
        }

        $this->term = $term;
        $this->type = $type;
    }

    public function show()
    {
        $term = $this->term;
        $type = $this->type;

        $form = new \PHPWS_Form('upload_terms_conditions');

        $cmd = CommandFactory::getCommand('UploadTermsConditions');
        $cmd->setTerm($term);
        $cmd->setType($type);
        $cmd->initForm($form);

        $form->addFile('tc_file');
        $form->addSubmit('submit', 'Upload');

        $tpl = $form->getTemplate();

        $tpl['INSTRUCTIONS'] = 'Please choose a ' .
                ($type == 'txt' ? 'Plain Text' :
                        ($type == 'pdf' ? 'PDF' : 'SOMETHING WENT WRONG')) .
                        ' file to upload.';

        return \PHPWS_Template::process($tpl, 'hms', 'admin/TermsConditionsUploadView.tpl');
    }
}
