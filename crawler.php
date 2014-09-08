<?php
/**
 * PHP crawler
 * @package php crawler
 * @author R Gowtham Vasishta
 */
 
/* HTML DOM parser  */
include_once('simple_html_dom.php');
ini_set('max_execution_time', 60);
class crawler
{
	//database credentials && user agent details
	protected $host = "localhost";				          //mysql host name
	protected $database = "database_name";		         //logical database name on your server
	protected $user="username";					        //username of your database
	protected $password="password";				       //password for login
	public $useragent = "your_useragent";             //User agent of the crawler and call the function 'setUserAgent($ua)'
	public $s_useragent = "cybersbot";               // short name of the bot
	
	//crawler properties
	public $version = "0.1";                                 // version 
	public $product = "Cyber Search Crawler";               // crawler name
	public $website = "http://www.gowthamvasishta.com";    //crawler help site
	private $startUrl;                                    // seed url to start crawling
	private $maxurls = 3;						         // declare max no. of url to processed
	private $depth = 2;							        // declare the levels of the tree
	private $unvisit_url = array();			       // stores the unvisited urls
	private $visit_url  = array();                  // stores the visited urls
	private $robotsTxt = false;                      // presence of robot.txt, by default false
    
	public function crawling($s_url)
	{
		//store starting url for future reference
		$this->startUrl = $s_url;
		/* keep the starting url in unvisited queue */
		array_push($this->unvisit_url, $this->startUrl);
		/* find number of elements in the queue */
		$this->maxUrls = sizeof($this->unvisit_url);
	
		foreach($this->unvisit_url as $key =>$url)
		{ 
			// Insert the url into the visited array
			$this->visit_url[$key] = $this->unvisit_url[$key];
			
			// remove the url from unvisited array
			unset($this->unvisit_url[$key]);
			//$this->unvisit_url[$key] = null;
			//set the index for user friendliness
			
			
			if(!isset($url))  {                                        //url is null then don't proceed
				echo "no url";
				break;  
			}
			
			elseif($this->protocol_check($url))  {                     //check for http or https
				echo "not http";
				continue;
			} 
			
			elseif($this->robotPermitted($url, $this->s_useragent)) {         //checks for robot.txt permission for $url
				
				// fetch the html data
				$htmlpage = $this->get_html($url);
				
				//check pdf file
				if($this->pdf_check($htmlpage)) {
						echo "FILE TYPE: PDF";
				}
				else {
				
					// extract the details
					
					$title = $this->get_title($url);
					$meta = array();
					$meta = $this->get_meta($url);
					$plaintext = $this->get_text($htmlpage);
					echo $title;
					echo "<br><br><br><br>";
				    if($this->depth != 0)
					{
						$links = array();
						$links = $this->get_links($htmlpage);
						$imagelinks = array();
						$imagelinks = $this->get_imagelinks($htmlpage);
						echo "gowtham is genius ";
					}
					
					var_dump($links);
					
					
					if($this->depth !== 0) {
						for($i=0; $i<$this->maxurls-1; $i++) {
							$this->unvisit_url[$i] = $links[$i];
						}
						$url_no = sizeof($this->unvisit_url);
					}
					while(isset($this->unvisit_url))
					{
						foreach($this->unvisit_url as $key =>$url)
						{ 
							$visit_length = sizeof($this->visit_url);
							
							//check weather the url is already present in the database
							/*echo" kkjklasj  visit length is $visit_length";
							for($i=0; $i<$this->maxurls-1; $i++)
							{
								if($this->visit_url[$i] == $this->unvisit_url[$key])
									break ;
							}
							*/
							
							$this->visit_url[$key+1] = $this->unvisit_url[$key];
							$this->unvisit_url[$key] = null;
							if(!isset($url))  {                                        //url is null then don't proceed
								
								break 3;  
							}
			
							elseif($this->protocol_check($url))  {                     //check for http or https
								echo "not http";
								continue;
							} 
			
							elseif($this->robotPermitted($url, $this->s_useragent)) {         //checks for robot.txt permission for $url
				
								// fetch the html data
								$htmlpage = $this->get_html($url);
				
								//check pdf file
								if($this->pdf_check($htmlpage)) {
									echo "FILE TYPE: PDF";
								}
								else {
				
								// extract the details
					
									$title = $this->get_title($url);
									$meta = array();
									$meta = $this->get_meta($url);
									$plaintext = $this->get_text($htmlpage);
									echo $title;
									echo "<br><br>";
									echo $plaintext;
									echo "<br><br><br><br>";
								}
							}
							
							else {
							
								echo "robot not permitted";
							}
							
							
						} //end second for
					} // end second while
					
					
				}
			
			}
			else {                                                     //if neither of the condition is met, then go to next url
				echo "robot not permitted";
				continue;
			}
			
		}//end first for
}
	
	
	// Returns false if the URL protocol is 'http' or 'https'
	public function protocol_check($url)
	{
		$parsed_url = parse_url($url);
		if($parsed_url['scheme'] == 'http' || $parsed_url['scheme'] == 'https')
			return false;
		else
			return true;
	}
	
