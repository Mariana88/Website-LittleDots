<?php
	/*--------------------------Login--------------------------------------
		Deze klasse checkt de login en heeft ook de code om het login scherm te tonen.
		Functie check_login moet in het begin van de index.php worden aangeroepen.
	*/
	
	class login
	{
		static function right($name, $rule, $data_id = NULL)
		{
			if(isset($_SESSION["login_usergroup_id"]) && isset($_SESSION["login_username"]))
			{
				
				if($_SESSION["login_usergroup_id"] == 1)
					return true;
				
				$sql = "SELECT `rules` FROM `site_rights` WHERE `usergroup`='" . $_SESSION["login_usergroup_id"] . "' AND `name`='" . $name . "'";
				if($data_id != NULL)
					$sql .= " AND `data_id`='" . $data_id . "'";
				$res = DBConnect::query($sql, __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					$rules = unserialize($row["rules"]);
					//debug::message("rule " . $rule . " " . (($rules[$rule])?"true":"false"));
					return (($rules[$rule])?true:false);
				}
				else
					return false;
			}
			
			return false;
		}
		
		static function rootpage()
		{
			if(isset($_SESSION["login_usergroup_id"]) && isset($_SESSION["login_username"]))
			{
				
				if($_SESSION["login_usergroup_id"] == 1)
					return 0;
				
				$sql = "SELECT * FROM `site_usergroup` WHERE `id`='" . $_SESSION["login_usergroup_id"] . "'";
				$res = DBConnect::query($sql, __FILE__, __LINE__);
				if($row = mysql_fetch_array($res))
				{
					return $row["r_rootpage"];
				}
				else
					return 0;
			}
			
			return false;
		}
		
		static function check_templ($templ_id, $action)
		{
			$res = DBConnect::query("SELECT * FROM site_usergroup_page_template WHERE usergroup_id='" . $_SESSION["login_usergroup_id"] . "' AND `template_id`='" . $templ_id . "' AND `" . $action . "`>'0'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
				return true;
			else
				return false;
		}
		
		//functie die moet worden aangeroepen in het begin van de index pagina
		static function check_login()
		{
			if(isset($_POST["login"]))
			{
				unset($_SESSION["login_usergroup_id"]);
				//checken of het een correct paswoord is
				//echo "SELECT * FROM `site_user` WHERE `lname`='" . addslashes($_POST["username"]) . "' AND `lpass`='" . md5($_POST["password"]) . "' AND `can_login`='1'";
				$result = DBConnect::query("SELECT * FROM `site_user` WHERE `lname`='" . addslashes($_POST["username"]) . "' AND `lpass`='" . md5($_POST["password"]) . "' AND `can_login`='1'", __FILE__, __LINE__);
				if(mysql_num_rows($result) <= 0)
				{
					unset($_SESSION["login_username"]);
					return false;
				}
				else
				{
					$row = mysql_fetch_array($result);
					$_SESSION["login_username"] = $_POST["username"];
					$_SESSION["login_usergroup_id"] = $row["user_group"];
					$_SESSION["login_ip"] = $_SERVER["REMOTE_ADDR"];
					//we checken of de user mag inloggen
					/*if(rights::check_main_right("BACKSITE_LOGIN"))
						return true;
					else
					{
						unset($_SESSION["login_username"]);
						return false;
					}
					*/
					return true;
				}
			}
			elseif(url::$page == "logout")
			{
				unset($_SESSION["login_username"]);
				unset($_SESSION["login_usergroup_id"]);
				return false;
			}
			else
			{
				if(isset($_SESSION["login_username"]))
				{
					//toch nog even checken of de user bestaat
					$result = DBConnect::query("SELECT * FROM `site_user` WHERE `lname`='" . addslashes($_SESSION["login_username"]) . "' AND `can_login`='1'", __FILE__, __LINE__);
					if(mysql_num_rows($result) <= 0)
					{
						unset($_SESSION["login_username"]);
						unset($_SESSION["login_usergroup_id"]);
						return false;
					}
					else
					{
						if($_SESSION["login_ip"] == $_SERVER["REMOTE_ADDR"])
							return true;
						else
							return false;
					}
				}
			}
		}
		
		//tonen van het formulier voor het inloggen
		function display_form()
		{
			//tonen van loginscherm
			?>
			<div id="superdiv">
			<div id="loginform">
			<p style="font-size: 20px; color: #FE0649">Get into the admin</p><br>
			<form action="/cms" method="POST">
				<label>Username:</label>
				<input class="no_standard_width" type="text" name="username" size="20"><br>
				<label>Password:</label>
				<input class="no_standard_width" type="password" name="password" size="20">
                <div style="height: 20px; clear:both;"></div>
				<input name="login" type="submit" id="login" value="Login" style="float: left;">
                <div class="error" style="float: left; display: none; line-height: 24px; margin-left: 10px; color: #F00;" id="login-error-msg"></div>
                <div style="height: 0px; clear:both;"></div>
			</form>
			</div>
			</div>
			<?php
			if (isset($_POST["login"]))
				echo '<script> $(document).ready(function(){$("#login-error-msg").text("login or password is incorrect"); $("#login-error-msg").show(300); $(\'[name="username"]\').focus();})</script>';
				
		}
		
		static function createpassw($length)
		{
			$letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
			$pwd = "";
			for($i=0; $i < $length ; $i++)
			{
				$pwd .= $letters[rand(0, (count($letters)-1))];
			}
			return $pwd;
		}
	}
?>