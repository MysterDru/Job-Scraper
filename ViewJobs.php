<!DOCTYPE html> 
<html>
	<head>
		<title>IT Sector Jobs - View Jobs Database</title>
		<link href="assets/main.css" rel="stylesheet" type="text/css"/>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<style type="text/css">
		  html { height: 100% }
		  body { height: 100%; margin: 0px; padding: 0px }
		  #map_canvas { height: 400px; width: 500px; float:right; }
		</style>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>	
	</head>
<?php
	/** database includes **/
	require_once( "database/class.DatabaseManager.php" );
	/** html dom includes **/
	require_once( 'htmldom/HtmlParser.php' );
	require_once( 'htmldom/class.HtmlStrip.php' );
	require_once( 'htmldom/URLRedirect.php' );	
	require_once( 'htmldom/class.FindCounty.php' );	
	/** xml includes **/
	require_once( "xml/class.IndeedParser.php" );

	$db = new DatabaseManager;
	$db->OpenDB();
?>
	<body><div id="pageContent">
		
		<div id="map_canvas" style="width:500px; height:400px"></div>		
		<h2><a href="index.php">IT Sector Jobs</a> - Jobs Database</h2>
		<a href="ViewJobs.php">Full Database</a> | <a href="ViewJobs.php?boardID=1">Indeed Database</a> | <a href="ViewJobs.php?boardID=2">Midstate Database</a> | <a href="ViewJobs.php?boardID=3">CareerBuilder Database</a>  | <a href="ViewJobs.php?boardID=4">WI Job Center Database</a>
		<p>County:<br />
<?php
		$county = $db->GetCounty();
		foreach( $county as $c ) {
			print( "| <a href='ViewJobs.php?county=" . $c[ 0 ] . "'>" . $c[ 1 ] . "</a> ");
		}
?>
		</p>
		<p>Skill Categories:<br />		
<?php
		$skillParents = $db->GetSkillFamily();
		foreach( $skillParents as $parent ) {
			print( " | <a href='ViewJobs.php?skillParent=" . $parent[ 1 ] . "'>" . $parent[ 0 ] . "</a> ");
		}			
?>			
		</p>
<?php
		// grab query values from url
		$employer 	= $_GET["company"];		// string value of company name
		$cat		= $_GET["category"];	// id of corresponding job category
		$skill		= $_GET["skill"];		// id of corresponding skill name
		$jobBoard	= $_GET["boardID"];		// id of corresponding job board
		$countyID	= $_GET["county"];		// id of corresponding county id
		$skillPar	= $_GET["skillParent"];	// id of a parent skill id
		$jobs;
		
		if( $employer ) {
			$jobs = $db->GetJobList( $employer );
		}
		elseif ( $cat ) {
			$jobs = $db->GetJobList( null, $cat );
		}
		elseif( $skill ) {
			$s 		= $db->GetJobSkills( null, $skill );
			$jobs	= array();
			foreach( $s as $j ) {				
				$job = $db->GetJobList( null, null, $j/* jobID */ );
				array_push( $jobs, $job[0] );
			}
		}
		elseif ( $jobBoard ) {
			$jobs = $db->GetJobList( null, null, null, $jobBoard/*job board id*/ );
		}
		elseif ( $countyID ) {
			$jobs = $db->GetJobList( null, null, null, null, $countyID/*county id*/ );
		}
		elseif( $skillPar ) {
			$skillList = $db->GetSkillFamily( $skillPar, $true );
			$jobs	   = array();
			foreach( $skillList as $skill ) {
				$skl   = $db->GetJobSkills( null, $skill[ 1 ] );
				foreach( $skl as $jb ) {
					$job = $db->GetJobList( null, null, $jb );
					array_push( $jobs, $job[ 0 ] );
				}
			}
			
		}
		else {
			$jobs = $db->GetJobList();
		}
?>		
		<p style="clear:both;">Job Count Displayed: <?php echo count( $jobs ); ?></p>
		<table id="skills" border="1" style="clear:both">
			<tr>
				<td class="jobID">Job ID (database)</td><td>Date Posted</td><td class="jobCompany">Company</td><td class="jobCat">Job Category</td><td class="jobTitle">Job Name &amp; URL</td><td class="jobSkills">Skills</td><td><!--DELETE--></td>
			</tr>
