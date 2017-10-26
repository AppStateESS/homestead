<?php

namespace Homestead\Command;

use \Homestead\HMS;

/**
 * HMS Command
 *
 * @package HMS
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Command
{
    protected $context;

    public abstract function getRequestVars();
    public abstract function execute(CommandContext $context);

    /**
     * Initializes a {@link \PHPWS_Form} with hidden values such that
     * it will properly call this command when submitted.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running initForm, as it calls
     * {@link getRequestVars} and sets what variables are available at
     * call time.
     *
     * @param \PHPWS_Form &$form The form to be initialized
     *
     * @see getRequestVars
     * @see getLink
     * @see getURI
     * @see redirect
     */
    public function initForm(\PHPWS_Form &$form)
    {
        $moduleElement = $form->get('module');
        if(\PEAR::isError($moduleElement)) {
            $form->addHidden('module', 'hms');
        }

        foreach($this->getRequestVars() as $key=>$val) {
            $form->addHidden($key, $val);
        }
    }

    /**
     * Sets the context to its only parameter.  Useful for redirecting to a
     * command while passing values from $_REQUEST.
     *
     * @param CommandContext $context A context containing information to be passed
     *
     * @return void
     */
    public function loadContext(CommandContext $context)
    {
        $context->unsetParam('module');
        $context->unsetParam('action');

        $this->context = $context;
    }

    /**
     * Returns the absolute URI to this command.  If you want to create
     * a proper HTML link to this command, you may want to look at
     * {@link getLink} instead.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running getURI, as it calls {@link getRequestVars}
     * and sets what variables are available at call time.
     *
     * @return string The absolute URI to this command
     * @see getRequestVars
     * @see getLink
     * @see initForm
     * @see redirect
     */
    public function getURI()
    {
        $uri = $_SERVER['SCRIPT_NAME'] . "?module=hms";

        foreach($this->getRequestVars() as $key=>$val) {
            if(is_array($val)) {
                foreach($val as $key2=>$val2)
                    $uri .= "&$key" . "[$key2]=" . rawurlencode($val2);
            }else{
                $uri .= "&$key=" . rawurlencode($val);
            }
        }

        return $uri;
    }

    /**
     * Returns a properly formatted link to this command that can be
     * outputted straight to the browser.  If you want just the URL
     * instead of a properly formatted HTML link, have a look at
     * {@link getURI} instead.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running getLink, as it calls {@link getRequestVars}
     * and sets what variables are available at call time.
     *
     * @param string $text		The text to format as a link
     * @param string $target	The target of the link - See \PHPWS_Text class.
     * @param string $cssClass	The "class" (css) of the link.
     * @param string $title		The alt-text for the link.
     * @return string The formatted link
     *
     * @see getRequestVars
     * @see getURI
     * @see initForm
     * @see redirect
     * @see \PHPWS_Text
     */
    public function getLink($text, $target = null, $cssClass = null, $title = null)
    {
        return \PHPWS_Text::moduleLink(dgettext('hms', $text), 'hms', $this->getRequestVars(), $target, $title, $cssClass);
    }

    /**
     * Returns a 303 Redirect to this command and then exits.  This
     * should be used after every POST request or destructive GET to
     * prevent accidental damage through a refresh or back/forward
     * operation in the client web browser.
     *
     * Note: Obviously, the implementation of HTTP 303 is browser-
     * specific and cannot be predicted by this script.  Most browsers
     * implement HTTP 303 in such a way that refresh/back/forward won't
     * attempt to re-POST, but some might.  Sucks to be them.
     *
     * Also Note: This DOES EVENTUALLY CALL exit().  After you call
     * this redirect function, you won't be returned control unless an
     * exception is somehow thrown.
     *
     * @see getRequestVars
     * @see getLink
     * @see getURI
     * @see initForm
     */
    public function redirect()
    {
        $path = $this->getURI();
        \NQ::close();

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        HMS::quit();
    }
}
