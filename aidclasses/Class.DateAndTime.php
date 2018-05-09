<?php
	//deze klasse bevat functies voor datums te maken
	class DateAndTime
	{
		//checken van een datum die we dan teruggeven in een timestamp of 0 als het een foute datum is
		function check_date($format, $date)
		{
			$day = 0;
			$month = 0;
			$year = 0;
			
			switch($format)
			{
				case "d/m/Y":
					$tmp = explode('/', $date);
					if(count($tmp) != 3)
						return 0;
					else
					{
						$day = (int)$tmp[0];
						$month = (int)$tmp[1];
						$year = (int)$tmp[2];
					}
					break; 
			}
			if(checkdate($month, $day, $year))
				return mktime(0, 0, 0, $month, $day, $year);
			else 
				return -1;
		}
		
		//functie die een timestamp maakt (eigenlijk roept ie gewoon check date aan)
		function create_timestamp($format, $date)
		{
			return DateAndTime::check_date($format, $date);
		}
		
		//checken van een datum die we dan teruggeven in een timestamp of 0 als het een foute datum is
		function check_time($time, $hours, $minutes, $seconds)
		{
			$hour = 0;
			$minute = 0;
			$second = 0;
			$index = 0;
			$tmp = explode(':', $time);
			if($hours)
			{
				$hour = $tmp[$index];
				$index++;
			}
			if($minutes)
			{
				$minute = $tmp[$index];
				$index++;
			}
			if($seconds)
			{
				$second = $tmp[$index];
				$index++;
			}
			if(count($tmp) != $index)
				return -1;
			if($hour > 23 || $hour < 0 || $minute > 59 || $minute < 0 || $second > 59 || $second < 0)
				return -1;
			else
				return $second + $minute * 60 + $hour * 3600;
		}
		
		function format_time($stamp, $hours, $minutes, $seconds)
		{
			if ($stamp == -1)
				return "";
			$h = (int)($stamp/3600);
			$stamp -= $h * 3600;
			$m = (int)($stamp/60);
			$s = $stamp - $m * 60;
			
			$tmp = array();
			if($hours) $tmp[] = (($h<10)?"0":"") . $h;
			if($minutes) $tmp[] = (($m<10)?"0":"") . $m;
			if($seconds) $tmp[] = (($s<10)?"0":"") . $s;
			
			return implode(":", $tmp);
		}
	}
?>