<?php
	require_once('aidclasses/data/Class.Files.php');

	class datasource
	{
		public $type; 			// DATABASE / FILESYSTEM / XML / CSV
		
		public $limit_top;		//if we only want a part of the data we use these limits
		public $limit_count;
		public $searchstr;		//if we have a search filter
		public $sort_field;		//whitch field to sort on
		public $sort_order;		//ASC or DESC
		
		//FILESYSTEM PROPS
		public $fs_root; 		//de root --> goed als er een filter wordt gebruikt
		public $fs_folder;		//the folder that we have to read
		public $fs_extentions;	//filter on the extentions
		public $fs_folders; 	//must be true if we also want to read the folders
		public $fs_files;		//must be true if we also want to read the files
		public $fs_from_root;  	//if its set we search the whole folder
		
		//DABASE PROPS
		public $db_table;
		public $db_extra_where;
		public $db_lang_dep;
		public $db_current_lang;
		
		//XML PROPS
		
		//CSV PROPS
		
		
		function __construct() 
		{
       		//we set the default vars
			$this->type = "DATABASE"; 			
		
			$this->limit_top = NULL;		
			$this->limit_count = NULL;
			$this->searchstr = "";
			$this->sort_field = "";		//whitch field to sort on
			$this->sort_order = "ASC";
			
			//FILESYSTEM PROPS
			$this->fs_root = "userfiles/"; 		
			$this->fs_folder = "";		
			$this->fs_extentions = NULL;	
			$this->fs_folders = true; 	
			$this->fs_files = true;				
			$this->fs_from_root = true; 	
			
			//DABASE PROPS
			$this->db_table = "";
			$this->db_extra_where = "";
			$this->db_lang_dep = false;
			$this->db_lang_current = mainconfig::$standardlanguage;
   		}
		
		public function get_data_count()
		{
			return $this->get_real_data(true);
		}
		
		public function get_data()
		{
			return $this->get_real_data(false);
		}
		
		private function get_real_data($only_count)
		{
			switch($this->type)
			{
				case "DATABASE":
					return $this->get_database($only_count);
					break;
				case "FILESYSTEM":
					return $this->get_filesystem($only_count);
					break;
				case "XML":
					break;
				case "CSV":
					break;
			}
		}
		
		private function get_database($only_count)
		{
			$counter = 0;
			$return_data = array();
			if(trim($this->db_table) != "")
			{
				$sql = "";
				if($this->db_lang_dep)
					$sql = "SELECT " . $this->db_table . ".*, " . $this->db_table . "_lang.* FROM `" . $this->db_table . "`, `" . $this->db_table . "_lang` WHERE " . $this->db_table . ".id = " . $this->db_table . "_lang.lang_parent_id AND " . $this->db_table . "_lang.lang = '" . $this->db_lang_current . "'";
				else
					$sql = "SELECT * FROM `" . $this->db_table . "`";
				if(trim($this->searchstr) != "")
				{
					$fields = array();
					$result = DBConnect::query("SHOW COLUMNS FROM `" . $this->db_table . "`", __FILE__, __LINE__);
					while($row = mysql_fetch_array($result))
						$fields[] = $row["Field"];
					if($this->db_lang_dep)
					{
						$result = DBConnect::query("SHOW COLUMNS FROM `" . $this->db_table . "_lang`", __FILE__, __LINE__);
						while($row = mysql_fetch_array($result))
							$fields[] = $row["Field"];
					}
					if(count($fields)>0)
					{
						if($this->db_lang_dep)
							$sql .= " AND (";
						else
							$sql .= " WHERE (";
						$first = true;
						foreach($fields as $field)
						{
							if($first)
								$first = false;
							else
								$sql .= " OR";
							
							//get the datadesc van het veld
							$result_desc = DBConnect::query("SELECT sys_datadescriptions.name as 'descname', sys_database_meta.* FROM sys_datadescriptions, sys_database_meta WHERE sys_datadescriptions.id=sys_database_meta.datadesc AND sys_database_meta.tablename='" . $this->db_table . "' AND sys_database_meta.fieldname='" . $field . "'", __FILE__, __LINE__);
							$row_desc = mysql_fetch_array($result_desc);
							switch($row_desc["descname"])
							{
								case "ENUM FROM TABLE":
									//we voeren de query uit van de options
									$enum_res = DBConnect::query($row_desc["data_options"], __FILE__, __LINE__);
									$in = array();
									while($enum_row = mysql_fetch_array($enum_res))
									{
										if(stristr($enum_row[1], $this->searchstr))
											$in[] = "'" . $enum_row[0] . "'";
									}
									if(count($in) > 0)
										$sql .= " `" . $field . "` IN (" . implode(",", $in) . ")";
									else
										$first = true;
									break;
								default:
									$sql .= " `" . $field . "` LIKE '%" . $this->searchstr . "%'";
									break;
							}
						}
						$sql .= ")";
					}
				}
				if(trim($this->db_extra_where) != "")
				{
					if(trim($this->searchstr) != "" || $this->db_lang_dep)
						$sql .= " AND ";
					else
						$sql .= " WHERE ";
					$sql .= $this->db_extra_where;
				}
				
				
				if(trim($this->sort_field))
					$sql .= " ORDER BY `" . $this->sort_field . "` " . $this->sort_order; 
				if(trim($this->limit_top) != "" && trim($this->limit_count) != "" && !$only_count)
					$sql .= " LIMIT " . $this->limit_top . ", " . $this->limit_count; 
				//var_dump($sql);
				$res = DBConnect::query($sql, __FILE__, __LINE__);
				if($only_count)
					return mysql_num_rows($res);
				else
				{
					//we halden de fields op
					$fieldnames = array();
					$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $this->db_table . "`", __FILE__, __LINE__);
					while($row_field = mysql_fetch_array($result_fields))
						$fieldnames[] = $row_field["Field"];
					if($this->db_lang_dep)
					{
						$result_fields = DBConnect::query("SHOW COLUMNS FROM `" . $this->db_table . "_lang`", __FILE__, __LINE__);
						while($row_field = mysql_fetch_array($result_fields))
							$fieldnames[] = $row_field["Field"];
					}
					$datacounter = 0;
					while($row = mysql_fetch_array($res))
					{
						$return_data[$datacounter] = array();
						foreach($fieldnames as $one_field)
							$return_data[$datacounter][$one_field] = $row[$one_field];
						$datacounter++;
					}
					
					return $return_data;
				}
			}
		}
		
		private function get_filesystem($only_count)
		{
			$counter = 0;
			$return_data = array();
			
			//if we have to search from root we search every folder
			if($this->fs_from_root && trim($this->searchstr) != "")
			{
				//we search in every folder
				$this->get_filesystem_folder($only_count, true, $_SERVER['DOCUMENT_ROOT'] . '/' . $this->fs_root, $counter, $return_data);
			}
			else
			{
				//we get the files from the $this->fs_folder
				$this->get_filesystem_folder($only_count, false, $this->fs_folder, $counter, $return_data);
			}
			if($only_count)
				return $counter;
			else
			{
				//we do the sorting
				$tmp_folders = array();
				$tmp_files = array();

				//sort the files and folders (folders always on name)
				for($i = 0 ; $i < count($return_data) ; $i++)
				{
					if($return_data[$i]["is_dir"] == "true")
						$tmp_folders[] = $return_data[$i];
					else
						$tmp_files[] = $return_data[$i];
				}
				if($this->sort_order == "ASC")
				{
					usort($tmp_folders, file_sort_name_asc);
					switch($this->sort_field)
					{
						case "filesizestr":
							usort($tmp_files, file_sort_size_asc);
							break;
						default:
							usort($tmp_files, file_sort_name_asc);
							break;
					}
				}
				else
				{
					usort($tmp_folders, file_sort_name_desc);
					switch($this->sort_field)
					{
						case "filesizestr":
							usort($tmp_files, file_sort_size_desc);
							break;
						default:
							usort($tmp_files, file_sort_name_desc);
							break;
					}
				}
				$return_data = array_merge($tmp_folders, $tmp_files);
				return $return_data;
			}
		}
		
		private function get_filesystem_folder($only_count, $recursive, $folder, &$counter, &$return_data)
		{
			if ($handle = opendir($folder)) 
			{
				/* This is the correct way to loop over the directory. */
				while (false !== ($file = readdir($handle))) 
				{
					//we do the filtering
					if($file=="." || $file=="..")
						continue;
					if((!$this->fs_files && !is_dir($folder . $file)) || (!$this->fs_folders && is_dir($folder . $file)))
						continue;
					if(is_array($this->fs_extentions) && !is_dir($folder . $file))
					{
						if(!in_array(strtolower(Files::subtract_extention($file)), $this->fs_extentions))
							continue;
					}
					
					//if we come here we know that we have to add the data to the result
					$counter++;
					
					if(!$only_count)
					{
						//we add the data in the array
						if(trim($this->searchstr) != "")
						{
							if(stristr($file, trim($this->searchstr)) !== FALSE)
								$return_data[] = array("id" => $folder . $file, "path" => $folder . $file, "filename" => str_ireplace($this->searchstr, '<b>' . $this->searchstr . '</b>', $file), "filesize" => filesize($folder . $file), "filesizestr" => Files::filesize_str($folder . $file), "protection" => fileperms($folder . $file), "iconmini" => ((is_dir($folder . $file))?'<img src="/css/back/icon/file/mini/folder.gif">':'<img src="' . Files::file_type_icon($folder . $file, false) . '">'), "icon64" => ((is_dir($folder . $file))?'<img src="/css/back/icon/file/64/folder.png">':'<img src="' . Files::file_type_icon($folder . $file, true) . '">'), "is_dir" => ((is_dir($folder . $file))?"true":"false"));
						}
						else
							$return_data[] = array("id" => $folder . $file, "path" => $folder . $file, "filename" => $file, "filesize" => filesize($folder . $file), "filesizestr" => Files::filesize_str($folder . $file), "protection" => fileperms($folder . $file), "iconmini" => ((is_dir($folder . $file))?'<img src="/css/back/icon/file/mini/folder.gif">':'<img src="' . Files::file_type_icon($folder . $file, false) . '">'), "icon64" => ((is_dir($folder . $file))?'<img src="/css/back/icon/file/64/folder.png">':'<img src="' . Files::file_type_icon($folder . $file, true) . '">'), "is_dir" => ((is_dir($folder . $file))?"true":"false"));
					}
					else
					{
						if(trim($this->searchstr) != "")
						{
							if(stristr($file, trim($this->searchstr)) !== FALSE)
								$counter++;
						}
						else
							$counter++;
					}
					if(is_dir($folder . $file) && $recursive)
					{
						$this->get_filesystem_folder($only_count, true, $folder . $file . "/", $counter, $return_data);
						//echo $folder . "/" . $file . "/";
					}
				}
				closedir($handle);
			}
			
			if($only_count)
				return $counter;
			else
				return $return_data;
		}
	}
	
	//FILESYSTEM SORT FUNCTIONS
	function file_sort_name_asc($x, $y)
	{
		 if ( strtolower($x["id"]) == strtolower($y["id"]) )
		  return 0;
		 else if ( strtolower($x["id"]) < strtolower($y["id"]) )
		  return -1;
		 else
		  return 1;
	}
	
	function file_sort_name_desc($x, $y)
	{
		 if ( strtolower($x["id"]) == strtolower($y["id"]) )
		  return 0;
		 else if ( strtolower($x["id"]) < strtolower($y["id"]) )
		  return 1;
		 else
		  return -1;
	}
	
	function file_sort_size_asc($x, $y)
	{
		 if ( $x["filesize"] == $y["filesize"] )
		  return 0;
		 else if ( $x["filesize"] < $y["filesize"] )
		  return -1;
		 else
		  return 1;
	}
	
	function file_sort_size_desc($x, $y)
	{
		 if ( $x["filesize"] == $y["filesize"] )
		  return 0;
		 else if ( $x["filesize"] < $y["filesize"] )
		  return 1;
		 else
		  return -1;
	}
?>