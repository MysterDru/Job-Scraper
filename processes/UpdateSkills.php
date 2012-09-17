<?php
/*********************
 * Loops through jobs table and updates the skills vs jobs relationship
 * 
 *  
 */
require_once( "../database/class.DatabaseManager.php" );
require_once( "../util/class.ProcessSkills.php");


$db = new DatabaseManager;
$db->OpenDB();

$query = "SELECT ID, SourceID, Description FROM Jobs";

$jobs = mysql_query( $query );

while( $row = mysql_fetch_assoc( $jobs ) ) {
	
	$source = $row[ "SourceID" ];

	$skills = new ProcessSkills( $row[ 'Description'], $row[ "ID" ] );
}

$db->CloseDB();
?>