	//fetches the content or plain text from a web page
	public function get_html($url)
	{ 
		$html_content = file_get_html($url);
		return $html_content;
	}

	//returns false if the given file is pdf
	public function pdf_check($htmlpage)
	{
		$cod_pattern = "%PDF";
		$cod = mb_substr($htmlpage, 0, 4);
			if($cod == '%PDF') 
				return true;
			else
				return false;
	}

	
	
	
	//fetches plain text from a html page
	public function get_text($htmlpage)
	{
		$text = $htmlpage->plaintext;
		return $text;
	}
	 
	
	//fetch the links from the webpage
	public function get_links($htmlpage)
	{
		$counter = 0; //counter set to 0
		foreach($htmlpage->find('a') as $element) {
		      $links[$counter] = $element->href;
			  $counter++;
		}
		
		return $links;
	}
	
	
	//  fetches image links from the webpage
	public function get_imagelinks($htmlpage)
	{
	    $counter = 0; //counter set to 0
		foreach($htmlpage->find('img') as $element) {
		      $links[$counter] = $element->src;
			  $counter++;
		}
		return $links;
	}
	
	
	// fetches title from the webpage
	public function get_title($url)
	{
		$html = new simple_html_dom();
        $g = $html->load_file($url); 
        $title = $html->find('title',0)->innertext;
		return $title;
	}
	
	// fetches meta  information
	public function get_meta($url)
	{
		$keywords = null;
		$describe = null;
		$tags = get_meta_tags($url);
		if(isset($tags['keywords']))
			$keywords = $tags['keywords'];
		if(isset($tags['description']))
			$describe = $tags['description']; 
		
		return array( $keywords, $describe);
	}
	
	
	
/*
 * Robot.txt parser and url checker
 *
 * 
 */
	//set user agent 
	function setUserAgent()
    {
		$ua = $this->useragent;
        ini_set('user_agent', $ua);
    }

	/* get the robot rules
	 * 
	 * i/p( path =>  http://www.example.com/dir && useragent => 'somebot')
	 * o/p( true, if permitted && false, if not permitted )
	*/
    function robotPermitted($path, $useragent)
    {
        $host = $this->getHost($path);
		
		global $robotsTxt;
		$robotsTxt = @file('http://'.$host.'/robots.txt');
		
        if ($robotsTxt === false) { return true; }
    
        $rules = array();
        $applies = false;
        
        foreach ($robotsTxt as $line)
        {
            $match = array();
            
            // Check to see if the user-agent command applies to us
            if (preg_match('/User-agent:(.*)/i', $line, $match))
            {
                $applies = false;
            
                $bot = trim($match[1]);
                if ($bot === '*')
                {
                    $applies = true;
                } else {
                    $applies = preg_match("/$bot/i", $useragent);
                }
            }
            
            $match = array();
            
            /* Add a rule */
            if ($applies && preg_match('/Disallow:(.*)/i', $line, $match))
            {
                if (!$match[1]) { return true; } // an empty disallow means allowed
            
                $page = trim($match[1]);
                
                $rules[] = preg_quote($page, '/').'*';
            }
            
        }
         
        foreach ($rules as $rule)
        {
            if (preg_match("/$rule/i", $path)) { return false; }
        }
       
        return true;
    }
	 
	/*
	 * returns host from a url
	 *
	 * i/p (url => http://www.example.com/dir ) 
	 * o/p( host => www.example.com ) 
	*/
	function getHost($url) { 
		$parseUrl = parse_url(trim($url)); 
		return trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2))); 
	} 
	
	
	
/*
 * database connectivity functions
 *
 *
*/
	
	
	
	
	//establishment of connection to database
	public function connect()
	{
		$conn = mysql_connect($this->host, $this->user, $this->password);
		if(!$conn)
			echo "connection failed";
	}
	
	//selecting a database
	public function select_db($my_db)
	{
		$this->connect();
		$db = mysql_select_db($my_db);
		if(!$db)
			echo "failed to select $my_db ";
		
	}

/* 
 * cybers crawl documentation function 
 */
	 
	// Provides details of the crawler
	public function get_details()
	{
		
		echo "<b>Product:</b> ".$this->product ."<br/>";
		echo "<b>Version:</b> ".$this->version ."<br/>";
		echo "<b>Author:</b> R Gowtham Vasishta <br/>";
		echo "<b>Website:</b> ".$this->website ."<br/>";
		echo "<b>Date :</b> 2nd december, 2013 <br/>";
		echo "<b>Copyrights :</b> 2013-2014 <br/>";
	}








} //end class crawling
$o = new crawler();
$k = array();
$o->crawling('http://www.gowthamvasishta.com');
//$o->get_details();
//var_dump($k);


?>
