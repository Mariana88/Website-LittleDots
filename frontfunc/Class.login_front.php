<?php
	/*--------------------------Login--------------------------------------
		Deze klasse checkt de login en heeft ook de code om het login scherm te tonen.
		Functie check_login moet in het begin van de index.php worden aangeroepen.
	*/
	
	class login_front
	{
		
		//functie die moet worden aangeroepen in het begin van de index pagina
		static function check_login()
		{
			if(isset($_POST["front_login"]))
			{
				unset($_SESSION["login_front_usergroup_id"]);
				//checken of het een correct paswoord is
				//echo "SELECT * FROM `site_user_front` WHERE `lname`='" . addslashes($_POST["front_login_email"]) . "' AND `lpass`='" . md5($_POST["front_login_paswoord"]) . "' AND `can_login`='1'";
				$result = DBConnect::query("SELECT * FROM `site_user_front` WHERE `lname`='" . addslashes($_POST["front_login_email"]) . "' AND `lpass`='" . md5($_POST["front_login_paswoord"]) . "' AND `can_login`='1'", __FILE__, __LINE__);
				if(mysql_num_rows($result) <= 0)
				{
					unset($_SESSION["login_front_username"]);
					unset($_SESSION["login_front_usergroup_id"]);
					unset($_SESSION["login_front_id"]);
					return false;
				}
				else
				{
					$row = mysql_fetch_array($result);
					$_SESSION["login_front_username"] = $_POST["front_login_email"];
					$_SESSION["login_front_usergroup_id"] = $row["user_group"];
					$_SESSION["login_front_id"] = $row["id"];
					return true;
				}
			}
			elseif(url_front::$url_extra == "/logout")
			{
				unset($_SESSION["login_front_username"]);
					unset($_SESSION["login_front_usergroup_id"]);
					unset($_SESSION["login_front_id"]);
				return false;
			}
			else
			{
				if(isset($_SESSION["login_front_username"]))
				{
					//toch nog even checken of de user bestaat
					$result = DBConnect::query("SELECT * FROM `site_user_front` WHERE `lname`='" . addslashes($_SESSION["login_front_username"]) . "' AND `can_login`='1'", __FILE__, __LINE__);
					if(mysql_num_rows($result) <= 0)
					{
						unset($_SESSION["login_front_username"]);
						unset($_SESSION["login_front_usergroup_id"]);
						unset($_SESSION["login_front_id"]);
						return false;
					}
					else
						return true;
				}
			}
		}
		
		//functie die zegt of de user de pagina mag zien
		function page_front_right($page_id)
		{
			$res = DBConnect::query("SELECT * FROM site_page_root WHERE `id`='" . $page_id . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				if($row["front_protected"] <= 0)
					return true;
				else
				{
					if(in_array($_SESSION["login_front_usergroup_id"], explode(";", $row["front_permission"])))
						return true;
					else
						return false;
				}
			}
			else
				return false;
		} 
	}
?>