<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martin Schulte <lebowski[at]corvus[dot]uberspace[dot]de>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

//constants
if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');



class helper_plugin_authorlist extends DokuWiki_Plugin 
{
    protected $authors = array();  // Contributors-Array (loginname => fullname)
    // ID for the page, the authors should be diesplayed. Not implemented yet, you can't choose another
    // page, can only handle the current rendered page. But maybe later.
    protected $pageID;  
    
    protected $output;
    
    
    protected $displaystyle;    // loginname/fullname or both
    protected $displayaslist;   // List or everything in one line?
    protected $tooltip;         // none, username or fullname.
    protected $linkto;          // can be none, userpage, eMail 
	protected $showheading;     // We want a heading?
    protected $showcreator;     // show Creator separat

	// Can't be set using ~~AUTHORS~~ only via adminmenu
    protected $creatortext;     // template for the creatortext  (contains %CREATOR% and %DATE% as wildcard)
	protected $intro;			// text before authorlist.
	protected $userpageid;      // Template for the pageID of the userhomepages (contains %USER% as wildcard for the loginname)
	
	// Some state variables
	private $cssClass;			// classes author, authoruserpage, authoremail are possible depending on the displaystyle.
	private $openTag;           // <li> or <span>
	private $closeTag;			// </li> or </span>
	private $printempty;        // Print everything even if the authorlist is empty
	private $creatorisauthor;  // creator in the authorlist.
    
    /**
     * Constructor gets default preferences (calling setOptions(...))
     */
    public function helper_plugin_authorlist() {
		global $INFO;
		
		// This options can only set using the admin menu. Because you can use html-Syntax for this options.
		$this->creatortext = $this->getConf('creatortext');
		$this->intro = $this->getConf('intro');
		$this->userpageid = $this->getConf('userpageid');
		$this->linkto = $this->getConf('linkto');
		$this->showheading = $this->getConf('showheading');
		$this->setOptions($INFO['id'] , null);		// set default options
    }
          
    
   /**
    * Set the options.
    *
    * @param $pageID String The page the authorlist should be displayed for. (Not implemented yet, only the current rendered page will be handled)
    * @param $data Array Optionsarray (option => value)
    */
    public function setOptions($pageID, $data){
		$this->output='';
		$this->pageID = $pageID;
		$options = array('displayaslist','displaystyle','tooltip','showcreator','printempty', 'creatorisauthor');// possible options
		foreach($options as $option){
			if(isset($data[$option])){
				$this->$option = $data[$option];
			}else{
				$this->$option = $this->getConf($option);	
			}
		}
		// find right css-class (author, authoeruserpage, authoremail -> see *.css), sets state variables $this->cssClass
        $this->findCssClass();
        // sets state variables $this->openTag, This->closeTag
        $this->findOpenAndCloseTags();
	}

   /**
    * Renders the heading
    */
	public function renderHeading(){
		// heading?
		if($this->showheading) $this->output .= "<h2 class='sectionedit1'><a name='authorlist'>".strip_tags($this->getConf('heading'))."</a></h2>".DOKU_LF;
		return true;
	}


   /**
    * Starts to render the authorlist: Creatortext, <div class='authorlist'> and maybe <ul class='authorlist'> will be add to $this->output
    */
    public function startList(){
        //Show creator separate (before authorlist)
        if($this->showcreator== "before" ) $this->output .= $this->renderCreator();
        // Text before authorslist
        if(!$this->printempty && empty($this->authors)) return false;
        $this->output .= $this->intro.DOKU_LF;
        // open div.authorlist            
        $this->output .= "<div class='authorlist'>".DOKU_LF;
        if($this->displayaslist) $this->output .= "<ul class='authorlist'>".DOKU_LF; // open <ul>

        return true;
    }
  
    /**
    * Add an author to the $this->authors array
    *
    * @param $loginname String Login name of an user.
    * @param $fullname String (optional) fullname of an user, will be found automatically if empty.
    */
    public function addAuthor($loginname, $fullname=''){
		// Get fullname from users.auth
		if($fullname == '') $fullname = $this-> getFullname($loginname);
		// add them to the authors-array
		$this->authors[$loginname] = $fullname;
		return true;
	}
	
	
		
	/**
    * Get's the authors of an article from the metadata and add them to $this->authors
    */
	public function fetchAuthorsFromMetadata(){
		global $INFO;
		if($this->creatorisauthor || !$this->showcreator){
			// Creator is an author (creator not in $INFO['meta']['contributor'] if he/she made no changes
			list($creator,$creatorfullname) = $this->getCreator();
			if(!empty($creator) ) $this->addAuthor($creator,$creatorfullname);
		}
		// Authors from metadata
		if(array_key_exists('contributor',$INFO['meta']))	$this->authors =  array_merge($INFO['meta']['contributor'],$this->authors);
	}
	
	/**
    * Sort authors in alphabetical order. After calling this function $this->authors ist ordered.
    */
	public function sortAuthors(){
		if($this->displaystyle != 'fullname' && $this->displaystyle != 'fullname (loginname)'){
			// sort by key
			ksort($this->authors);
		}else{
			// sort by value
			asort($this->authors);
		}
	}
	
