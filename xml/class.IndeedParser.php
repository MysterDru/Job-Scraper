<?php

class IndeedParser {
	
	public $jobtitle;
	public $company;
	public $location;
	public $country;
	public $source;
	public $dateposted;
	public $snippet;
	public $url;
	public $latitude;
	public $longitude;
	public $jobkey;		
	public $sourceURL;
	public $bodyDump;
	
	public $coordinates;
	public $dateformatted;
	
	/** variables not from api, stored for temp purposes **/
	public $pay 		= null;
	public $education 	= null;
	public $duration	= null;
	public $county		= null;
	public $zipcode		= null;
	public $licensing	= null;
	
	public $redirectURL;
	
	public $xml;
	
	private $xmlCount	= 0;
	
	function __construct( $xmlSource ) {
		
		$this->xml = new DOMDocument();
		$this->xml->loadXML( $xmlSource );
		//print( "->INDEED PARSER CONSTRUCTOR");
		$this->xmlCount = $this->xml->getElementsByTagName( 'result' )->length;
	}
	
	public function GetCount() {
			
		return $this->xmlCount;
		
	}
	
	public function ParseXML( $pos ) {		
		/** xml parsing for httpresponse **/
		
			$result = $this->xml->getElementsByTagName( 'result' )->item( $pos );
		
//		foreach( $results as $result ) {			
			
			$this->jobtitle 	= $result->getElementsByTagName('jobtitle')->item(0)->nodeValue;
			//print( $this->jobtitle . "<br />");
			$this->company		= $result->getElementsByTagName('company')->item(0)->nodeValue;
			//print( $this->company . "<br />");
			$this->city			= $result->getElementsByTagName('city')->item(0)->nodeValue;
			//print( $this->city . "<br />");
			$this->state		= $result->getElementsByTagName('state')->item(0)->nodeValue;
			//print( $this->state . "<br />");
			$this->location		= $result->getElementsByTagName('formattedLocation')->item(0)->nodeValue;
			//print( $this->location . "<br />");			
			$this->country		= $result->getElementsByTagName('country')->item(0)->nodeValue;
			//print( $this->country . "<br />");
			$this->source 		= $result->getElementsByTagName('source')->item(0)->nodeValue;
			//print( $this->source . "<br />");
			$this->dateposted 	= $result->getElementsByTagName('date')->item(0)->nodeValue;
			//print( $this->dateposted . "<br />");
			$this->snippet		= $result->getElementsByTagName('snippet')->item(0)->nodeValue;
			//print( $this->snippet . "<br />");
			$this->url			= $result->getElementsByTagName('url')->item(0)->nodeValue;
			//print( $this->url . "<br />");
			$this->latitude		= $result->getElementsByTagName('latitude')->item(0)->nodeValue;
			//print( $this->latitude . "<br />");
			$this->longitude	= $result->getElementsByTagName('longitude')->item(0)->nodeValue;
			//print( $this->longitude . "<br />");
			$this->jobkey		= $result->getElementsByTagName('jobkey')->item(0)->nodeValue;
			//print( $this->jobkey . "<br />");
			
			$this->coordinates	= $this->latitude . ', ' . $this->longitude;	
			//print( $this->coordinates . "<br />");			
			$this->sourceURL	= $this->GetSourceUrl();
			//print( $this->sourceURL . "<br />");
			$this->bodyDump		= $this->GetBodyText();
			//print( $this->bodyDump . "<br />");
			$this->dateformatted= $this->FormatDate();
			//print( $this->dateformatted . "<br />");
//		}

	}
	
	private function GetSourceUrl() {
	
		$html = file_get_html( $this->url );		
		
		foreach( $html->find( 'a.view_job_link' ) as $element ) {
			$finalLinks[ $i ] = $element->href;
			
			//fix incomplete URLs - test to see if it starts with http
			$testURL = substr( $finalLinks[ $i], 0, 4 );
			
			//variable for fix
			$URLFix = "http://www.indeed.com";
			
			if( $testURL != 'http' )
				$this->redirectURL = $URLFix . $finalLinks[ $i ];
			else $this->redirectURL = $finalLinks[ $i ];
			//print( " redirect:" . $this->redirectURL );		
 			break;
		}	

		$redirect = new /*URLRedirect*/RedirectFollow;		
		try {
			return $redirect->follow_redirects( $this->redirectURL );
		} catch( Exception $e ) {
			return "Could not connect to host site.";
		}
 
	}
	
	private function GetBodyText() {
		//set_time_limit( 3 );
		$html 		= new HTMLStrip();
		
		$content = file_get_contents( $this->sourceURL );
		//$dom->preserveWhiteSpace = false;
		
		return $html->strip_html_tags( $content );		
	}
	
	private function FormatDate() {
		
		$tempDate = explode( ", ", $this->dateposted );
		$tempDate = $tempDate[ 1 ];
		$tempDate = explode( " ", $tempDate );
		$YYYY = $tempDate[2]; $DD = $tempDate[0]; $MM;
		
		switch( $tempDate[ 1 ] ) {
			case "Jan" 		: $MM = 01; break;
			case "Feb"		: $MM = 02; break;
			case "Mar" 		: $MM = 03; break;
			case "April" 	: $MM = 04; break;
			case "May" 		: $MM = 05; break;
			case "Jun" 		: $MM = 06; break;
			case "Jul" 		: $MM = 07; break;
			case "Aug" 		: $MM = 08; break;
			case "Sep" 		: $MM = 09; break;
			case "Oct" 		: $MM = 10; break;
			case "Nov" 		: $MM = 11; break;
			case "Dec" 		: $MM = 12; break;
			default 		: $MM = 00; break;
		}
		
		return $YYYY . "-" . $MM . "-" . $DD . " " . $tempDate[ 3 ];
	}
}

?>