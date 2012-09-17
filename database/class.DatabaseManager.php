<?php

class DatabaseManager {
	
	private $DATABASE_HOST		= "localhost";
	private $DATABASE_NAME		= "drewfri9_jobsscrape";	
	private $DATABASE_USERNAME	= "drewfri9_jobs";	
	private $DATABASE_PASSWORD	= "jobsMIT2011";
	
	private $connection;
	
	/** table names **/
	private $T_County		= "Counties";				// counties
	private $T_Duration		= "Duration";				// duration (full time, part time, etc)
	private $T_Education	= "Education";				// education level (high school, college, etc)
	private $T_Employers	= "Employers";				// complied employers
	private $T_Jobs			= "Jobs";					// compiled jobs
	private $T_JobCats		= "JobCategories";			// job titles/categories	
	//private $T_Skills		= "Skills";					// list of skills, retrieve from stack overflow
	private $T_Skills		= "SkillsList";				// skills l
	private $T_SkillCat		= "SkillCategories";		// skill categories, i.e stack overflow, midstate, etc
	private $T_Jobs_Skills	= "JobsVsSkills";			// job id vs skill id
	private $T_Zipcodes		= "WIZipcodes";				// zipcode list
	private $T_SourceBoard	= "JobSourceBoard";			// job board source website (indeed, midstate, etc)
	
	public $lastJobInsertID;						// id of the last job insert query
	
	function __construct() {
		/** TO-DO **/
		// will open the connection to the database
		
		//$this->OpenDB();		
	}
	
	public function OpenDB() {
		
		$this->connection = mysql_connect( $this->DATABASE_HOST, $this->DATABASE_USERNAME, $this->DATABASE_PASSWORD );
		mysql_select_db( $this->DATABASE_NAME, $this->connection ) or die ( "<p class='error'>Sorry, we were unable to connect to the database.</p>" );
		
	}

	public function CloseDB() {		
		// close the connection to the database
		mysql_close( $this->connection );
	}

