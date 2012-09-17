<?php
require_once( '../database/class.DatabaseManager.php' );
require_once( '../htmldom/HtmlParser.php' );
require_once( '../htmldom/class.HtmlStrip.php' );	
require_once( '../htmldom/FollowURLRedirect.php' );	
require_once( '../util/class.DateFormat.php' );
require_once( '../util/class.ProcessSkills.php' );
class GetJobCenter {

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
		private $sourceBoard 	= "JobCenter";
	
		private $db;
		public $jobsAdded		= 0;	

		private $case;
	/***********************************/
	
	public function GetJobCenter( $url, $siteCase ) {
		$this->sourceURL 	= $url;
		$this->case 		= $siteCase;
		//$htmlStrip = new HTMLStrip;
	}

	public function setJobTitle( $title ) {
		$this->jobTitle = $title;
	}
	public function setEmployer( $emp ) {
		$this->employer = $emp;
	}
	public function setCity( $cty ) {
		$this->city = $cty;
	}
	public function setDate( $dt ) {
		$this->date = $dt;
	}
	public function setURL( $url ) {
		$this->sourceURL = $url;
	}
	public function ParseSource() {
		$htmlStrip = new HTMLStrip;
		if( $this->case == 0 ) {
			$html = file_get_html( $this->sourceURL );			

		
			//start by confirming new jobNumber
			//$htmlStrip->strip_html_tags(
			$jobKey 			= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_JobNumber]');
			$jobTitle 			= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_JobTitle]');	
			$employer 			= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_EmployerValue]');
			$pay 				= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_PayValue]');
			$duration 			= $html->find('span[span[id=ctl00_ContentPlaceHolder_MainBase_Label_DurationValue]');
			$education 			= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_EducationValue]');
		
			$qualifications 	= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_Experience_QualificationsValue]');
			$duties				= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_DutiesText]');
			
			$city				= $html->find('span[id=ctl00_ContentPlaceHolder_MainBase_Label_CityStateZipValue]');
			//$this->ProcessJob();
			
			$this->jobKey 		= $jobKey[ 0 ]->plaintext;
			$this->jobTitle 	= $jobTitle[ 0 ]->plaintext;
			$this->employer		= $employer[ 0 ]->plaintext;
			
			$qualifications		= $qualifications[ 0 ]->plaintext;
			$duties				= $duties[ 0 ]->plaintext;			
			$city 				= explode( "," , $city[ 0 ]->plaintext );
			
			$this->city			= trim( ucwords( strtolower( $city[ 0 ] ) ) );			
			$this->description	= $qualifications . "<br />" . $duties; 

		}
		
		if( $this->case == 1 ) {
			
			$content = file_get_contents( $this->sourceURL );
			$this->description = $htmlStrip->strip_html_tags( $content );
		}
		
		print( "<ul>
			<li>Job Title: " . $this->jobTitle . "</li>
			<li>Employer: " . $this->employer . "</li>
			<li>City: " . $this->city . "</li>
			<li>Source URL:" . $this->sourceURL . "</li>					
		</ul>");
		
		$this->ProcessJob();
	}	

	private function ProcessJob() {
		$this->db = new DatabaseManager;
		$this->db->OpenDB();
		
		
		$cityData	= $this->db->GetCityData( $this->city );
		$this->state	= $cityData[ 1 ];
		$this->county 	= $cityData[ 2 ]; $this->zip 		= $cityData[ 3 ];
		$this->lat		= $cityData[ 4 ]; $this->lng		= $cityData[ 5 ];
		
		$this->coordinates = $this->lat . ', ' . $this->lng;
		if( $this->state == "WI" ) {
						
			$insertJob = $this->db->insert( $this->sourceBoard, $this->jobTitle, $this->datePosted, $this->employer, $this->jobCat, $this->jobKey, $this->description, $this->pay, $this->duration, $this->education, $this->coordinates, $this->county, $this->city, $this->state, $this->zip, $this->country, $this->sourceURL, $this->licensing );	
	
			if( $insertJob == "true" ) {
				$skills = new ProcessSkills( $this->description, $this->db->lastJobInsertID, 0 ); // process all skills from skill list ( 0 = all )
				$this->jobsAdded += 1;
			}
		}
		echo "Did not create entry <br />";
		$this->db->CloseDB();
	}
	private function FormatDate( $date ) {
				
		$tempDate = explode( "/", $date );			
		$YYYY = $tempDate[ 2 ]; $DD = $tempDate[ 1 ]; $MM = $tempDate[ 0 ];
		return $YYYY . "-" . $MM . "-" . $DD;
		
	}	
}

$source = "https://jobcenterofwisconsin.com/Presentation/JobSeekers/JobOrderList.aspx?onet=15&loc=001,041,067,069,073,085,097,125,141&wd=30";

$URLfix = "https://jobcenterofwisconsin.com/Presentation/JobSeekers/";

$html = new simple_html_dom();
$html->load_file( $source );

$tableString = "table[id=ctl00_ContentPlaceHolder_MainBase_GridViewJobs]";

$jobTitles 	= array();
$employers 	= array();
$cities		= array();
$dates		= array();
$links		= array();
/** get links from job titles **/

$rows = $html->find( "table[id=ctl00_ContentPlaceHolder_MainBase_GridViewJobs] tr" );

foreach( $rows as $row ) {
	
	if( $row->find( "div.TableData a" ) ) {
		$jobTitleCell = $row->find( "div.TableData a" );
				
		$InfoNoteSearch = $row->find( "div.InfoNoteSearch" ); 
		$InfoNoteSearch = explode( "Source", $InfoNoteSearch[ 0 ]->plaintext );
		$InfoNoteSearch = explode( "Pay", $InfoNoteSearch[ 0 ] );
		$InfoNoteSearch = $InfoNoteSearch[ 0 ];				

		$city = $row->find( "span" );
		$city = $city[0]->plaintext;

		$date = $row->find( "td[align=right]" );
		$date = $date[0]->plaintext;

		$jobTitle 	= ucwords( strtolower( trim( $jobTitleCell[ 0 ]->plaintext ) ) );
		$jobLink 	= $jobTitleCell[ 0 ]->href;
		$employer	= ucwords( strtolower( trim( $InfoNoteSearch ) ) );
		$city 		= trim( $city );
 		$date		= trim( $date );
		$case = 0;	//0 represents a jobcenter site, if not from jobcenter, case will be 1

		if( ( substr( $jobLink, 0, 12 ) == "EnhancedJobs" ) == 1 ) {
			$jobLink = $URLfix . $jobLink;			
		}		
		else { $case = 1; }
		
		$center = new GetJobCenter( $jobLink, $case );		
		if( $case == 1 ) {
			$center->setJobTitle( $jobTitle );
			$center->setEmployer( $employer );
			$center->setCity( $city );
			$center->setDate( $date );
			
			//get redirect url of site url
			$redirect = new RedirectFollow;
			$jobLink = $redirect->follow_redirects( $jobLink ); 		
			try {
				$center->setURL( $jobLink );
			} catch( Exception $e ) {
				echo "Could not connect to host site.";
			}
		}
		
		$center->ParseSource();
	}

}
?>