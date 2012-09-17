<?php
set_time_limit( 0 );						//disable timelimit on script runtime
//error_reporting(E_ALL); 
//ini_set( "display_errors", 0); 			// hide warnings and notices
ini_set( "mysql.connect_timeout", 5000);	// set mysql connection timeout to 5 seconds
/** database includes **/
require_once( "../database/class.DatabaseManager.php" );
/** html dom includes **/
require_once( '../htmldom/HtmlParser.php' );
require_once( '../htmldom/class.HtmlStrip.php' );
require_once( '../htmldom/URLRedirect.php' );	
require_once( '../htmldom/FollowURLRedirect.php' );	
require_once( '../htmldom/class.FindCounty.php' );	
/** xml includes **/
require_once( "../xml/class.CareerBuilderParser.php" );
require_once( "../util/class.ProcessSkills.php" );

class CareerBuilder {
	public $db;

	/** array values to query individual categories **/	
	public $_CATEGORYARRAY 	= Array( "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31" ); 
	public $sleep			= 10; 				// duration to wait between each category process
	public $sleepLong		= 30; 				// duration to wait between each chunk process
	
	public $zipcode;							// zipcode to start the search radius from, will be passed into the constructor.
	public $currEnum		= 0;				// current enumeration that the script is running through. Will be used to determine start number of getting values
	
	public $jobCount		= 0;				// track the number of jobs added to the database
	public $SourceBoard		= "CareerBuilder";	// Job board these results are coming from
	
	public function CareerBuilder( $zip ) {
		$this->zipcode 	= $zip;		
		$this->db 		= new DatabaseManager;		
		$this->BuildQuery();	
	}
		
	public function BuildQuery() {	
	
		/** manage query **/	
		$KEY		= "WDhc3YN75QBHGXQJY2J7";			// Developer API Key
		$l 			= $this->zipcode;					// Start search radius from Goodnow, WI 54529 | 54455 is Dancy Wi (central)
		$radius		= 50;								// Expand search radius to 60 miles.
		$cat		= "Information%20Technology";		// Category to search for
		$order		= "Date";
		$exclude	= "True";							// If set to true will exclude jobs whose location is "US-Nationwide"
		$limit		= 100;								// Number of items to return on a page.
		// empty variables, for use later with an expanded query string
		// left null until further use
		  

		// replace white space in query
		$q = str_replace( " ", "%20", $jobsearch[ 1 ] );

		/** create query string that will be passed into the indeed api **/
		$queryString = "http://api.careerbuilder.com/v1/jobsearch?DeveloperKey=" . $KEY . "&Location=" . $l . "&Radius=" . $radius . "&Category=" . $cat . "&OrderBy=" . $order . "&ExcludeNational=" . $exclude . "&PerPage=" . $limit; 
		
		print( "<br /> query: " . $queryString );
			
		$this->ProcessJobs( file_get_contents( $queryString ), $jobsearch[ 0 ] );
	}
	
	public function ProcessJobs( $xml, $jobcat ) {
			
		$cbXML = new CareerBuilderParser( $xml );
		$count = $cbXML->GetCount();
		
		for( $i = 0; $i < $count; $i++ ) {
//print( '<br /><ul>');						
			$cbXML->ParseXML( $i );
			
			$this->db->OpenDB();	//open the database connection
			
			$local = $this->ParseLocation( $cbXML->location );
			$city = $local[ 1 ];
			$cityData = $this->db->GetCityData( $city );
			$city 		= $cityData[ 0 ]; $state 	= $cityData[ 1 ];
			$county 	= $cityData [ 2 ]; $zip 	= $cityData[ 3 ];
			$country 	= "US";
			
			if( $city != "" ) { // some locations are entered as "WI - Wisconsin", not saving these			
/*				print( '
					<li>Job Title: ' . $cbXML->jobtitle . '</li>
					<li>Company: ' . $cbXML->company . '</li>
					<li>Location: ' . $city . ', ' . $county . ', ' . $state . ' ' . $zip . '</li>				
					<li>Source: ' . $cbXML->sourceURL . '</li>
				');
*/				
				if( $this->db->GetCounty( trim( $county ) ) >= 1 ) {
					$category = $this->db->GetJobCategories( null, 'Information Technology' );
					$jobCat = $category[ 0 ]; // job category id
					
					$insertJob = $this->db->insert( $this->SourceBoard, $cbXML->jobtitle, $cbXML->dateformatted, $cbXML->company, $jobcat, $cbXML->jobkey, $cbXML->description, $cbXML->pay, $cbXML->duration, $cbXML->education, $cbXML->coordinates, $county, $city, $state, $zip, $country, $cbXML->sourceURL, $cbXML->licensing );
					if( $insertJob == "true" ) {
						$skills = new ProcessSkills( $cbXML->description, $this->db->lastJobInsertID, 1 ); // get only skills from Stack Overflow
						$this->jobCount += 1;
					}					
					else {
					}
					$this->jobCount += 1;
				}
				else {
				}
			}			
			$this->db->CloseDB(); 	// close the database connection
//print( '</ul>');				
		}
		
		return true;
	}
	
	private function ParseLocation( $local ) {
		
		// $local[ 0 ] = state
		// $local[ 1 ] = city
		$local = explode( "-", $local );
		
		return array( trim( $local[ 0 ] ), trim( $local[ 1 ] ) ); 		
	}		
}


$jobsAdded = 0;
$zipcodes = array( "54455", "54529" );
foreach( $zipcodes as $zip ) {
	$cb = new CareerBuilder( $zip );
	$jobsAdded += $cb->jobCount;
}

print( $jobsAdded . " added to database from CareerBuilder" );
?>