	public function insert( $source, $jobtitle, $date, $employer, $catID, $id, $desc, $pay, $duration, $education, $latlong, $county, $city, $state, $zipcode, $country, $postURL, $licensing ) {
		$returnValue = null; $employerID;
		$job 			= /*addslashes( $jobtitle );/*/$this->CleanString( $jobtitle );
		$employer 		= /*addslashes( $employer );/*/$this->CleanString( $employer );
		
		//print( "<br /><strong>EMPLOYER NAME: </strong>" . $employer . "<br />" );
				
		if( $employer == "" ) {
			$employerID	= 0; // set employerID to be 0 so it won't represent a value in the employer table.
		}
		else if( ( $employerID = $this->GetEmployer( $employer ) )  == null ) {
			$this->CreateEmployer( $employer );
			$employerID = $this->GetEmployer( $employer );
		}

		$educationID 	= $this->GetEducation( $education ); 
		$countyID       = $this->GetCounty( $county );
		$durationID		= $this->GetDuration( $duration );
		$sourceID		= $this->GetSource( null, trim( $source ) );
		//escape special characters in description
		$desc = mysql_real_escape_string( $desc );
		
		$checkQuery 	= "SELECT JobTitle, EmployerID, City FROM $this->T_Jobs WHERE JobTitle = '$job' AND EmployerID = $employerID AND City = '$city'";		
		//print( "<br /><strong>Check Query:</strong>" . $checkQuery . "<br />");
		$result 		= mysql_query( $checkQuery ) or die( mysql_error() );
		if( mysql_num_rows( $result ) ) {
			//print( "Job exists in database, skipping index of job.");
			$returnValue = "false";
		}	
		else {
			
			$insertQuery = mysql_query( "INSERT INTO " . $this->T_Jobs . "
			 	(SourceID, JobTitle, Date_Posted, EmployerID, JobCategoryID, InternalID, Description, Pay, DurationID, EducationID, Licensing, LatLong, CountyID, City, State, ZipCode, Country, PostURL) 
			 	VALUES ('$sourceID','$job','$date','$employerID','$catID','$id','$desc','$pay','$duration','$education','$licensing','$latlong','$countyID','$city','$state','$zipcode','$country','$postURL')");
			
			if( $insertQuery ) {
				$this->lastJobInsertID = mysql_insert_id();			
				//print( ' | Job added to database with id: ' . mysql_insert_id() );
				$returnValue = "true";
			}			
			else {
				print( ' | Error: ' . mysql_error() );
				$returnValue = "false";
			}			
		}
		return $returnValue;
	}

	public function delete( $jobID ) {
		$q = "DELETE $this->T_Jobs,$this->T_Jobs_Skills FROM $this->T_Jobs LEFT JOIN $this->T_Jobs_Skills ON $this->T_Jobs.ID = $this->T_Jobs_Skills.JobID WHERE $this->T_Jobs.ID = $jobID";
		//echo $q;		 
		$query = mysql_query( $q );
		
		if( !$query ) {
			print( ' | Error: ' . mysql_error() ); 
			die();
		}
		else { echo mysql_info(); }
	}
	
	public function InsertSkill( $name ) {
			
		$insertQuery	= mysql_query( "INSERT INTO " . $this->T_Skills . "(SkillName) VALUES ('$name')" );
		if( !$insertQuery )
			print( ' | Error: ' . mysql_error() );			
		else
			print( ' | Skill added to database' );
	}	
	
	public function InsertJobSkill( $jobID, $skillID ) {
		//print( 'inserting job skill ' . $skillID. " " );
		$insertQuery	= mysql_query( "INSERT INTO " . $this->T_Jobs_Skills . "(SkillID, JobID) VALUES ('$skillID','$jobID')" );
		if( !$insertQuery )
			print( ' | Error: ' . mysql_error() );
			
	}
	
	public function GetJobCategories( $id = null, $title = null ) {
		$filter = ""; // filter is defaulted to null, will return all
		
		if( $id == null && $title == null ) {
			$filter = "";
		}		
		elseif( $id == null || $title == null ) {
			if( $id != null ) { // build filter from id values
				$filter = " WHERE ";
				
				for( $i = 0; $i < count( $id ); $i++ ) {
					$filter = $filter . "JobCategoryID = " . $id[ $i ];
					if( ( count( $id ) > 1 ) && ( count( $id ) != ( $i + 1 ) ) ) $filter = $filter . " OR ";					
				}
			}
			if( $title != null ) {
				$filter = " WHERE ";
				for( $t = 0; $t < count( $title ); $t++ ) {
					$filter = $filter . "JobCategory = '" . $title[ $t ] . "' ";
					if( ( count( $title ) > 1 ) && ( count( $title ) != ( $t + 1 ) ) ) $filter = $filter . " OR ";					
				}
			}
		}		
		$q ="SELECT * FROM " . $this->T_JobCats . $filter;
//		print( $q . "<br />" );
		//print( $filter );
		// will return categories
		$query 	= mysql_query( $q );
		
		$title_array = array();		// store list of all returned titles
		if( $query ) {
		while( $row = mysql_fetch_assoc( $query ) ) {
			array_push( $title_array, array( $row[ "JobCategoryID" ], $row[ "JobCategory" ] ) );
		}

		return $title_array;
		}
		else {
			return "hello";
		}
	}	
	
	/*** Get Skills List ***
	 * If params are left null, will return an array of all skill names
	 * 
	 * @params
	 *  cat(optional): the id of the skill category to query 0 	 
	 * 	name(optional): an array of the skill name(s) to return from the db
	 * 	id(optional): 	and array of the skill id(s) to return from the db 
	 */
	public function GetSkillsList( $name = null, $id = null/*, $cat = null*/, $all = false ) {
		$filter; $returnValue = null;

		if( $name == null && $id == null && $cat == null && $all == false ) {
			return $returnValue;
		}
		elseif( $all == true ) {
			$filter = " WHERE ParentID != 0";
		}
		elseif( $name != null && $id == null && $cat == null && $all == false ) {
			return $returnValue;			
		}
		elseif( $name == null && $id != null && $cat == null && $all == false ) {
			$filter = " WHERE ";
			for( $i = 0; $i < count( $id ); $i++ ) {
				$filter = $filter . "SkillID = " . $id[ $i ];
				if( ( count( $id ) > 1 ) && ( count( $id ) != ( $i + 1 ) ) ) $filter = $filter . " OR ";					
			}						
		}/*		
		elseif( $name == null && $id == null && $cat != null && $all == false ) {
			if( $cat == 0 )	
				$filter = ""; // return all results from skills table if 0 is passed as the category id	
			else
				$filter = " WHERE CategoryID = $cat";
		}*/
		
		$q = "SELECT * FROM " . $this->T_Skills . $filter;
		//echo $q . "<br />";
		$query = mysql_query( $q );
		if( $query ) {
			$returnValue = array();
			while( $row = mysql_fetch_assoc( $query ) ) {
				array_push( $returnValue, array( $row[ "SkillName" ], $row[ "SkillID" ] ) );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;
	}
	
	public function GetSkillFamily( $parentID = 0, $getChild = false ) {
		$returnValue = null;
		
		$filter = " WHERE ParentID = $parentID";
		$q = "SELECT * FROM " . $this->T_Skills . $filter;
		$query = mysql_query( $q );
		if( $query ) {
			$returnValue = array();
			while( $row = mysql_fetch_assoc( $query ) ) {
				array_push( $returnValue, array( $row[ "SkillName" ], $row[ "SkillID" ] ) );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;
	}
	
	public function GetJobSkills( $jobID = null, $skillID = null ) {
		$filter; $returnValue = null; $rowValue = "SkillID";
		if( $jobID != null ) {
			$filter 	= " WHERE JobID = $jobID"; 
			$rowValue 	= "SkillID";
		}
		elseif( $skillID != null ) {
			$filter 	= " WHERE SkillID = $skillID";
			$rowValue	= "JobID";
		}
		$q 		= "SELECT SkillID, JobID FROM " . $this->T_Jobs_Skills . $filter;
		//print( "<br />query " . $q );
		$query 	= mysql_query( $q );
		if( $query ) {
			$returnValue = array();
			while( $row = mysql_fetch_assoc( $query ) ) {
				array_push( $returnValue, $row[ $rowValue ] );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		
		return $returnValue;
	}
	
	public function GetJobList( $employer = null, $jobCategory = null, $jobID = null, $boardID = null, $countyID = null ) {
		$filter; $returnValue = null;
		
		if( $employer != null && $jobCategory == null && $jobID == null && $boardID == null && $countyID == null ) {
			$eID = $this->GetEmployer( $employer );
			$filter = " WHERE EmployerID = " . $eID;
		}
		elseif( $employer == null && $jobCategory != null && $jobID == null && $boardID == null && $countyID == null ) {			
			$filter = " WHERE JobCategoryID = $jobCategory";
		}
		elseif( $employer == null && $jobCategory == null && $jobID != null && $boardID == null && $countyID == null) {
			$filter = " WHERE ID = $jobID";
		}
		elseif( $employer == null && $jobCategory == null && $jobID == null && $boardID != null && $countyID == null) {
			$filter = " WHERE SourceID = $boardID";
		}
		elseif( $employer == null && $jobCategory == null && $jobID == null && $boardID == null && $countyID != null ) {
			$filter = " WHERE CountyID = $countyID";
		} 	
		
		$q = "SELECT ID, JobTitle, Date_Posted, JobCategoryID, EmployerID, CountyID, SourceID, PostURL, LatLong, Description FROM " . $this->T_Jobs . $filter;
		//print( $q );
		$query = mysql_query( $q );		
		if( $query ) {
			$returnValue = array();
			while( $row = mysql_fetch_assoc( $query ) ) {
				array_push( $returnValue, array( $row[ "ID" ], $row[ "JobTitle" ], $row[ "JobCategoryID" ], $row[ "EmployerID" ], $row[ "PostURL" ], $row[ "LatLong" ], $row[ "SourceID" ], $row[ "Description"], $row[ "Date_Posted"] ) );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;
	}
	
	public function GetCityData( $city ) {
		$returnValue = null;
		
		$q = "SELECT * FROM " . $this->T_Zipcodes . " WHERE city = '" . $city . "' LIMIT 1";
		//print( "<br />".$q."<br />" );
		$query = mysql_query( $q );
		
		if ( $query ) {			
			while ( $row = mysql_fetch_assoc( $query ) ) {
				$returnValue = array( $row[ "city" ], $row[ "state" ], $row[ "county" ], $row[ "zip" ], $row[ "latitude" ], $row[ "longitude" ] );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;
	}
	
	private function CheckEntries( $jobtitle, $employerID, $zipcode ) {
		$returnValue = false;
		
		$getQuery 	= mysql_query( "SELECT * FROM " . $this->T_Jobs . "WHERE JobTitle = " . $jobtitle . " AND EmployerID = " . $employerID . " AND ZipCode = " . $zipcode );
		if( !empty( $getQuery ) )
			$returnValue = true;
		return $returnValue;
	}
	
	private function CreateEmployer( $employer ) {
		$returnValue = null;
		$emp = $this->CleanString( $employer );
		$insertQuery	= mysql_query( "INSERT INTO " . $this->T_Employers . "(EmployerName) VALUES ('$emp')" );
		if( !$insertQuery ) print( "Error: " . mysql_error() . "\n");
		else /*print( "Employer: " . $employer . " added to database\n" )*/;
	}
	
	public function GetEmployer( $employer = null, $id = null ) {
		$filter; $returnValue = null; $rowValue;
		
		if( $employer == null && $id == null ) { return $returnValue; }
		elseif( $employer != null && $id != null ) { return $returnValue; }
		elseif( $id != null ) {
			$filter = " WHERE EmployerID = $id";
			$rowValue = "EmployerName";
		}
		elseif( $employer != null ) {
			$filter = " WHERE EmployerName LIKE '%$employer%'";
			$rowValue = "EmployerID";	
		}
		$q = "SELECT * FROM $this->T_Employers" . $filter;
		//print( "<br /><strong>Get Employer Q:</strong> " . $q . "<br />");
		$getQuery 	= mysql_query( $q );
		if( $getQuery ) {
			$row		= mysql_fetch_assoc( $getQuery );
			$returnValue = $row[ $rowValue ];
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;
		
	}
	
	private function GetEducation( $education = null ) {
		$filter; $returnValue = null;
		
		if( $education != null ) 
			$filter = " WHERE Education = " . $education;
			
		$getQuery	= mysql_query( "SELECT * FROM " . $this->T_Education . $fiter );
		if( $getQuery ) {
			$row		= mysql_fetch_assoc( $getQuery );
			$returnValue = $row[ "EducationID" ];
		}
		
		return $returnValue;
	}
	
	public function GetCounty( $county = null ) {
		$filter = ""; $returnValue = null;
		if( $county != null )
			$filter = " WHERE CountyName = '" . $county . "'";			
		$getQuery 	= mysql_query( "SELECT * FROM " . $this->T_County . $filter );
		if( $getQuery ) {
			
			if( $county == null ) {
				$returnArray = array();
				while( $row = mysql_fetch_assoc( $getQuery ) ) {						
					array_push( $returnArray, array( $row[ "CountyID" ], $row[ "CountyName" ] ) );						
				}	
				$returnValue = $returnArray;
			}
			else {
				$row		= mysql_fetch_assoc( $getQuery );
				$returnValue = $row[ "CountyID" ];
			}						
			
		}
		
		return $returnValue;
	}
	
	private function GetDuration( $duration = null ) {
		$filter; $returnValue = null;
		
		if( $duration != null )
			$filter = " WHERE Duration = " . $duration;
			
		$getQuery 	= mysql_query( "SELECT * FROM " . $this->T_Duration . $fiter );
		if( $getQuery ) {
			$row		= mysql_fetch_assoc( $getQuery );
			$returnValue = $row[ "DurationID" ];
		}
		
		return $returnValue;		
	}	
	
	public function GetZipcodeData() {
		$returnValue = null;
		
		$q = "SELECT * FROM " . $this->T_Zipcodes;
		
		$getQuery 	= mysql_query( $q );
		if( $getQuery ) {			
			$returnValue = array();
			while( $row = mysql_fetch_assoc( $getQuery ) ) {
				array_push( $returnValue, array( $row[ "city" ], $row[ "state"], $row[ "zip" ], $row[ "county" ] ) );
			}
		}
		else { print( "Error: " . mysql_error() . "<br/>"); }
		return $returnValue;		
		
	}

	public function AddCountyToZip( $county, $city, $zip ) {
		
		$q = "UPDATE  " . $this->T_Zipcodes . " SET  county =  '" . $county . "' WHERE " . $this->T_Zipecodes . ".zip = " . $zip . " AND  " . $this->T_Zipecodes . ".city =  '" . $city . "'";
		//print( $q . "<br />");
		$updateQ = mysql_query( $q );
		if( !$updateQ ) {
			print( "Error: " . mysql_error() . "<br/>");
		}
				
	}

	public function GetSource( $sourceID = null, $sourceName = null ) {
		$returnValue = ""; $returnParam; $q;
		if( $sourceID != null ) {
			$q = "SELECT * FROM $this->T_SourceBoard WHERE ID = $sourceID";
			$returnParam = "SourceName";
		}
		
		if( $sourceName != null ) {
			$q = "SELECT * FROM $this->T_SourceBoard WHERE SourceName = '$sourceName'";
			$returnParam = "ID";
		}

		$query = mysql_query( $q );
		if( $query ) {
			while( $row = mysql_fetch_assoc( $query ) ) {
				$returnValue = $row[ $returnParam ];
			}
		}
		
		return $returnValue;
	}

	private function CleanString( $string ) {		
		$rString = str_replace( "'", "", $string );
		//$rString = mysql_real_escape_string( $string );
		//$rString = addslashes( $string );
				
		return trim( $rString );
	}
}


?>