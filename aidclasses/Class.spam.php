<?php
	//this class helps with email functions
	class spam
	{
		static function checkpost($forlinks) 
		{
 			$spam = false;
			foreach($_POST as $onepost)
			{
				if(stristr($onepost, "http://"))
					$spam = true;
			}
			
			return $spam;
		}
		
		static function checkget($forlinks)
		{
		
		}
	}
?>