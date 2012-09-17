<?php

class CareerBuilderParser {
	
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
	public $description;
	
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
	public $bodyXML;
	
	private $xmlCount	= 0;
	
	function __construct( $xmlSource ) {
		
		$this->xml = new DOMDocument();
		$this->xml->loadXML( $xmlSource );
		//print( "->INDEED PARSER CONSTRUCTOR");
		$this->xmlCount = $this->xml->getElementsByTagName( 'JobSearchResult' )->length;
		print( "XML COUNT: " . $this->xmlCount );
	}
	
	public function GetCount() {
			
		return $this->xmlCount;
		
	}
	
	public function ParseXML( $pos ) {		
		/** xml parsing for httpresponse **/
		
			$result = $this->xml->getElementsByTagName( 'JobSearchResult' )->item($pos);
			
			$this->jobtitle 	= $result->getElementsByTagName('JobTitle')->item(0)->nodeValue;
			$this->company		= $result->getElementsByTagName('Company')->item(0)->nodeValue;
			$this->location		= $result->getElementsByTagName('Location')->item(0)->nodeValue;
			$this->dateposted 	= $result->getElementsByTagName('PostedDate')->item(0)->nodeValue;
			$this->sourceURL	= $result->getElementsByTagName('JobDetailsURL')->item(0)->nodeValue;			
			$this->url			= $result->getElementsByTagName('JobServiceURL')->item(0)->nodeValue; // api version of the deatils page (returns as xml)
			$this->latitude		= $result->getElementsByTagName('LocationLatitude')->item(0)->nodeValue;
			$this->longitude	= $result->getElementsByTagName('LocationLongitude')->item(0)->nodeValue;
			$this->jobkey		= $result->getElementsByTagName('DID')->item(0)->nodeValue;
			
			$this->coordinates	= $this->latitude . ', ' . $this->longitude;	
			$this->description	= $this->GetBodyText();
			$this->dateformatted= $this->FormatDate( $this->dateposted );
	}
	
	private function GetBodyText() {

		$htmlStrip = new HTMLStrip;

		$this->bodyXML = new DOMDocument();
		$this->bodyXML->loadXML( file_get_contents( $this->url ) );
		
		$tempDescrip = $this->bodyXML->getElementsByTagName( 'JobDescription' )->item(0)->nodeValue;
		$tempRequire = $this->bodyXML->getElementsByTagName( 'JobRequirements' )->item(0)->nodeValue;
		
		$descrip = $htmlStrip->strip_html_tags( html_entity_decode( $tempDescrip ) ); // convert entiy codes to html, and then strip the html
		$require = $htmlStrip->strip_html_tags( html_entity_decode( $tempRequire ) ); // convert entiy codes to html, and then strip the html
		
		return $descrip . " " . $require;
				
	}
	
	private function FormatDate( $date ) {
				
		$tempDate = explode( "/", $date );			
		$YYYY = $tempDate[ 2 ]; $DD = $tempDate[ 1 ]; $MM = $tempDate[ 0 ];
		return $YYYY . "-" . $MM . "-" . $DD;
		
	}	
}

?>