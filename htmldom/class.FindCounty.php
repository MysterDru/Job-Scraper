<?php
class FindCounty {
	private $county;
	private $html;
	private $queryString;
	
	private $foundState = false;
	
	public function Find( $city ) {
		
		$cityQ = str_replace( " ", "+", $city );
		
		$this->queryString = "http://www.uscounties.org/cffiles_web/counties/city_res.cfm?city=" . $cityQ;
		
		$this->html	= file_get_html( $this->queryString );
		
		$this->ProcessCounties();
		return $this->county;
}
	
	private function ProcessCounties() {
		$count = 0;
		foreach( $this->html->find( 'td') as $element ) {
			 
			if( $element->plaintext == "WI" ) {
				$this->foundState = true;
			}
			if( $this->foundState == true ) { $count += 1; }
			if( $this->foundState == true && $count == 4 ) {
				$tempC = explode( " ", $element->plaintext );
				$this->county = $tempC[ 0 ]; 
			}
		}		
	}
	
}
?>