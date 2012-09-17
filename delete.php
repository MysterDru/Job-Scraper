<?php
/** database includes **/
require_once( "database/class.DatabaseManager.php" );

$id = $_GET[ 'id' ];

$db = new DatabaseManager;
$db->OpenDB();

$db->delete( $id );

header('Location: ' . $_SERVER['HTTP_REFERER'] );
?>