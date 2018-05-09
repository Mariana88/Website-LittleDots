<?php
class data
{
	//functie die data delete, ook alle subdata
	static public function delete($table, $id, $order_field = "", $parent_id_field = "", $parent_id_value = "")
	{
		data::delete_one($table, $id, $order_field, $parent_id_field, $parent_id_value);
		//opzoeken of er subdata is
		$res = DBConnect::query("SELECT * FROM `sys_database_subtable` WHERE `table_parent`='" . $table . "'");
		while($row_sub = mysql_fetch_array($res))
		{
			$res_del = DBConnect::query("SELECT * FROM `" . $row_sub["table_sub"] . "` WHERE `" . $row_sub["foreign_key_field"] . "`='" . $id . "'", __FILE__, __LINE__);
			while($row_del = mysql_fetch_array($res_del))
			{
				data::delete($row_sub["table_sub"], $row_del["id"]);
			}
		}
	}
	
	static public function delete_one($table, $id, $order_field = "", $parent_id_field = "", $parent_id_value = "")
	{
		DBConnect::query("DELETE FROM `" . $table . "` WHERE `id`='" . $id . "'", __FILE__, __LINE__);
		$res_meta = DBConnect::query("SELECT * FROM `sys_database_table` WHERE `table`='" . $table . "'", __FILE__, __LINE__);
		$row_meta = mysql_fetch_array($res_meta);
		if($row_meta && $row_meta["lang_dep"] > 0)
			DBConnect::query("DELETE FROM `" . $table . "_lang` WHERE `lang_parent_id`='" . $id . "'", __FILE__, __LINE__);
		//als er ordening is reorder table
		if(trim($order_field) != "")
		{
			$sql = "SELECT `id` FROM `" . $table . "`";
			if(trim($parent_id_field) != "")
				$sql .= " WHERE `" . $parent_id_field . "`='" .  $parent_id_value . "'";
			$sql .= " ORDER BY `" . $order_field . "`";
			$res = DBConnect::query($sql, __FILE__, __LINE__);
			$order = 1;
			while($row = mysql_fetch_array($res))
			{
				DBConnect::query("UPDATE `" . $table . "` SET `" . $order_field . "`='" . $order . "' WHERE `id`='" . $row['id'] . "'", __FILE__, __LINE__);
				$order++;
			}
		}
	}
}
?>