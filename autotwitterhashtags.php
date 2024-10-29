<?php
/*
Plugin Name: Auto Twitter Hashtags
Plugin URI: http://albinofruit.com/plugins/auto-twitter-hashtags
Description: Auto Twitter Hashtags is a WordPress plugin that reads the content of your posts and pages for hash-tagged words and links them to the relevant search entry in Twitter.
Author: Kayleigh Thorpe
Version: 1.1
Author URI: http://albinofruit.com
*/

require_once ABSPATH . WPINC . "/post.php";


if ( !class_exists("AutoTwitterHashtags") ) {
class AutoTwitterHashtags {

    var $name = "AutoTwitterHashtagsPlugin";
    var $shortName = "AutoTwitHash";
    var $longName = "Auto Twitter Hashtags Plugin";
    var $adminOptionsName = "AutoTwitterHashtagsPluginAdminOptions";
    
    var $debug = false;


    
    function log($message) {
        if ($this->debug)
            error_log($message . "\n", 3, "/tmp/twithash.log");
    
    }
    
    //PHP4 constructor
	function AutoTwitterHashtags() {$this->__construct();} 
    
    //PHP5 constructor
    function __construct() {
	    // WordPress Hooks
        //add_action('admin_menu', array(&$this, 'addAdminPanel'));  
		add_filter('the_content', array(&$this, 'hashtag_filter'));
	}
	
	function _install() {
	    $this->log("Twitter Hashtags installed!");
	}
	
	function _uninstall() {
	    $this->log("Twitter Hashtags uninstalled!");
    }
    

    function unusedfunctionimtoodumbtoclear($link) {
   		list($link, $title) = explode('\|', $link, 2);
    	if (!$title) $title = $link;
    	return array($link, $title);
    }

	/* Content search script.
	 * Finds hashtagged words to convert to links.
	 */
	function hashtag_filter($content) {
		$options = $this->getAdminOptions();

		//Words with hastags.
		preg_match_all('/#([\p{L}\p{Mn}]+)/u', $content, $matches);


		$links = array();
		foreach( $matches[1] as $keyword ) {
			$links[$keyword] = current($matches[0]);
			next($matches[0]);
		}

		foreach( $links as $full_link => $match ) {
			
			
			list($prefix, $sublink) = explode(':', $full_link, 2);

			if ( $sublink ) {
				if ( array_key_exists($prefix, $options['shortcuts']) ) {
					list($link, $subtitle) = $this->unusedfunctionimtoodumbtoclear($sublink);
					$shortcutLink = sprintf( $options['shortcuts'][$prefix],
						rawurlencode($link));
					$content = str_replace($match, 
						"#<a href='https://twitter.com/search?q=$search_title'>$subtitle</a>",
						$content);
					continue;
				}
			}
			
			list($link, $search_title) = $this->unusedfunctionimtoodumbtoclear($full_link);

			if ( $page = get_page_by_title(html_entity_decode($link, ENT_QUOTES)) ) {
				$content = str_replace($match, 
					"#<a href='https://twitter.com/search?q=$search_title' target='_blank'>$search_title</a>",
					$content);

			} else {
				
				$content = str_replace($match, 
					"#<a href='https://twitter.com/search?q=$search_title' target='_blank'>$search_title</a>",
					$content);
			}
		}
		
		return $content;
	}

    function getAdminOptions() {

        $options = array(
            'shortcuts' => $this->defaultShortcuts,
        );
    
    	$savedOptions = get_option($this->adminOptionsName);
    	
		if (!empty($savedOptions)) {
			foreach ($savedOptions as $key => $value) {
			    $options[$key] = $value;
			}
		} 
		
		return $options;
    
    }
    
  

}
}


if (class_exists('AutoTwitterHashtags') && !isset($twitterHashtags_plugin)) {
    $twitterHashtags_plugin = new AutoTwitterHashtags();    
}

?>