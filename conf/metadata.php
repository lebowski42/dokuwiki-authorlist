<?php
$meta['automatic']           = array('onoff');
$meta['displaystyle']        = array('multichoice', '_choices' => array('loginname', 'fullname','loginname (fullname)','fullname (loginname)'));
$meta['displayaslist']       = array('onoff');
$meta['tooltip']             = array('multichoice', '_choices' => array('none', 'loginname','fullname'));
$meta['showheading']         = array('onoff');
$meta['heading']             = array('string');
$meta['intro']               = array('string');
$meta['showcreator']         = array('multichoice', '_choices' => array('none', 'before','below'));
$meta['creatortext']         = array('string');
$meta['printempty']          = array('onoff');
$meta['creatorisauthor']     = array('onoff');
$meta['linkto']              = array('multichoice', '_choices' => array('none', 'userhomepage','email'));
$meta['userpageid']          = array('string');
$meta['_basic']              = array('fieldset');
