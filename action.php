<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martin Schulte <schulte-martin [at] web [dot] de>
 */
//error_reporting (E_ALL | E_STRICT);  
//ini_set ('display_errors', 'On');
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_authorlist extends DokuWiki_Action_Plugin{

    function register(&$contr) {
       // $contr->register_hook('TPL_ACT_RENDER','AFTER',$this,'renderAuthorlist');
        $contr->register_hook('PARSER_WIKITEXT_PREPROCESS','BEFORE',$this,'renderAuthorlist1');
    }
    
    
      function renderAuthorlist1(&$event, $param){
		global $INFO;	
		//if($event->data != 'show' && $event->data != 'preview') return false;
		//if(!$INFO['exists'] && $event->data != 'preview') return false;
		//echo "<h1>hier: ".$event->data."</h1>";
		//if(strpos($event->data, '~~AUTHORS:off~~') != false) return false;
		//
		if($this->getConf('showheading'))  $event->data .= DOKU_LF."======".strip_tags($this->getConf('heading'))."======".DOKU_LF;
		$event->data .= "~~AUTHORS~~";
		echo "<h1>los gehts</h1>";
		echo $event->data;
		echo "<h1>STOP</h1>";
        return true;
	}
    
    function renderAuthorlist(&$event, $param){
		global $INFO;		
		if($event->data != 'show' && $event->data != 'preview') return false;
		if(!$INFO['exists'] && $event->data != 'preview') return false;
		echo "<h1>hier: ".$event->data."</h1>";
		if(strpos($event->data, '~~AUTHORS:off~~') != false) return false;
		$al = $this->loadHelper('authorlist',false);
		if (!$al) return false;
		$al->setOptions($INFO['id'],array());
		$al->fetchAuthorsFromMetadata();
		$al->sortAuthors();
        $al->startList();
		$al->renderAllAuthors();
        $al->finischList();
        echo($al->getOutput());
        return true;
	}

}
