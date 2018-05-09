<?php
	require 'plugins/eigen/facebook/facebook.php';
	$_REQUEST[$raw_facebook] = new Facebook(array(
		'appId' => '139426504646',
		'secret' => 'ebca8093bc1cde6abc6e091b14124984'));
	
	class blicsmFB
	{	
		public $fb;
		public $posts;
		public $count;
		public $feedwidth;
		public $maxMessageLength;
		public $enkelMsgVan; //ids van mensen/pagina's
		
		function __construct() {
 			$this->fb = $_REQUEST[$raw_facebook];
			$this->count = 5;
			$this->feedwidth = 300;
			$this->feedMessageLength = 300;
   		}
		
		public function get_posts($count)
		{
			$this->count = $count;
			$fbApiGetPosts = $this->fb->api('/136077734894/feed?limit=' . $this->count);
			if (isset($fbApiGetPosts["data"]) && !empty($fbApiGetPosts["data"]))
			{
				$this->posts = $fbApiGetPosts["data"];
			}
		}
		
		public function display_posts($count = NULL)
		{
			if($count)
				$this->count = $count;
			if(!$this->posts)
				$this->get_posts($this->count);
			if(!$this->posts)
				return;
				
			foreach($this->posts as $post)
			{
				$this->	print_post($post);
			}
			
			var_dump($this->posts);
		}
		
		public function print_post($post)
		{
			if(is_array($this->enkelMsgVan) != "" && !in_array($post["from"]["id"], $this->enkelMsgVan))
				return;
			echo '<div class="fb_post">
					<div class="fb_header">' . date('d.m.Y H:i', strtotime($post["created_time"])) . ' ' . $post["type"] . ' ' . $post["from"]["name"] . '</div>';
			
			switch($post["type"])
			{
				case 'link': $this->print_message_text($post["message"]);
								//the link
								echo '<a class="fb_link_link" href="' . $post["link"] . '">
										<div class="fb_link">';
								if(trim($post["picture"]) != '')
									echo '<img class="fb_link_pic" src="' . $post["picture"] . '"/>';
								echo '<p class="fb_link_name">' . $post["name"] . '</p>
											<p class="fb_link_caption">' . $post["caption"] . '</p>
											<p class="fb_link_description">' . $post["description"] . '</p>
										</div>
									</a>';
					break;
				case 'video': $this->print_message_text($post["message"]);
						$vidid = files_front::get_video_id($post["link"]);
						echo '<div class="fb_vid">
								<iframe width="' . $this->feedwidth . '" height="' . ((int)(($this->feedwidth/16)*9)) . '" src="http://www.youtube.com/embed/' . $vidid . '?autoplay=0" frameborder="0" allowfullscreen wmode="Transparent"></iframe>
								</div>';
					break;
				case 'photo': $this->print_message_text($post["message"]);
					//picture, zoeken naar original
					$tmp = explode('.', $post["picture"]);
					$tmp[count($tmp) - 2] = substr($tmp[count($tmp) - 2], 0, strlen($tmp[count($tmp) - 2]) - 2) . '_o';
					$post["picture"] = implode('.', $tmp);
					echo '<a href="' . $post["link"] . '" target="_blank"><img class="fb_photo" style="max-width: ' . $this->feedwidth . 'px;" src="' . $post["picture"] . '"/></a>';
					break;
				default: $this->print_message_text($post["message"]);
					break;
			}
			
			echo '</div>';
			
			//js
			?>
            <script language="javascript">
            	$('.fb_message_readmore').click(function(){
					$(this).parent().find(".fb_message_short").hide();
					$(this).hide();
					$(this).parent().find(".fb_message_original").show();
				});
            </script>
            <?php
		}
		
		public function print_message_text($message)
		{
			//linken vervangen in de message
			if($this->feedMessageLength > 0 && ($this->feedMessageLength+50) < strlen($message))
			{
				$message_small = substr($message, 0, $this->feedMessageLength);
				$lastsign = substr($message_small, strlen($message_small) - 1, 1);
				while($lastsign != " " && strlen($message) > 0)
				{
					$message_small = substr($message, 0, strlen($message_small) - 1);
					$lastsign = substr($message_small, strlen($message_small) - 1, 1);
				}
				$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
				if(preg_match($reg_exUrl, $message, $url)) {
		       		$message = preg_replace($reg_exUrl, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $message);
				}
				if(preg_match($reg_exUrl, $message_small, $url)) {
				   $message_small = preg_replace($reg_exUrl, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $message);
				}
				echo '<div class="fb_message"><div class="fb_message_short">' . nl2br($message_small) . '</div><div class="fb_message_readmore">read more...</div><div class="fb_message_original">' . nl2br($message) . '</div></div>';
			}
			else
			{
				$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
				if(preg_match($reg_exUrl, $message, $url)) {
		       		$message = preg_replace($reg_exUrl, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $message);
				}
				echo '<div class="fb_message">' . nl2br($message) . '</div>';
			}
		}
	}
?>