	/**
    * Renders all authors and add them to $this->output
    */
	public function renderAllAuthors(){
		 foreach($this->authors as $loginname => $fullname){
				$this->output .= $this->renderOneAuthor($loginname, $fullname);
		 }
	}
    
    /**
    * Finish to render the authorlist: close all open tags.
    */
    public function finishList(){
		if($this->printempty || !empty($this->authors)){
			// close <ul>
			if($this->displayaslist) $this->output .= "</ul>".DOKU_LF;
			// close div
			$this->output .= "</div>".DOKU_LF;
		}
		if($this->showcreator == "below" ) $this->output .= $this->renderCreator();
		return true;
	}
	
	/**
    * Returns the current output. Makes sense after calling startlist() renderAllAuthors and finishList()
    */
	public function getOutput(){
		return $this->output;	
	}
	
	/**
    * Builds the html-code for one author (called for each author by renderAllAuthors())
    */
	public function renderOneAuthor($loginname, $fullname=''){
		$loginname = htmlspecialchars($loginname);
		$fullname = htmlspecialchars($fullname);
		// Find text to display on the site.
		switch($this->displaystyle){
				case "fullname": $display = $fullname; break;
				case "loginname (fullname)": $display = $loginname.($fullname != ''?" (":"").$fullname.($fullname != ''?")":""); break;
				case "fullname (loginname)": $display = $fullname != ''?"$fullname ($loginname)":$loginname; break;
				default: $display = $loginname;
		}
		$inner = ">"; // $this->openTag has no closing > (so it's possible to add an title="..."
		// Find title
		if($this->linkto != 'email'){
			switch($this->tooltip){
				case "loginname": $inner = "title=\"$loginname\">"; break;
				case "fullname": $inner = "title=\"$fullname\">"; break;
			}
		}
		global $auth; // if we need to get the eMail-adress.
		// build a link if necessary
		switch($this->linkto){
				case 'email': $userdata = $auth->getUserData($loginname); $display= $this->email($userdata['mail'], $display, 'authormail'); break;
				case 'userhomepage': $display = $this->linkToUserhomepage($loginname, $display); break;
		}
		// Return the htmlcode for one author.
		return $this->openTag.$inner.$display.$this->closeTag;
	}
	
	/**
    * Builds the html-code for a link to a userhompage 
    * 
    * @param $loginname String Login name of an user.
    * @param $display String The Text should be displayed as link.
    */
	private function linkToUserhomepage($loginname, $display){
			$userpageid = str_replace("%USER%",$loginname, $this->userpageid);
			$userpageid = htmlspecialchars($userpageid);
			return "<a href=".wl($userpageid,'',true)." class='authoruserpage' title='".($this->tooltip == 'none'?$userpageid:'')."'>".$display."</a>";
	}
	
	/**
    * Find's the right css-class, depending on on the linkto option.
    */
	private function findCssClass(){
		switch($this->linkto){
                case "userhomepage": $this->cssClass = "'authoruserpage'"; break;
                case "email": $this->cssClass = "'authoremail'"; break;
                default: $this->cssClass = "'author'";
            }
        return true;
	}
	
	/**
    * If displayed as list, we need a <li>-Tag, else we need a <span>-Tag
    */
	private function findOpenAndCloseTags(){
		if($this->displayaslist){
			$this->openTag = DOKU_TAB."<li class='level1'><div class=".$this->cssClass." ";
			$this->closeTag = "</div></li>".DOKU_LF;           
        }else{
			$this->openTag = DOKU_TAB."<span class=".$this->cssClass." ";
			$this->closeTag = "</span>".DOKU_LF;
        }
		return true;
	}
	
	/**
    * Get the creator from the metadata (https://www.dokuwiki.org/devel:metadata)
    */
	private function getCreator(){
		global $INFO;
		return array($INFO['meta']['user'],$INFO['meta']['creator']);
	}
	
	/**
    * Builds the html-code for the creatorline.
    */
	private function renderCreator($creator = '', $cTime = ''){
		$creator = htmlspecialchars($creator);
		$cTime = htmlspecialchars($cTime);
		global $INFO;
		// Get metadata if parameters are empty
		if($creator == '' && $cTime == ''){
			list($creator,$creatorfullname) = $this->getCreator();
			$ctime = $INFO['meta']['date'];
			$ctime = $ctime['created'];
		}
	   //$creator = "<span class=".$this->cssClass."";
       // Handle template for this line
       if($creator =='') return "";
       switch($this->displaystyle){
			case "fullname": $creator = $creatorfullname; break;
			case "loginname (fullname)": $creator = $creator.($creatorfullname != ''?" (":"").$creatorfullname.($creatorfullname != ''?")":""); break;
			case "fullname (loginname)": $creator = $creatorfullname != ''?"$creatorfullname ($creator)":$creator; break;
		}
	   global $conf;
	   // build text from template
       $output = str_replace("%CREATOR%",$creator, $this->creatortext);
       $output = str_replace("%DATE%",strftime($conf['dformat'],$ctime), $output);
       return $output.DOKU_LF;
    }
    
    /**
    * Get the fullname for a given login name.
    */
    public function getFullname($loginname){
		global $auth;
		$userdata = $auth->getUserData($loginname);
		return $userdata['name'];
	}
}
