<?php

define('TERMS_CONDITIONS_DIR', 'files/terms_cond/');

class UploadTermsConditionsCommand extends Command {
	
	private $term;
	private $type;
    
    public function getRequestVars() {
        $vars = array('action' => 'UploadTermsConditions');
        
        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }
        
        if(isset($this->type)) {
            $vars['type'] = $this->type;
        }
        
        return $vars;
    }
    
    public function setTerm($term) {
        $this->term = $term;
    }
    
    public function setType($type) {
        $this->type = $type;
    }
    
    public function execute(CommandContext $context) {
    	if(!isset($this->term)) {
    		$this->term = $context->get('term');
    		if(is_null($this->term)) {
    			throw new InvalidArgumentException('Must provide a term to UploadTermsConditions');
    		}
    	}
    	
    	if(!isset($this->type)) {
    		$this->type = $context->get('type');
    		if(is_null($this->type)) {
    			throw new InvalidArgumentException('Must provide a type to UploadTermsConditions');
    		}
    	}
    	
    	if(empty($_FILES['tc_file']['name'])) {
    		NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a file to upload.');
    	}
    	
    	$term = $this->term;
    	$type = $this->type;
    	
    	$termInstance = new Term($term);
    	
    	PHPWS_Core::initModClass('filecabinet', 'Document.php');
    	$document = new PHPWS_Document;
    	$document->setDirectory(TERMS_CONDITIONS_DIR);
    	
    	if(!$document->importPost('tc_file', false, true)) {
    		if(isset($document->_errors)) {
    			foreach($document->_errors as $oError) {
    				NQ::simple('hms', HMS_NOTIFICATION_ERROR, $oError->getMessage());
    			}
    		}
    	} elseif($document->file_name) {
    		// Check Extension (lame)
    		if($document->getExtension() != $type) {
    			NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You did not provide a file of type ' . $type);
    		} else {
   		  	    // Check for and delete an existing file of the same name
    		    if(is_file(TERMS_CONDITIONS_DIR . $term . '.' . $type)) {
        			unlink(TERMS_CONDITIONS_DIR . $term . '.' . $type);
    		    }
    		
    		    $document->setFilename($term . '.' . $type);
    		
    		    $result = $document->write();
    		    if(PHPWS_Error::logIfError($result)) {
    		    	NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem saving the Terms and Conditions file.');
    		    } else {
    			    if($type == 'pdf') {
    				    $termInstance->setPdfTerms($document->getPath());
    			    } else if($type == 'txt') {
    				    $termInstance->setTxtTerms($document->getPath());
    			    }
    			    $termInstance->save();
    		    }
    		}
    	}
    	
    	$cmd = CommandFactory::getCommand('ShowEditTerm');
    	$cmd->redirect();
    }
}

?>