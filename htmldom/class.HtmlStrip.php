<?php
class HTMLStrip {	
	
	function GetHTML( $url ) {
		
		$htmlString = null;
		
		// make request to indeed api to retrieve xml results base upon entered criteria
		$r = new HttpRequest( $url , HttpRequest::METH_GET );
		try {
		    $r->send();
		    if ( $r->getResponseCode() == 200 ) {
		    	
		        $htmlString = $r->getResponseBody();	
				
		    }
		} catch ( HttpException $ex ) {
		    echo $ex; 
		}
		return $htmlString;
		
	}
	
	function GetBodyText( $url ) {
		$content =	file_get_contents( $url );
		
		$dom 	= new DOMDocument();
		$html 	= $dom->loadHTML( $content );
		$dom->preserveWhiteSpace = false;
		
		$body 	= $dom->getElementsByTagName( 'body' );
		$text 	= str_replace( "'", "", str_replace( '"', '', $body->item(0)->nodeValue ) ); 
		return $text;
		
	}
	
	function GetBody( $string ) {

		$dom 	= new DOMDocument();
		$html 	= $dom->loadHTML( $string );
		$dom->preserveWhiteSpace = false;
		
		$body 	= $dom->getElementsByTagName( 'body' );
		$text 	= str_replace( "'", "", str_replace( '"', '', $body->item(0)->nodeValue ) ); 
		return $text;
		
	}


	function strip_html_tags( $text ) {
	    $text = preg_replace(
	        array(
	          // Remove invisible content
	            '@<head[^>]*?>.*?</head>@siu',
	            '@<style[^>]*?>.*?</style>@siu',
	            '@<script[^>]*?.*?</script>@siu',
	            '@<object[^>]*?.*?</object>@siu',
	            '@<embed[^>]*?.*?</embed>@siu',
	            '@<applet[^>]*?.*?</applet>@siu',
	            '@<noframes[^>]*?.*?</noframes>@siu',
	            '@<noscript[^>]*?.*?</noscript>@siu',
	            '@<noembed[^>]*?.*?</noembed>@siu',
	          // Add line breaks before and after blocks
	            '@</?((address)|(blockquote)|(center)|(del))@iu',
	            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
	            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
	            '@</?((table)|(th)|(td)|(caption))@iu',
	            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
	            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
	            '@</?((frameset)|(frame)|(iframe))@iu',
	        ),
	        array(
	            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',"$0", "$0", "$0", "$0", "$0", "$0","$0", "$0",), $text );
	  
	    // you can exclude some html tags here, in this case B and A tags        
	    return strip_tags( $text );
	}	
}
?>