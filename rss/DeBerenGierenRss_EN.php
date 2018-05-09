<?php
	require_once("../systemclasses/Class.mainconfig.php");
	require_once("../systemclasses/Class.login.php");
	require_once("../systemclasses/Class.dbconnect.php");
	require_once("../frontfunc/Class.url_front.php");
	require_once("../systemclasses/Class.url.php");
	require_once("../frontfunc/Class.login_front.php");
	require_once("../frontfunc/Class.page_front.php");
	require_once("../frontfunc/Class.files_front.php");
	require_once("../aidclasses/data/Class.data_description.php");	
	require_once("../frontfunc/functions.php");
	require_once("../plugins/ezc/Base/src/base.php"); // dependent on installation method, see below
	function __autoload( $className )
	{
		ezcBase::autoload( $className );
	}
	
	$feed = new ezcFeed();
	
	$feed->title = 'De Beren Gieren Nieuws en Concerten';
	$feed->description = 'Met deze feed blijf je op de hoogte van al onze nieuwtjes en concerten!';
	$feed->published = date('r'); 
	
	$author = $feed->add( 'author' );
	$author->name = 'De Beren Gieren';
	$author->email = 'info@deberengieren.be';
	
	$link = $feed->add( 'link' );
	$link->href = 'http://www.deberengieren.be'; 
	
	//ophalen ven nieuws en concerten
	$res_main = DBConnect::query("(SELECT `id`, `date`, 'n' as `type` FROM data_news, data_news_lang WHERE data_news.id=data_news_lang.lang_parent_id AND data_news_lang.lang='EN' AND published='1') UNION (SELECT `id`, `date`, 'c' as `type` FROM data_concert, data_concert_lang WHERE data_concert.id=data_concert_lang.lang_parent_id AND data_concert_lang.lang='EN' AND published='1' AND `date` > '" . (time()-86400) . "') ORDER BY `type` DESC, `date` DESC" ,__FILE__, __LINE__);
	
	//Ophalen van al het nieuws
	
	while($row_main = mysql_fetch_array($res_main))
	{
		if($row_main["type"] == "n")
		{
			$res = DBConnect::query("SELECT * FROM data_news, data_news_lang WHERE data_news.id=data_news_lang.lang_parent_id AND data_news_lang.lang='EN' AND `id`='" . $row_main["id"] . "'", __FILE__, __LINE__);
			$row = fetch_db($res);
			$item = $feed->add( 'item' );
			$item->title = 'NIEUWS:' . utf8_encode(htmlentities($row["title"]));
			//$item->description = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '<BR>', '<BR/>', '<BR />'), chr(13), $row["text"]));
			$row["text"] = str_replace('<img class="overlay" src="/css/front/img/button_player_large.png" alt="" />', '', $row["text"]);
			$item->description = utf8_encode(htmlentities($row["text"]));
			$date = explode('.', $row["date"]);
			$date = mktime(0, 0, 0, (int)$date[1], (int)$date[0], (int)$date[2]);
			$item->published = date('r', $date); 
			
			$link = $item->add( 'link' );
			$link->href = 'http://www.deberengieren.be/EN/Home/n' . $row["id"]; 
		}
		
		if($row_main["type"] == "c")
		{
			$res = DBConnect::query("SELECT * FROM data_concert, data_concert_lang WHERE data_concert.id=data_concert_lang.lang_parent_id AND data_concert_lang.lang='EN' AND `id`='" . $row_main["id"] . "'", __FILE__, __LINE__);
			$row = fetch_db($res);
			$item = $feed->add( 'item' );
			$item->title = 'CONCERT:' . $row["date"] . ' ' . utf8_encode(htmlentities($row["title"]));
			//$item->description = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '<BR>', '<BR/>', '<BR />'), chr(13), $row["text"]));
			$html = "";
			
			if($row["usestart"] || $row["useend"] || $row["useprice"] || trim($row["adress"])!="" || trim($row["link"])!="" || trim($row["zaal_naam"])!="")
			{
				$html .= '<div style="color: #EA0A0A;">';
				$echo = false;
				if($row["price"])
						$html .= '<div class="redbg" style="float:right">&euro;' . $row["price"] . '</div>'; 
				if(trim($row["zaal_naam"])!=""){ $html .= '<b>' . $row["zaal_naam"] . '</b>'; $echo = true;}
				if(trim($row["adress"])!="")
				{ 
					if($echo) $html .= '<br/>';
					$html .= $row["adress"]; 
					$echo = true;
				}
				if($row["usestart"])
				{ 
					if($echo) $html .=  '<br/>';
					$html .=  $row["start"]; 
					$echo = true;
					if($row["useend"])
						$html .=  ' - ' . $row["end"]; 
				}
				if(trim($row["link"])!="")
				{ 
					if($echo) $html .=  '<br/>';
					$html .=  '<a href="' . $row["link"] . '" target="_blank">' . ((trim($row["link_label"])!="")?$row["link_label"]:$row["link"]) . '</a>'; 
					$echo = true;
				}
				$html .= '</div>';
			}
					
			$item->description = utf8_encode(htmlentities($html));
			$date = explode('.', $row["date"]);
			$date = mktime(0, 0, 0, (int)$date[1], (int)$date[0], (int)$date[2]);
			$item->published = date('r', $date); 
			
			$link = $item->add( 'link' );
			$link->href = 'http://www.deberengieren.be/EN/Agenda/c' . $row["id"]; 
		}
	}
	
	$xml = $feed->generate( 'rss2' );
	
	header( 'Content-Type: ' . $feed->getContentType() . '; charset=utf-8' );
	echo $xml; 
?>