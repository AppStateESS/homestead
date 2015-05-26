<?php

/**
 * HMS Ajax ViewController
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'HMS.php');
PHPWS_Core::initModClass('hms', 'JsonError.php');

class AjaxHMS extends HMS
{
    public function process()
    {
        // Set headers to allow Cross-origin scripting
        $rh = getallheaders();
        header('Allow: GET,HEAD,POST,PUT,DELETE,OPTIONS');

        if(array_key_exists('Origin', $rh)) {
            header('Access-Control-Allow-Origin:'.$rh['Origin']);
        }

        if(array_key_exists('Access-Control-Request-Headers', $rh)) {
            header('Access-Control-Allow-Headers:'.$rh['Access-Control-Request-Headers']);
        }

        header('Access-Control-Allow-Credentials: true');

        try {
            parent::process();
        } catch (PermissionException $e) {
            $error = new JsonError('401 Unauthorized');
            $error->setMessage('You are not authorized to perform this action.  You may need to sign back in.');
            $error->renderStatus();
            $content = $error->encode();
        } catch (Exception $e) {
            $error = new JsonError('500 Internal Server Error');
            $error->setMessage($e->getMessage());
            $error->renderStatus();
            $content = $error->encode();

            // Log the exception
            error_log('Caught API Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . ' Trace: ' . $e->getTraceAsString());

            $message = $this->formatException($e);
            $this->emailError($message);
        }

        $callback = $this->context->get('callback');

        $content = $this->context->getContent();

        // Wrap a jsonp request in it's function callback
        $response = !is_null($callback) ? "$callback($content)" : $content;

        header('Content-Type: application/json; charset=utf-8');

        echo $response;

        // This sets NQ (notifications), which aren't valid for AJAX, so we aren't doing it. Just exit instead.
        //HMS::quit();
        exit;
    }
}
