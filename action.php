<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl2.html)
 * @author     Adrian Schlegel <adrian.schlegel@liip.ch>
 * @author     Robert Bronsdon <reashlin@gmail.com>
 * @author     Martin Gross <martin@pc-coholic.de>
 */

class action_plugin_recaptcha2 extends DokuWiki_Action_Plugin {

    /**
     * register an event hook
     *
     */
    function register(Doku_Event_Handler $controller)
    {
        // only register the hooks if the necessary config paramters exist
        if($this->getConf('publickey') && $this->getConf('privatekey')) {
            if($this->getConf('regprotect') || $this->getConf('editprotect')){
                    $controller->register_hook('ACTION_ACT_PREPROCESS',
                                               'BEFORE',
                                               $this,
                                               'preprocess',
                                               array());
            }
            if($this->getConf('regprotect')){
                // new hook
                $controller->register_hook('HTML_REGISTERFORM_OUTPUT',
                                           'BEFORE',
                                           $this,
                                           'insert',
                                           array('oldhook' => false));
                // old hook
                $controller->register_hook('HTML_REGISTERFORM_INJECTION',
                                           'BEFORE',
                                           $this,
                                           'insert',
                                           array('oldhook' => true));
            }
            if($this->getConf('editprotect')){
                // old hook
                $controller->register_hook('HTML_EDITFORM_INJECTION',
                                           'BEFORE',
                                           $this,
                                           'editform_output',
                                           array('editform' => true, 'oldhook' => true));
                // new hook
                $controller->register_hook('HTML_EDITFORM_OUTPUT',
                                           'BEFORE',
                                           $this,
                                           'editform_output',
                                           array('editform' => true, 'oldhook' => false));
            }
        }
    }

    /**
     * Add reCAPTCHA to edit fields
     *
     * @param obj $event
     * @param array $param
     */
    function editform_output(&$event, $param){
        // check if source view -> no captcha needed
        if(!$param['oldhook']){
            // get position of submit button
            $pos = $event->data->findElementByAttribute('type','submit');
            if(!$pos){
                return; // no button -> source view mode
            }
        } elseif($param['editform'] && !$event->data['writable']){
            if($param['editform'] && !$event->data['writable']){
                return;
            }
        }

        // If users don't need to fill in captcha then end this here.
        if(!$this->getConf('forusers') && $_SERVER['REMOTE_USER']){
            return;
        }

        $this->insert($event, $param);
    }

    /**
     * insert html code for recaptcha into the form
     *
     * @param obj $event
     * @param array $param
     */
    function insert(&$event, $param) {
        global $conf;
        $helper = plugin_load('helper','recaptcha2');
        $recaptcha = $helper->getHTML($param['editform']);

        if($param['oldhook']) {
            echo $recaptcha;
        } else {
            $pos = $event->data->findElementByAttribute('type','submit');
            $event->data->insertElement($pos++, $recaptcha);
        }
    }


    /**
     * process the answer to the captcha
     *
     * @param obj $event
     * @param array $param
     *
     */
    function preprocess(&$event, $param) {
        // get and clean the action
        $act = $this->_act_clean($event->data);
        // If users don't need to fill in captcha then end this here.
        if(!$this->getConf('forusers') && $_SERVER['REMOTE_USER']){
            return;
        }
        // Check we are either registering or saving a html page
        if(
            ($act == 'register' && $this->getConf('regprotect') && $_POST['save'] ) ||
            ($act == 'save' && $this->getConf('editprotect'))
          ){

            // Check the recaptcha answer and only submit if correct
            $helper = plugin_load('helper', 'recaptcha2');
            $resp = $helper->check();

            if (!$resp->isSuccess()) {
                if($act == 'save'){
                    // stay in preview mode
                    msg($this->getLang('testfailed'),-1);
                    $event->data = 'preview';
                }else{
                    // stay in register mode, but disable the save parameter
                    msg($this->getLang('testfailed'),-1);
                    $_POST['save']  = false;
                }
            }
        }
    }

    /**
     * Pre-Sanitize the action command
     *
     * Similar to act_clean in action.php but simplified and without
     * error messages
     * (taken from Andreas Gohrs captcha plugin)
     */
    function _act_clean($act){
         // check if the action was given as array key
         if(is_array($act)){
           list($act) = array_keys($act);
         }

         //remove all bad chars
         $act = strtolower($act);
         $act = preg_replace('/[^a-z_]+/','',$act);

         return $act;
     }
} //end of action class
