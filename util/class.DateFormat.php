<?php

class FormatDate {
	
	
	public function YYYYMMDD( $dateArray, $pattern ) {
		$YYYY; $DD; $MM;
		$tempDate = $dateArray[ 1 ];
		$tempDate = explode( " ", $tempDate );
		
		if( $patter == "/" ) {
			
		}
		if( $pattern == "," ) {
			$YYYY = $tempDate[2]; $DD = $tempDate[0];
		}
		
		switch( $tempDate[ 1 ] ) {
			case "Jan" 		: $MM = 01; break;
			case "Feb"		: $MM = 02; break;
			case "Mar" 		: $MM = 03; break;
			case "April" 	: $MM = 04; break;
			case "May" 		: $MM = 05; break;
			case "Jun" 		: $MM = 06; break;
			case "Jul" 		: $MM = 07; break;
			case "Aug" 		: $MM = 08; break;
			case "Sep" 		: $MM = 09; break;
			case "Oct" 		: $MM = 10; break;
			case "Nov" 		: $MM = 11; break;
			case "Dec" 		: $MM = 12; break;
			default 		: $MM = 00; break;
		}
		
		switch( $tempDate[ 1 ] ) {
			case "Jan" 		: $MM = 01; break;
			case "Feb"		: $MM = 02; break;
			case "Mar" 		: $MM = 03; break;
			case "April" 	: $MM = 04; break;
			case "May" 		: $MM = 05; break;
			case "Jun" 		: $MM = 06; break;
			case "Jul" 		: $MM = 07; break;
			case "Aug" 		: $MM = 08; break;
			case "Sep" 		: $MM = 09; break;
			case "Oct" 		: $MM = 10; break;
			case "Nov" 		: $MM = 11; break;
			case "Dec" 		: $MM = 12; break;
			default 		: $MM = 00; break;
		}		
		return $YYYY . "-" . $MM . "-" . $DD . " " . $tempDate[ 3 ];
	}
	
}

?>