<?php

class RedirectFollow{

	function follow_redirects( $url,$timeout = 5 )
	{
	     $ch = curl_init($url);
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	     curl_setopt($ch, CURLOPT_NOBODY, true);
	     curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	     curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");
	
	     $html = curl_exec($ch);
	     $info = array();
	     if(!curl_errno($ch))
	     {
	          $info = curl_getinfo($ch);
	          return $info['url'];
	     }
	     return $url;
	}
}
?>