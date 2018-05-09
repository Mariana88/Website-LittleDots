<?php
class debug
{
	static function message($message)
	{
		DBConnect::query("insert into aa_debug(`message`) VALUES('" . addslashes($message) . "')", __FILE__, __LINE__);
	}
}

?>