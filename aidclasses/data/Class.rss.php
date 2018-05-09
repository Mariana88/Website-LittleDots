<?php 
class RSS 
{ 
	static function start()
	{
		echo '<?xml version="1.0" encoding="ISO-8859-1" ?><rss version="2.0">
				<channel>
					<title>Toutpartout</title> 
					<link>http://toutpartout.blicsm.be</link> 
					<description></description> 
					<language>EN</language> 
					<image> 
						<title>Toutpartout Logo</title> 
						<url>http://toutpartout.blicsm.be/css/front/img/logo.jpg</url> 
						<link>http://toutpartout.blicsm.be</link> 
						<width>94</width> 
						<height>115</height> 
					</image>';
	}
	static function stop()
	{
		echo '</channel></rss>';
	}
	
	static function item($title, $link, $description, $date)
	{
		echo '<item> 
				<title><![CDATA['. $title .']]></title> 
				<link><![CDATA['. $link .']]></link> 
				<description><![CDATA['. $description .']]></description> 
				<pubDate>' . date("r", $date) . '</pubDate>
			</item>'; 
	} 
}  
?>