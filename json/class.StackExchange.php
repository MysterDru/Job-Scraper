<?php

class StackExchange {
	
	private $endpoint 	= 'http://api.stackoverflow.com/1.0';		// define API endpoint	
	// api methods	
	private $tags 		= '/tags';									// define method for tags	
	private $params 	= array();									// define method params	
	//private $response;											// response for the json request
	
	
	public function GetTags() {
		
		// decode JSON response value into PHP array
		$url = sprintf( '%s%s?%s', $this->endpoint, $this->tags, http_build_query( $this->params ) );
		$response = $this->QueryAPI( $url );
		return $response;
		
	}
	
	private function QueryAPI( $url ) {
			
		$url = sprintf( '%s%s?%s', $endpoint, $method, http_build_query( $params ) );
		return json_decode( http_inflate( file_get_contents( $url ) ) );
		
	}		
}

?>
