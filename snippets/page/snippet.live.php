<?php
	//http://api.songkick.com/api/3.0/artists/6912169/calendar.xml?apikey=376768559499526144
	
	/*
	$artist_id  =   "6912169";
    $api_key    =   "0u1ERyDnWiHBkLrR";
    $perm_link  =   "http://www.songkick.com/artists/$artist_id";

    $request_url    =   "http://api.songkick.com/api/3.0/artists/" . $artist_id . "/calendar.xml?apikey=" . $api_key;
	$xml            =   simplexml_load_file($request_url) or die("Songkick API error.  Click <a href=\"" . $perm_link . "\" target=\"_blank\">Click here for show dates</a>."); // load file, or if error, print direct link to songkick page

    echo '<table class="concerts">';
	foreach ($xml->results->event as $event) {
	   	$skdate     =   $event->start["date"];
		$uri = $event["uri"];
        $date   =   date("d-m-Y", strtotime($skdate));
        $venue      =   $event->venue["displayName"];
        $city       =   $event->location["city"];
        $artists    =   $event->xpath("./performance/artist/@displayName");

        echo '<tr>
				<td><a href="' . $uri . '" target="_blank">' . $venue . '</a></td>
				<td>' . $city . '</td>
				<td>' . $date . '</td>
			</tr>';
    }
	
	echo '</table>';
	echo '<br><a href="http://www.songkick.com/artists/6912169-little-dots" target="_blank"><img src="/css/front/img/songkicklogo.png" style="width: 70px;"/><a>';
	
	
	*/
	
	
	//AUTH
    /*
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api-read.bandpage.com/token");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode('89cabcfb4fcf21073dec76c008b113cc:8903bb9f2a2849dd6629bc54f05cc347')));
	curl_setopt($ch,CURLOPT_POSTFIELDS, 'client_id=89cabcfb4fcf21073dec76c008b113cc&grant_type=client_credentials');
	$output = curl_exec($ch);
	curl_close($ch); 
	
	$js = json_decode($output);
	$token = $js->access_token;
	

	//get concerts
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, "https://api-read.bandpage.com/356840583651733504");
	curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token));
	
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch2);
	curl_close($ch2); 
	
	var_dump($output);
	*/
	
	/*
	$credential = array(
		'clientId' => '89cabcfb4fcf21073dec76c008b113cc',
		'sharedSecret' => '8903bb9f2a2849dd6629bc54f05cc347'
	); 

	$transport = new \BandPage\BandPageAPITransportCurl;
	$bp = \BandPage\BandPageAPI::of($credential, $transport);
	 
	
	try { 
		$band = $bp->get('357527219553247232'); 
		var_dump($band);
	} catch (BandPageAPIException $e) { 
		error_log("There was an error fetching the band: $e");
	} 
	*/
?>
<br /><br />
<div class="bp-extension"></div>

<script language="javascript">
	var bandpageloaded = false;
	bandpage.load({
		"done" : function() {
			if(!bandpageloaded)
			{
				var ext = bandpage.sdk.createWidget( {
					widgetType : "show",   
					height : 800,  
					width : 600,  
					theme : "light",    
					font : "Montserrat",  
					opacity : 30,  
					bandbid : "356840583651733504",
					container : $(".bp-extension").get(0) 
				});  
				bandpageloaded = true;
			}
		},
		
		"fail" : function() {
			console.log("Failed to initialize sdk");
		}
	});
	
	if(typeof(bandpage.sdk) != "undefined")
	{
		if(!bandpageloaded)
		{
			var ext = bandpage.sdk.createWidget( {
				widgetType : "show",   
				height : 800,  
				width : 600,  
				theme : "light",    
				font : "Montserrat",  
				opacity : 30,  
				bandbid : "356840583651733504",
				container : $(".bp-extension").get(0) 
			});
			bandpageloaded = true;
		}
	}
</script>