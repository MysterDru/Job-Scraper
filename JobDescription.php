<!DOCTYPE html> 
<html>
	<head>
		<title>IT Sector Jobs - Job Description</title>
	</head>
	<body>
<?php
	/** database includes **/
	require_once( "database/class.DatabaseManager.php" );

	$id = $_GET[ 'JobID' ];

	$db = new DatabaseManager;
	$db->OpenDB();
	
	$job = $db->GetJobList( null, null, $id );
	
	$jobID 		= $job[ 0 ][ 0 ];
	$jobTitle 	= $job[ 0 ][ 1 ];
	$jobCat		= $job[ 0 ][ 2 ];
	$empID		= $job[ 0 ][ 3 ];
	$url		= $job[ 0 ][ 4 ];
	$latlng		= $job[ 0 ][ 5 ];
	$descrip	= $job[ 0 ][ 7 ];
?>

	<ul>
		<li>Source: <a href="<?php echo $url; ?>"><?php echo $url; ?></a></li>
		<li>Job Title: <?php echo $jobTitle; ?></li>
		<li>Employer: <?php echo $db->GetEmployer( null, $empID ) ?></li>
		<li>Location: <?php echo $latlng; ?></li>
		<li>Description: <br /> <?php echo $descrip; ?><br />
		</li>
	</ul>
	
	<?php $db->CloseDB(); ?>
	</body>
</html>