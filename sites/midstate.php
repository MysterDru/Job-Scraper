<?php
require_once( '../database/class.DatabaseManager.php' );
require_once( '../htmldom/HtmlParser.php' );
require_once( '../htmldom/class.HtmlStrip.php' );	
require_once( '../htmldom/class.FindCounty.php' );	
require_once( '../util/class.DateFormat.php' );
require_once( '../util/class.ProcessSkills.php' );
class GetMidstateJobs {

	/****** JOB INFO VARIABLES **********
	 * *********************************/
	
		private $employer;
		private $location; 
		private $city; 
		private $state; 
		private $county; 
		private $zip; 
		private $lat; 
		private $lng;
		private $coordinates;
		private $description;
		private $education;
		private $datePosted;
	
	
		private $jobTitle;
		private $jobCat			= 0; // midstate jobs don't correspond to a category
		private $jobKey; 
		private $pay;
		private $duration;
		private $licensing;
		private $country 		= "US";
		private $sourceURL; 
		private $sourceBoard 	= "MidState";
	
		private $db;
		public $jobsAdded		= 0;	
		//private $html;
		//private $htmlStrip;
	/***********************************/
	
	public function GetMidstateJobs( $url ) {
		$this->sourceURL = $url;
		//$htmlStrip = new HTMLStrip;
	}

	public function ParseSource() {
		$html = file_get_html( $this->sourceURL );			
		$htmlStrip = new HTMLStrip;	
		$this->jobTitle = $htmlStrip->strip_html_tags( $html->find( 'td', 0 ) );
		
		// find all table cells (td elements)
		$td = $html->find( 'td' );
		
		foreach( $td as $element ) {
			
			$linebreaks = split( "<br>", $element );
		//	echo "<br />";
			
			// $linebreaks[ 0 ] = "bolded heading", if it exists | a.k.a section title, match against it
			$heading = trim( $htmlStrip->strip_html_tags( $linebreaks[ 0 ] ) );
			switch ( $heading ) {
				case 'Employer Information':
					$this->employer 	= $linebreaks[ 1 ]; // $linebreaks[ 1 ] is the first line of that cell, always the employers name
					break;
				default:				
					break;
			}
		
			// $singleRow[ 0 ] = heading in cases where needed, "bolded heading", split by </strong> 
			// $singleRow[ 1 ] = corresponding description in cases where needed
			$singleRow = split( "</strong>", $element );
			$heading = trim( $htmlStrip->strip_html_tags( $singleRow[ 0 ] ) );
		
			switch ( $heading ) {
				case 'Job Location':			
					$this->location 	= ucwords( strtolower( $htmlStrip->strip_html_tags( $singleRow[ 1 ] ) ) ); // set all words to first letter capital, rest lower
					$this->location 	= split( ",", $this->location );
					$this->city			= trim( $this->location[ 0 ] );
					$this->state 		= trim( strtoupper( $this->location[ 1 ] ) );
					break;
				case 'Posting Date':
					$date 				= trim( $htmlStrip->strip_html_tags( str_replace( "<", "", $singleRow[ 1 ] ) ) );
					$this->datePosted 	= $this->FormatDate( $date );
					break;
				case 'Duties':
					$this->description 	= trim( $htmlStrip->strip_html_tags( $singleRow[ 1 ] ) );
					break;
				case 'Other Qualifications':
					$this->description 	.= " ";
					$this->description 	.= trim( $htmlStrip->strip_html_tags( $singleRow[ 1 ] ) );
					break;
				case 'Minimum Required Education':
					// database currently doesn't handle education categories
					//$this->education 	= trim( $singleRow[ 1 ] );
					break;
				case 'TechConnect Job ID':
					$this->jobKey 		= trim( $htmlStrip->strip_html_tags( $singleRow[ 1 ] ) );
					break;						
				default:
					
					break;
			}
		}
		
		$this->ProcessJob();
	}	

	private function ProcessJob() {
		$this->db = new DatabaseManager;
		$this->db->OpenDB();
		
		$this->cityData 	= $this->db->GetCityData( $this->city );
		$this->county 	= $this->cityData[ 2 ]; $this->zip 		= $this->cityData[ 3 ];
		$this->lat		= $this->cityData[ 4 ]; $this->lng		= $this->cityData[ 5 ];
		
		$this->coordinates = $this->lat . ', ' . $this->lng;
		if( $this->state == "WI" ) {
						
			$insertJob = $this->db->insert( $this->sourceBoard, $this->jobTitle, $this->datePosted, $this->employer, $this->jobCat, $this->jobKey, $this->description, $this->pay, $this->duration, $this->education, $this->coordinates, $this->county, $this->city, $this->state, $this->zip, $this->country, $this->sourceURL, $this->licensing );	
	
			if( $insertJob == "true" ) {
				$skills = new ProcessSkills( $this->description, $this->db->lastJobInsertID, 0 ); // process all skills from skill list ( 0 = all )
				$this->jobsAdded += 1;
			}
		}
		
		$this->db->CloseDB();
	}
	private function FormatDate( $date ) {
				
		$tempDate = explode( "/", $date );			
		$YYYY = $tempDate[ 2 ]; $DD = $tempDate[ 1 ]; $MM = $tempDate[ 0 ];
		return $YYYY . "-" . $MM . "-" . $DD;
		
	}	
}
$jobsAdded 		= 0;
$count			= 0;
$midstateSource = "http://www.timkrause.info/MSTC/";

$processFile 	= "MSstatus.txt";
$currID			= trim( file_get_contents( $processFile ) );

$msHTML = file_get_html( $midstateSource );

foreach( $msHTML->find( "a" ) as $link ) {
	$count += 1;
	file_put_contents( $processFile, $count );		
	if( $currID <= $count ) {						
		$href = "http://www.timkrause.info/MSTC/" . $link->href;	
		$midstate = new GetMidstateJobs( $href );
		$midstate->ParseSource();
		$jobsAdded += $midstate->jobsAdded;
	}
 
}
print( $jobsAdded . " jobs added from MidState");

?>