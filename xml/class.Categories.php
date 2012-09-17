<?php

class Categories {
	
	static $FILENAME = "../categories.xml";
	
	public function RetrieveCategories() {
		
		/** parse categories **/
	  	$xml = new DOMDocument();
		$xml->load( "categories.xml" );
		$categories = $xml->getElementsByTagName( 'category' );
		
		//$html = "<ul class='tabs'>\n";
		$results = array();
		
		foreach( $categories as $cat ) {
			$id 		= $cat->getAttribute('id');					
			$category 	= $cat->getElementsByTagName( 'name' )->item(0)->nodeValue;				
			//$html 		= $html . "<li><a href='tab" . $id ."'>" . $category . "</a></li>\n";
			$results[ count( $results ) ] = array( $id, $category );			
		}
		//$html = $html . "</ul>\n";
		
		return $results;
		
	}
	
}

?>