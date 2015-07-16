<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl2.html)
 * @author     Adrian Schlegel <adrian.schlegel@liip.ch>
 * @author     Martin Gross <martin@pc-coholic.de>
 *
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(dirname(__FILE__).'/lib/autoload.php');

class helper_plugin_recaptcha2 extends DokuWiki_Plugin {

    /**
     * Check if the reCAPTCHA should be used. Always check this before using the methods below.
     *
     * @return bool true when the reCAPTCHA should be used
     */
    function isEnabled(){
        if(!$this->getConf('forusers') && $_SERVER['REMOTE_USER']) return false;
        return true;
    }

    /**
     * check the validity of the recaptcha
     *
     * @return obj (@see ReCaptchaResponse)
     */
    function check() {
        // Check the recaptcha answer and only submit if correct
      	$recaptcha = new \ReCaptcha\ReCaptcha($this->getConf('privatekey'));
      	$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
      	return $resp;
    }


    /**
     * return the html code for the recaptcha block
     * @return string 
     */
    function getHTML($editblock) {
        $lang = $this->getConf('lang') ? $this->getConf('lang') : $conf['lang'];
       	$stylewidth = $editblock ? "100%" : "75%";
      	$captchahtml = '<div class="g-recaptcha" data-sitekey="' . $this->getConf('publickey') . '" data-theme="' . $this->getConf('theme') . '" style="margin: 0 auto; display: block; width: '. $stylewidth . ';"></div>
            <script type="text/javascript"
                    src="https://www.google.com/recaptcha/api.js?hl=' . $lang . '">
            </script>
            <br>';
       	return $captchahtml;
    }
}