<?php
			// variables for print out map javascript
			// use only if category value is not null
			$scriptPrint;
			$latLngList		= array();
			$locationCount 	= array();			
			$count = 0;	
			
			$categories = $db->GetJobCategories();
			
			foreach( $jobs as $job ) {
				$jID = $job[ 0 ]; $jTitle = $job[ 1 ]; $jCat = $job[ 2 ]; $eID = $job[ 3 ]; $url = $job[ 4 ];
				$latLng = $job[ 5 ]; $jDate = $job[ 8 ];									
				//$skillList = $db->GetJobSkills( $jID );
				
				$skills = $db->GetSkillsList( null, $db->GetJobSkills( $jID ), false );
				print("<tr>");
				$emp 		= $db->GetEmployer( null, $eID ); // employee reference, store locally so query is only ran once
				$category 	= $categories[ $jCat - 1 ][ 1 ]; // job category reference, store locally so query is only ran once 	
				print("<td class='jobID'>" . $jID . "</td>
						<td class='datePosted'>" . $jDate . "</td>
						<td class='jobCompany'><a href='ViewJobs.php?company=" . $emp . "'>" . $emp . "</a></td>	
						<td class='jobCat'><a href='ViewJobs.php?category=" . $jCat . "'>" . $category . "</td>						
						<td class='jobTitle'><a href='" . /*$url*/ "JobDescription.php?JobID=" . $jID . "' target='_blank'>" . $jTitle . "</a></td>
						<td lass='jobSkills'>");
				
				if( $skills != "" ) {
					foreach( $skills as $skill ) : print( "<a href='ViewJobs.php?skill=" . $skill[1] . "'>" . $skill[0] . "</a>, " ); endforeach;
				}
				else { echo "Job does not have skills listed"; }
					
				print("</td>
						<td><input value='Delete' type='button' onclick='DeleteJob(" . $jID . ")' /></td>
					</tr>");	
				
				$key = array_search( $latLng, $latLngList );
				if( !$key ) 
					$locationCount [ array_push( $latLngList, $latLng ) - 1 ] = 1;
					
				else {
					$locationCount[ $key ] += 1;
				}
				
				

/*				$tempString = '
				  	var point' . $jID . '= new google.maps.LatLng(' . $latLng . ');
				  	var marker' . $jID . '= new google.maps.Marker( { position: point'. $jID . ', title:"' . /*$jTitle . / '" } );
			  		marker' . $jID . '.setMap( map );';
			  	$scriptPrint = $scriptPrint . $tempString;
*/
			}	
			for( $i = 0; $i < count( $latLngList ); $i++ ) {
				$marker = "red.png";
				$count = $locationCount[ $i ];
					if( 10 > $count && $count < 0 )
						$marker = "green.png";
					else if( 20 > $count && $count < 10 )
						$marker = "orange.png";
					else if( 30 > $count && $count < 20 )
						$marker = "purple.png";
					else if( 40 > $count && $count < 30 )
						$marker = "off-green.png";
					else if( $count > 40 )
						$marker = "blue.png";

				$tempString = '
				  	var point' . $i . '= new google.maps.LatLng(' . $latLngList[ $i ] . ');
				  	var marker' . $i . '= new google.maps.Marker( { position: point'. $i . ', title:"' . $count . ' jobs in this city"} );
			  		marker' . $i . '.setMap( map );';
				$scriptPrint = $scriptPrint . $tempString;					
			}			
			$db->CloseDB();		
?>
		</table>
		<script type="text/javascript">	
			function addMarkers() {
				var latlng = new google.maps.LatLng( 44.525885,-90.074158 );
				var myOptions = {
			  		zoom: 7,
			  		center: latlng,
			  		mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var map = new google.maps.Map( document.getElementById("map_canvas"), myOptions );
			
			<?php echo $scriptPrint; ?>
			}				
			window.onload = addMarkers;
			
			/***** Delete Job from Database ******/
			function DeleteJob( id ) {
				var deleteJob = confirm( "Are you sure you wish to delete this job?" );
				if( deleteJob ) {
					window.location = 'delete.php?id=' + id;
				}
			}
		</script>
	</div></body>
</html>