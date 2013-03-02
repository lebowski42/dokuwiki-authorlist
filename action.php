<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martin Schulte <lebowski[at]corvus[dot]uberspace[dot]de>
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
       $contr->register_hook('PARSER_WIKITEXT_PREPROCESS','BEFORE',$this,'appendAuthors');
    }
    
    /**
     * Add heading and ~~AUTHORS~~ to each wikipage.
     */
    function appendAuthors(&$event, $param){
		global $ID;
		global $ACT;
		global $INFO;
		//var_dump($INFO);
		if(!page_exists($ID) && $ACT != 'preview' ) return false; // Don't show on "This topic does not exist yet" pages
		if(strpos($event->data, '~~AUTHORS:off~~') != false) return false; //Disabled manually 
		if($this->getConf('automatic')){	// on every page by default?
			//if($ACT != 'show') return false;
			if(isset($INFO) && $ACT != 'preview') return false; // We are on a "real" wikipage, not 'Recent-', 'Login-', ...-page 
			if($this->getConf('showheading'))  $event->data .= DOKU_LF."======".strip_tags($this->getConf('heading'))."======".DOKU_LF;
			$event->data .= "~~AUTHORS~~";
			return true;
		}
	}
    
    
    // old stuff
    function renderAuthorlist(&$event, $param){
		global $INFO;		
		if($event->data != 'show' && $event->data != 'preview') return false;
		if(!$INFO['exists'] && $event->data != 'preview') return false;
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
