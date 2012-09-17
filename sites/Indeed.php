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
require_once( "../xml/class.IndeedParser.php" );
require_once( "../util/class.ProcessSkills.php" );

class Main {
	public $db;

	/** array values to query individual categories **/	
	public $_CATEGORYARRAY 	= Array( "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31" ); 
	public $sleep			= 10; 		// duration to wait between each category process
	public $sleepLong		= 30; 		// duration to wait between each chunk process
	
	public $zipcode;					// zipcode to start the search radius from, will be passed into the constructor.
	public $currEnum		= 0;		// current enumeration that the script is running through. Will be used to determine start number of getting values
	
	public $jobCount		= 0;		// track the number of jobs added to the database
	public $SourceBoard		= "Indeed";	// Job board these results are coming from
	
	function __construct( $zip, $enum ) {
		$this->zipcode 	= $zip;		
		$this->currEnum = $enum;		
		$this->db 		= new DatabaseManager;
		
	}

	public function StartScrape( $id ) {
		$this->db->OpenDB();
		$jobCategories = $this->db->GetJobCategories(/*$this->_CATEGORYARRAY*/ array( $id ) );
		$this->db->CloseDB();

		if( count( $jobCategories ) > 0 ) {
			
			for( $i = 0; $i < count( $jobCategories ); $i++ ) {
				$category = $jobCategories[ $i ];	
				$this->ProcessJobArray( $category );
			}

		}
	}

	public function ProcessJobArray( $item ) {
				
//		echo("<h3>"  . $item[0] . ": " . $item[ 1 ] ."</h3>\n" );
							
		$this->BuildQuery( $item );

		return true;
	}	
		
	public function BuildQuery( $jobsearch ) {	
	
		/** manage query **/	
		$KEY		= "5775835590700813";				// Publisher API Key
		$v;												// Version. Must be version 2, left blank for default.
		$l 			= $this->zipcode;					// Start search radius from Goodnow, WI 54529 | 54455 is Dancy Wi (central)
		$sort;											// Sort by relevance or date. Default is relevance.
		$radius		= 60;								// Expand search radius to 60 miles.
		$st			= "";
		$jt			= "";
		$start		= ( $this->currEnum * 10 ) - 10;	// Start results at this result number, beginning with 0. Default is 0.
		$limit		= $this->currEnum * 10;				// Maximum number of results returned per query. Default is 10
		$latlong 	= 1; 								// If latlong=1, returns latitude and longitude information for each job result. Default is 0.	
		$co			= "us";								// Search within country specified. Default is us				
		$fromage; 										// Number of days back to search.
		$highlight	= 0;								// Setting this value to 1 will bold terms in the snippet that are also present in q. Default is 0.
		$filter		= 1;								// Filter duplicate results. 0 turns off duplicate job filtering. Default is 1.	
		
		// empty variables, for use later with an expanded query string
		// left null until further use
		  

		// replace white space in query
		$q = str_replace( " ", "%20", $jobsearch[ 1 ] );

		/** create query string that will be passed into the indeed api **/
		$queryString = "http://api.indeed.com/ads/apisearch?publisher=" . $KEY . "&q=" . $q . "&l=" . $l . "&sort=" . $sort . "&radius=" . $radius . "&st=" . $st . 
			"&jt=" . $jt . "&start=" . $start . "&limit=" . $limit . "&fromage=" . $fromage . "&filter=" . $filter . "&latlong=" . $latlong . "&co=" . $co . 
			"&chnl=&userip=1.2.3.4&useragent=Mozilla/%2F4.0%28Firefox%29&v=2";
		
		print( "<br /> query: " . $queryString );
			
		$this->ProcessJobs( file_get_contents( $queryString ), $jobsearch[ 0 ] );
	}
	
	public function ProcessJobs( $xml, $jobcat ) {
			
		$indeedXML = new IndeedParser( $xml );
		$count = $indeedXML->GetCount();
print ("<br />Job Titles:<ul>");		
		for( $i = 0; $i < $count; $i++ ) { 
			set_time_limit( 0 );
			$valid = $indeedXML->ParseXML( $i );
			
			if( $valid == "null") { return false; }
			else {

				$this->db->OpenDB();				

				$cityData 	= $this->db->GetCityData( $indeedXML->city );
				$city 		= $cityData[ 0 ]; $state 	= $cityData[ 1 ];
				$county 	= $cityData [ 2 ]; $zip 	= $cityData[ 3 ];
				
				if( $city == "" ) { return true; }

				print( '
					<li>' . $indeedXML->jobtitle . '</li>' );				
									
				if( $this->db->GetCounty( trim( $county ) ) >= 1 ) {
				
					$insertJob = $this->db->insert( $this->SourceBoard, $indeedXML->jobtitle, $indeedXML->dateformatted, $indeedXML->company, $jobcat, $indeedXML->jobkey, $indeedXML->bodyDump, $indeedXML->pay, $indeedXML->duration, $indeedXML->education, $indeedXML->coordinates, $county, $city, $state, $zip, $indeedXML->country, $indeedXML->sourceURL, $indeedXML->licensing );
					if( $insertJob == "true" ) {

						$skills = new ProcessSkills( $indeedXML->bodyDump, $this->db->lastJobInsertID, 1 ); // get only skills from Stack Overflow
						$this->jobCount += 1;
					}					
					else {
					}
				}
				else {
				}

				$this->db->CloseDB();
				
			}

 		}
print ("</ul>");
		return true;
	}		
}

$jobsAdded;
$processFile 	= "status.txt";
$currID			= trim( file_get_contents( $processFile ) );
file_put_contents( $processFile, $currID + 1 );

$enumerations = 2; //multiply this number by 10. 1 will do 10 results, 2 will do 20 results, etc.
$zipcodes = array( "54455", "54529" );

for( $i = 1; $i <= $enumerations; $i++ ) {			
	foreach( $zipcodes as $zip ) {
		
		$main = new Main( $zip, $i );
		$main->db->OpenDB();
		$catArray = $main->db->GetJobCategories();
		$main->db->CloseDB();		

		if( count( $catArray ) >= $currID ) {

			$main->StartScrape( $currID );

		}
		else {
			echo "Error: Category ID out of range."; 
			file_put_contents( $processFile, 1 );				
			exit(); 
		}
		$jobsAdded += $main->jobCount;
	}
}
echo $jobsAdded . " jobs added to the database";
