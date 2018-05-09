<?php
	/*--------------------------Login--------------------------------------
		deze klasse is een portaal tussen het programma en de database
		je kan er queries aan vragen en deze geeft het resultaat terug en controleerd of er geen fouten zijn gebeurd
	*/
	
	class DBConnect
	{
		var $cfg;
		
		//functie die een query uitvoerd en altijd controleerd op fouten
		function query($sql, $file = NULL, $line = NULL)
		{
			$link = mysql_connect("Kleine99puntjes.db.11125927.hostedresource.com", "Kleine99puntjes", "Grote99puntjes!") or die(mysql_error());
			mysql_select_db("Kleine99puntjes", $link);
			
			$result = mysql_query($sql, $link);
			//var_dump($result);
			if(mysql_error($link) != "")
			{
				//wat moeten we doen bij een database error?
				if(DBConnect::get_cfg("error_show"))
				{
					echo '<div>A database error occurd:<br>
							<b>MYSQL reported:</b> ' . mysql_error($link) . '<br>
							<b>SQL:</b> ' . $sql . '<br>
							This error was reported in file <b>' . $file . '</b> on line <b>' . $line . '</b>.<br>
							</div>';
				}
				if(DBConnect::get_cfg("error_mail"))
				{
					$message = 'A database error occurd on ' . date("d/m/Y H:i:s", time()) . ': 
MYSQL reported: ' . mysql_error() . '\n
SQL: ' . $sql . '\n
This error was reported in file "' . $file . '" on line ' . $line . '.';
					$adres = DBConnect::get_cfg("error_emails");
					$ond = DBConnect::get_cfg("error_mail_title");
					
					mail($adres, $ond, $message, "From: noreply@simon.be");
				}
			}
			return $result;
		}
		
		function getConnection()
		{
			
		}
		
		function get_cfg($nodename)
		{
			switch($nodename)
			{
				case "host": return "Kleine99puntjes.db.11125927.hostedresource.com";
				case "username": return "Kleine99puntjes";
				case "password": return "Grote99puntjes!";
				case "databasename": return "Kleine99puntjes";
				case "error_show": return 1;
				case "error_mail": return 1;
				case "error_emails": return "segers.simon@gmail.com";
				case "error_mail_title": return "BLACK FLOWER DB error";
			}
		}
		
		function get_last_inserted($table, $id_name)
		{
			$result = DBConnect::query("SELECT `" . $id_name . "` FROM `" . $table . "` ORDER BY `" . $id_name . "` DESC LIMIT 0,1", __FILE__, __LINE__);
			$row = mysql_fetch_array($result);
			return $row[$id_name];
		}
		
		function check_if_table_exists($tablename)
		{
			$result = DBConnect::query("SELECT count(*) FROM information_schema.tables WHERE table_schema = '" . DBConnect::get_cfg("databasename") . "' AND table_name = '" . $tablename . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($result);
			if($row[0] > 0)
				return true;
			else
				return false;
		}
		
		function check_if_column_exists($tablename, $columnname)
		{
			$result = DBConnect::query("SELECT count(*) FROM information_schema.columns WHERE table_schema = '" . DBConnect::get_cfg("databasename") . "' AND table_name = '" . $tablename . "' AND `column_name`='" . $columnname . "'", __FILE__, __LINE__);
			$row = mysql_fetch_array($result);
			if($row[0] > 0)
				return true;
			else
				return false;
		}
		
		function get_colums($tablename)
		{
			$result = DBConnect::query("SELECT * FROM information_schema.columns WHERE table_schema = '" . DBConnect::get_cfg("databasename") . "' AND table_name = '" . $tablename . "'", __FILE__, __LINE__);
			return $result;
		}
	}
?>