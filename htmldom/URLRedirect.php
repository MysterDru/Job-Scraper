<?php

class URLRedirect {

	/**
	 * get_redirect_url()
	 * Gets the address that the provided URL redirects to,
	 * or FALSE if there's no redirect. 
	 *
	 * @param string $url
	 * @return string
	 */
	function get_redirect_url( $url ){
		//echo"<br />get_redirect: " . $url . "<br />";
		$redirect_url = null; 
//print( "<br />STEP 1<br />");	 
		$url_parts = @parse_url( $url );
		if ( !$url_parts ) return false;
//print( "<br />STEP 2a<br />");		
		if ( !isset($url_parts['host'] ) ) return false; //can't process relative URLs
//print( "<br />STEP 2b<br />");		
		if ( !isset($url_parts['path'] ) ) $url_parts[ 'path' ] = '/';
//print( "<br />STEP 2c<br />");	 
		$sock = fsockopen( $url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30 );
//print( "<br />STEP 2d<br />");
print( "<br/><strong>sock: " . $sock . "</strong>");		
		if ( !$sock ) return false;
//print( "<br />STEP 3<br />");	 
		$request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
		$request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
		$request .= "Connection: Close\r\n\r\n"; 
		fwrite($sock, $request);
		$response = '';
		while(!feof($sock)) $response .= fread($sock, 8192);
		fclose($sock);
//print( "<br />STEP 4<br />");	 
		if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
			if ( substr($matches[1], 0, 1) == "/" )
				return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
			else
				return trim($matches[1]);
	 
		} else {
			return false;
		}
	 
	}
	 
	/**
	 * get_all_redirects()
	 * Follows and collects all redirects, in order, for the given URL. 
	 *
	 * @param string $url
	 * @return array
	 */
	function get_all_redirects( $url ){
		$redirects = array();
		while ( $newurl = $this->get_redirect_url( $url ) ){						
			if ( in_array( $newurl, $redirects ) ){
				break;
			}
			//print( "<strong> --hello world: " . $newurl . "-- </strong>");
			print( "<br />newurl: " . $newurl . "<br />" );
			$redirects[] = $newurl;
			$url = $newurl;
		}
		return $redirects;
	}
	 
	/**
	 * get_final_url()
	 * Gets the address that the URL ultimately leads to. 
	 * Returns $url itself if it isn't a redirect.
	 *
	 * @param string $url
	 * @return string
	 */
	function get_final_url( $url ){
		$redirects = $this->get_all_redirects( $url );
		//print( "redirect count:" . count( $redirects ) );
		if ( count( $redirects ) > 0 ){
			return array_pop( $redirects );
		} else {
			return $url;
		}
	}
	
}
?>