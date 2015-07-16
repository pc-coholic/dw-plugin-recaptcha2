<?php
/**
 * Options for the recaptcha plugin
 *
 * @author Adrian Schlegel <adrian.schlegel@liip.ch>
 * @author Robert Bronsdon <reashlin@gmail.com>
 * @author Martin Gross <martin@pc-coholic.de>
 */

$meta['publickey']  = array('string');
$meta['privatekey'] = array('string');
$meta['theme'] = array('multichoice', '_choices'=>array('light', 'dark'));
$meta['lang'] = array('string');
$meta['regprotect'] = array('onoff');
$meta['editprotect'] = array('onoff');
$meta['forusers'] = array('onoff');
