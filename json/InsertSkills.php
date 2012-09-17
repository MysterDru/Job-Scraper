<?php
/*
// define API endpoint
$endpoint = 'http://api.stackoverflow.com/1.0';

// define method
$method = '/tags';

// define method params
$params = array(
  'sort' => 'featured',
  'type' => 'jsontext'
);

// connect to API and obtain results
// decode JSON response value into PHP array
$url = sprintf( '%s%s?%s', $endpoint, $method, http_build_query( $params ) );
$response = json_decode( http_inflate( file_get_contents( $url ) ) ); 
*/

/** database includes **/
require_once( "database/class.DatabaseManager.php" );

$response = json_decode( file_get_contents( 'data.json' ) );
/*
foreach( $response->tags as $t ) {

	
}
*/
?>
<html>
	<head></head>
	<body>
		<ul>
			<?php foreach( $response->tags as $t ): ?>
			<li>
				<?php 
					echo $t->name . " | " . $t->count;						
					$db->InsertSkill( $t->name ); 
				?> 
			</li>
			<?php endforeach; ?>
		</ul>
	</body>
</html>