<?php
/** database includes **/
require_once( "database/class.DatabaseManager.php" );
require_once( 'htmldom/HtmlParser.php' );
require_once( 'htmldom/class.HtmlStrip.php' );
require_once( 'htmldom/class.FindCounty.php' );	

$db = new DatabaseManager;

$cityData = $db->GetZipcodeData();

print( "<table><tbody>");
$count = 0;
foreach( $cityData as $data ) {
	$city 	= $data[ 0 ];
	$state 	= $data[ 1 ];
	$zip 	= $data[ 2 ];
	$county = $data[ 3 ];
	
	if( $county == "" ) {
	
		//if( $count < 2 ) {	
			
			$findCounty = new FindCounty;
			$county = $findCounty->Find( $city );
			
			print( "<tr>
				<td>" . $city . "</td>
				<td>" . $county . "</td>
				<td>" . $state . "</td>
				<td>" . $zip . "</td>
				</tr>" );
			
			$db->AddCountyToZip($county, $city, $zip);	
		}
		
	$count += 1;
	//}			
}

print( "</tbody></table>");
?>