<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandContext {
	
    private $params = array();
    private $error = "";
    private $content = "";
    private $successCommand = null;
    private $rewritten = FALSE;

    function __construct()
    {
        foreach($_REQUEST as $key => $val) {
            if(!empty($val) || $val == "0" || $val == 0) {
            //if(!empty($val)) {
                $this->addParam($key, $val);
            }
        }
        
        if(!isset($_SERVER['REDIRECT_URL'])) $this->rewritten = FALSE;
        else if(empty($_SERVER['QUERY_STRING'])) $this->rewritten = TRUE;
        else $this->rewritten = FALSE;
        
        if($this->get('hms_goback')) {
            $this->loadLastContext();
        } else if($this->get('hms_load')) {
        	$this->loadContext();
        }
    }

    function addParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    function get($key)
    {
        if(!isset($this->params[$key]))
            return NULL;

        return $this->params[$key];
    }

    /**
     * Returns a list of parameters sans module and action.  Useful for passing
     * a context forward on redirect.
     *
     * @return array Array of parameters from $_REQUEST
     */
    public function getParams()
    {
        //TODO: Enumerating badness is bad, enumerate goodness instead
        return array_diff_key($this->params, array('module'=>'','action'=>''));
    }
    
    public function unsetParam($key)
    {
        unset($this->params[$key]);
    }
    
    function plugObject($obj)
    {
    	return PHPWS_Core::plugObject($obj, $this->params);
    }

    function setDefault($key, $val)
    {
        if(!isset($this->params[$key]))
            $this->params[$key] = $val;
    }

    function setError($error)
    {
        $this->error = $error;
    }

    function getError()
    {
        return $this->error;
    }

    function setContent($content)
    {
        $this->content = $content;
    }

    function getContent()
    {
        return $this->content;
    }
    
    function isRewritten()
    {
    	return $this->rewritten;
    }

    function saveLastContext()
    {
        $_SESSION['HMS_Last_Context'] = $this->params;
    }

    function loadLastContext()
    {
        $this->params = $_SESSION['HMS_Last_Context'];
    }
    
    function saveContext()
    {
    	$_SESSION['HMS_Saved_Context'] = $this->params;
    }
    
    function loadContext()
    {
    	if(isset($_SESSION['HMS_Saved_Context']) && !empty($_SESSION['HMS_Saved_Context'])) {
    	    $this->params = $_SESSION['HMS_Saved_Context'];
    	}
    }
    
    function redirectToSavedContext()
    {
    	$path = $_SERVER['SCRIPT_NAME'].'?module=hms&hms_load=true';
    	
    	header('HTTP/1.1 303 See Other');
    	header("Location: $path");
        HMS::quit();
    }

    function goBack()
    {
        $path = $_SERVER['SCRIPT_NAME'] . '?module=hms&hms_goback=true';

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        HMS::quit();
    }
}

?>
