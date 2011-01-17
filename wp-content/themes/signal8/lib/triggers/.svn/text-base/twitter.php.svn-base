<?php

function get_latest_tweets($username, $limit=5) {
    $rss_url = "http://search.twitter.com/search.atom?q=from:$username";
    $data = @file_get_contents($rss_url);
    $doc = @DomDocument::loadXML($data);
    
    if(empty($doc)) {
    	return array('tweet_link'=>'#', 'tweet_text'=>'Cannot connect to twitter');
	}
	
    $entries = $doc->getElementsByTagName('entry');
    $res = array();
    $loop_counter = 1;
    foreach ($entries as $entry) {
        $tweet = new StdClass();
        $tweet->tweet_text = $entry->getElementsByTagName('content')->item(0)->nodeValue;
        $tweet->tweet_link = $entry->getElementsByTagName('link')->item(0)->getAttribute('href');
       
        $res[] = $tweet;
        if ($loop_counter==$limit) {
            break;
        }
        $loop_counter++;
    }
    if (count($res)==1 && $limit==1) {
        return $res[0];
    }
    return $res;
}
?>