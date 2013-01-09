<?php
// Configuration
include_once('config.inc.php');
include_once('fonctions.inc.php');

if(isset($_GET['ajaxcall']))
{
	switch($_GET['ajaxcall'])
	{
		case 'feedburner_read': feedburner_read(); break;
		case 'feedburner_update': feedburner_update(); break;
	}
}

//------------------------------------------------------------------------------------------------------

function twitter_write($msg)
{
	global $twitter_user, $twitter_pass;
	
	$out="POST http://twitter.com/statuses/update.json HTTP/1.1\r\n"
	  ."Host: twitter.com\r\n"
	  ."Authorization: Basic ".base64_encode ("$twitter_user:$twitter_pass")."\r\n"
	  ."Content-type: application/x-www-form-urlencoded\r\n"
	  ."Content-length: ".strlen ("status=$msg")."\r\n"
	  ."Connection: Close\r\n\r\n"
	  ."status=$msg";

	$fp = fsockopen ('twitter.com', 80);
	fwrite ($fp, $out);
	fclose ($fp);
}


//twitter_write('encore un test');

//include_once('api/twitter-rss.php');


function twitter_read()
{
	global $twitter_user, $num_tweets;
	
	if(isset($_COOKIE['prefs_tweets']) and is_numeric($_COOKIE['prefs_tweets']))
		$num_tweets = $_COOKIE['prefs_tweets'];
	
	$doc = new DOMDocument();
	@$doc->load("http://twitter.com/statuses/user_timeline/$twitter_user.xml?callback=twitterCallback2&count=5");
	
	$output = "<ul class=\"tweet\">";
	// Récupérer tous les tweets
	$tweets = $doc->getElementsByTagName('status');
	$loop = 0;
	foreach($tweets as $tweet)
	{
		if($loop == $num_tweets) break;
		
		// Titre
		$titles = $tweet->getElementsByTagName('text');
		$title = $titles->item(0)->nodeValue;		
				
		// Date
		$pubdates = $tweet->getElementsByTagName('created_at');
		$pubdate = $pubdates->item(0)->nodeValue;
		
		$pubdate = explode(" ",$pubdate);
		$j = $pubdate[0];
		$m = $pubdate[1];
		$d = $pubdate[2];
		$t = $pubdate[3];
		$a = $pubdate[5];
		
		$t = explode(':',$t);
		$h = $t[0];
		$i = $t[1];
		$s = $t[2];
		
		$date = "$d $m - $h"."h".$i;
		
		// Auteur
		$users = $tweet->getElementsByTagName('user');
		foreach($users as $user)
		{
			$names = $user->getElementsByTagName('name');
			$name = $names->item(0)->nodeValue;
		}
		if($name == $twitter_user)
		{			
			$xpl = explode("|",$title);
			$name = $xpl[0];
						
			$title = "";
			for($i = 1 ; $i < count($xpl) ; $i++)
				$title .= $xpl[$i].' ';
			$title = trim($title);
		}
		$output .= "<li /><span class=\"tweet_title\">$title</span><br /><span class=\"tweet_meta\">$name [$date]</span>";
		$loop++;
	}
	$output .= "</ul>";
	echo $output;
}

function feedburner_read()
{
	/*
		<item>
		<title>user: helloworld ! coeur du texte</title>
		<description>user: helloworld ! coeur du texte</description>
		<pubDate>Tue, 23 Jun 2009 12:06:57 +0000</pubDate>
		<guid>http://twitter.com/fabcomo/statuses/2293583724</guid>
		<link>http://twitter.com/fabcomo/statuses/2293583724</link>
		</item>
	*/
	
	global $twitter_user, $num_tweets;
	
	if(isset($_COOKIE['prefs_tweets']) and is_numeric($_COOKIE['prefs_tweets']))
		$num_tweets = $_COOKIE['prefs_tweets'];
	
	$doc = new DOMDocument();
	@$doc->load("http://feeds.feedburner.com/Twitter/HordesmetaWithFriends");
	
	$output = "<ul class=\"tweet\">";
	// Récupérer tous les tweets
	$tweets = $doc->getElementsByTagName('item');
	$loop = 0;
	foreach($tweets as $tweet)
	{
		if($loop == $num_tweets) break;
		
		// Titre
		$titles = $tweet->getElementsByTagName('title');
		$titre = $titles->item(0)->nodeValue;
		
		$t = explode(':',$titre);
		$user = $t[0];
		$title = "";
		for($i = 1 ; $i < count($t) ; $i++)
			$title .= $t[$i].":";
		$title = trim($title,":");
		
		// Linkify
		$title = linkify($title);
		
		// Auteur
		if($user == $twitter_user)
		{
			$t = explode("&gt;",$title);
			$user = $t[0];
			$title = "";
			for($i = 1 ; $i < count($t) ; $i++)
				$title .= $t[$i]."&gt;";
			$title = trim($title,"&gt;");			
		}
		
		// Date
		$pubdates = $tweet->getElementsByTagName('pubDate');
		$pubdate = $pubdates->item(0)->nodeValue;
		
		$pubdate = explode(" ",$pubdate);
		$j = $pubdate[0];
		$d = $pubdate[1];
		$m = $pubdate[2];
		$y = $pubdate[3];
		$t = $pubdate[4];
		$a = $pubdate[5];
		
		$t = explode(':',$t);
		$h = $t[0]+2; // feedburner 2h de décalage horaire avec Paris
		$i = $t[1];
		$s = $t[2];
		
		$date = "$d $m - $h"."h".$i;
		
		// Affichage
		$output .= "<li /><span class=\"tweet_meta\">$user&gt;</span><span class=\"tweet_title\">$title</span><br /><span class=\"tweet_meta\">$date</span>";
		$loop++;
	}
	$output .= "</ul>";
	echo $output;
}
//feedburner_read();

function linkify($str)
{
	$str = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\" target=\"_blank\">\\0</a>", $str);
	$str = preg_replace("/@(\w+)/","<a href=\"http://twitter.com/$1\" target=\"_blank\">@$1</a>",$str);
	return $str;
}
?>