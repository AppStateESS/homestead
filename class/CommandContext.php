<?php

namespace Homestead;

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

    private $postdata = null;

    public function __construct()
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

        // Load raw postdata
        $this->postdata = file_get_contents('php://input');
    }

    public function addParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    public function get($key)
    {
        if(!isset($this->params[$key]))
        return NULL;

        return $this->params[$key];
    }

    public function setParams(Array $params)
    {
    	foreach($params as $key => $value)
        {
        	$this->addParam($key, $value);
        }
    }

    public function clearParams()
    {
    	$this->params = array();
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

    public function plugObject($obj)
    {
        return PHPWS_Core::plugObject($obj, $this->params);
    }

    public function setDefault($key, $val)
    {
        if(!isset($this->params[$key]))
        $this->params[$key] = $val;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isRewritten()
    {
        return $this->rewritten;
    }

    public function getPostData()
    {
        return $this->postdata;
    }

    public function getJsonData()
    {
        return json_decode($this->postdata, true);
    }

    public function saveLastContext()
    {
        $_SESSION['HMS_Last_Context'] = $this->params;
    }

    public function loadLastContext()
    {
        $this->params = $_SESSION['HMS_Last_Context'];
    }

    public function saveContext()
    {
        $_SESSION['HMS_Saved_Context'] = $this->params;
    }

    public function loadContext()
    {
        if(isset($_SESSION['HMS_Saved_Context']) && !empty($_SESSION['HMS_Saved_Context'])) {
            $this->params = $_SESSION['HMS_Saved_Context'];
        }
    }

    public function redirectToSavedContext()
    {
        $path = $_SERVER['SCRIPT_NAME'].'?module=hms&hms_load=true';

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        HMS::quit();
    }

    public function goBack()
    {
        $path = $_SERVER['SCRIPT_NAME'] . '?module=hms&hms_goback=true';

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        HMS::quit();
    }
}
