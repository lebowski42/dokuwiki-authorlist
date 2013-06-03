<?php
/**
 *
 * Syntax: ~~AUTHORS:param1&param2~~ will be replaced by List of authors
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martin Schulte <lebowski[at]corvus[dot]uberspace[dot]de>
 */

//error_reporting (E_ALL | E_STRICT);  
//ini_set ('display_errors', 'On');

// must be run inside dokuwiki
if(!defined('DOKU_INC')) die();
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_authorlist extends DokuWiki_Syntax_Plugin {

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Martin Schulte',
            'email'  => '<lebowski[at]corvus[dot]uberspace[dot]de>',
            'date'   => '2013-02-19',
            'name'   => 'authorlist Plugin',
            'desc'   => 'Displays all contributors/authers of a wikipage',
            'url'    => 'http://dokuwiki.org/plugin:authorlist',
        );
    }
   

   /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

   /**
    * Where to sort in?
    */
    function getSort(){
        return 999;
    }

    /**
     * Close open paragraphs before
     */
    function getPType(){
        return 'block';
    }


   /**
    * Connect lookup pattern to lexer.
    */
    function connectTo($mode) {                                        
      $this->Lexer->addSpecialPattern('~~AUTHORS[:]?[a-zA-Z&=]*~~',$mode,'plugin_authorlist');
    }
	

    /**
    * Handler to prepare matched data for the rendering process.
    *
    * @param $match String The text matched by the patterns, somthing like ~~AUTHORS:displayaslist&tooltip=fullname...~~
    * @param $state Integer The lexer state for the match, doesn't matter because we only substitute.
    * @param $pos Integer The character position of the matched text.
    * @param $handler Object reference to the Doku_Handler object.
    * @return Integer The current lexer state for the match.
    */
    function handle($match, $state, $pos, &$handler){
        $match = strtolower(substr($match,10,-2)); //strip ~~AUTHORS: from start and ~~ from end
        $options = explode('&',$match);
        $data = array();
        foreach($options as $option){
                $tmp = explode("=",$option);
                if(count($tmp)==1){
                    $data[strtolower($tmp[0])] = true;
                }else{
                    $data[strtolower($tmp[0])] = strtolower($tmp[1]);
                }
        }
        return $data;
    }

   /**
    * Render the complete authorlist. 
    */
    function render($mode, &$renderer, $data) {
		// Only if XHTML
        if($mode == 'xhtml' && !$data['off']){
			global $INFO;
			$al = &plugin_load('helper', 'authorlist'); // A helper_plugin_authorlist object
			if (!$al) return false; // Everything went well?
			$al->setOptions($INFO['id'],$data);	// Set options. Data was created by the handle-mode. If empty, default are used.
			$al->fetchAuthorsFromMetadata();
			$al->sortAuthors();
            $al->startList();
			$al->renderAllAuthors();
            $al->finishList();
            $renderer->doc .= $al->getOutput();
            return true;
        }

        return false;
    }
}
?>
