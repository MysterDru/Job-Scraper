<?php
	/** database includes **/
	require_once( "database/class.DatabaseManager.php" );
	
	$db = new DatabaseManager;
	$db->OpenDB();
	$skills = $db->GetSkillsList( null, null, true);
	
	echo count( $skills );
?>