<?php
	class management
	{
		static function publish_contacttree($filter = array(), $formail=false)
		{
			if(count($filter) > 0)
			{
				management::publish_filter($filter);
			}
			else
			{
				echo '<div class="treeview"><ul class="treeview" id="treeview_contacts">';
				//Rootnode
				
					echo '<li id="managementtree_root"><div the_id="0" type="0" onclick="select_me_please(\'treeview_contacts\', this); management_tree_select(this);" ondblclick="management_tree_open(this);" style="padding: 2px 4px 2px 4px; backgound-color:#456123; height:18px;"><span>All contacts</span></div><ul>';
					management::tree_node_publish(0, 0, "group", $filter, $formail);
					echo '</ul></li>';
				
				echo '</ul></div>
						<script type="text/javascript">
							ddtreemenu.createTree("treeview_contacts", true);
						</script>';
			}
		}
		
		static function publish_filter($filter)
		{
			$search = array("contact" => array(), "entity" => array(), "group" => array());
			//opzoeken van alle contacten/entities/groepen die aan de voorwaarden voldoen
			$result = DBConnect::query("SELECT * FROM man_contact WHERE `id`>'0' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":'') . " ORDER BY `name`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($result))
			{
				$search["contact"][] = $row["id"];
			}
			$result = DBConnect::query("SELECT * FROM man_entity WHERE `id`>'0' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":'') . " ORDER BY `name`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($result))
			{
				$search["entity"][] = $row["id"];
			}
			$result = DBConnect::query("SELECT * FROM man_entity_group " . ((isset($filter["name"]))?"WHERE `name` LIKE '%" . $filter["name"] . "%'":"WHERE `id`='0'") . "  ORDER BY `name`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($result))
			{
				$search["group"][] = $row["id"];
			}
			
			//nu begint het overlopen van de boom
			management::publish_filter_node(0, $search, "group", "", trim($filter["name"]));
			
		}
		
		static function publish_filter_node($parent_id, $search, $parenttype, $prefix, $filtername)
		{
			$results = array();
			if($parenttype == "group")
			{
				//een groep kan andere groepen bevatten en losse entities en contacten
				$results["group"] = DBConnect::query("SELECT * FROM man_entity_group WHERE parent_id='" . $parent_id . "' ORDER BY `name`", __FILE__, __LINE__);
				$results["entity"] = DBConnect::query("SELECT * FROM man_entity WHERE group_id='" . $parent_id . "' ORDER BY `name`", __FILE__, __LINE__);
				$results["contact"] = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='" . $parent_id . "' AND entity_id='0' ORDER BY `name`", __FILE__, __LINE__);
			}
			if($parenttype == "entity")
			{
				//kan enkel contacten bevatten
				$results["contact"] = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='0' AND entity_id='" . $parent_id . "' ORDER BY `name`", __FILE__, __LINE__);
			}
			foreach($results as $type => $result)
			{
				while ($row = mysql_fetch_array($result)) 
				{
					$show = false;
					switch($type)
					{
						case 'group':
							if(in_array($row["id"], $search["group"]))
								$show = true;
							break;
						case 'entity':
							if(in_array($row["id"], $search["entity"]))
								$show = true;
							break;
						case 'contact':
							if(in_array($row["id"], $search["contact"]))
								$show = true;
							break;
					}
					$prefix2 = $prefix . '<span the_id="' . stripslashes($row["id"]) . '" type="' . $type . '" onclick="management_tree_open(this);" style="cursor: pointer; line-height: 18px;">';
					switch($type)
					{
						case 'group':
							$prefix2 .= '<img src="/css/back/icon/management/tree_group.gif">&nbsp;';
							break;
						case 'entity':
							$prefix2 .= '<img src="/css/back/icon/management/tree_entity.gif">&nbsp;';
							break;
						case 'contact':
							$prefix2 .= '<img src="/css/back/icon/management/tree_contact.gif">&nbsp;';
							break;
					}
					if($filtername != "")
						$prefix2 .= str_ireplace($filtername, '<b>' . $filtername . '</b>', htmlentities(stripslashes($row["name"])));
					else
						$prefix2 .= htmlentities(stripslashes($row["name"]));
					$prefix2 .= '</span>';
					
					if($show)
					{
						echo '<div the_id="' . stripslashes($row["id"]) . '" type="' . $type . '" style="padding: 2px 4px 4px 4px; backgound-color:#456123; min-height:18px; border-bottom: 1px solid #AAAAAA; margin-top:3px">';
						
						
						echo $prefix2 . '</div>';
					}
					management::publish_filter_node(stripslashes($row["id"]), $search, $type, $prefix2 . ' &gt; ', $filtername);
				}
			}
			if($firstfound)
				echo '</ul>';
		}
		
		//pagefound = als in rechten een rootpage gedefinieerd is dan gebruiken we dit attribuut
		static function tree_node_publish($parent_id, $level, $parenttype, $filter = array(), $formail=false)
		{
			
			//opzoeken van alle groepen
			$results = array();
			if($parenttype == "group")
			{
				//een groep kan andere groepen bevatten en losse entities en contacten
				$results["group"] = DBConnect::query("SELECT * FROM man_entity_group WHERE parent_id='" . $parent_id . "' ORDER BY `name`", __FILE__, __LINE__);
				$results["entity"] = DBConnect::query("SELECT * FROM man_entity WHERE group_id='" . $parent_id . "' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":'') . " ORDER BY `name`", __FILE__, __LINE__);
				$results["contact"] = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='" . $parent_id . "' AND entity_id='0' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":'') . " ORDER BY `name`", __FILE__, __LINE__);
			}
			if($parenttype == "entity")
			{
				//kan enkel contacten bevatten
				$results["contact"] = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='0' AND entity_id='" . $parent_id . "' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":'') . " ORDER BY `name`", __FILE__, __LINE__);
			}
			$firstfound = false;
			foreach($results as $type => $result)
			{
				while ($row = mysql_fetch_array($result)) 
				{
					if(!$firstfound && $parent_id != 0  && $parent_id != "-1")
					{
						echo '<ul>';
						$firstfound = true;
					}
					$subpages = false;
					if($type == "group")
					{
						$restest = DBConnect::query("SELECT * FROM man_entity_group WHERE parent_id='" . $row["id"] . "'", __FILE__, __LINE__);
						if(mysql_num_rows($restest) > 0) $subpages = true;
						$restest = DBConnect::query("SELECT * FROM man_entity WHERE group_id='" . $row["id"] . "' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":''), __FILE__, __LINE__);
						if(mysql_num_rows($restest) > 0) $subpages = true;
						$restest = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='" . $row["id"] . "' AND entity_id='0' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":''), __FILE__, __LINE__);
						if(mysql_num_rows($restest) > 0) $subpages = true;
					}
					if($type == "entity")
					{
						$restest = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='0' AND entity_id='" . $row["id"] . "' " . ((isset($filter["land"]))?" AND `land`='" . $filter["land"] . "'":'') . ((isset($filter["tag"]))?" AND (`tags` LIKE '%;" . $filter["tag"] . ";%' OR `tags` LIKE '" . $filter["tag"] . ";%' OR `tags` LIKE '%;" . $filter["tag"] . "' OR tags = '" . $filter["tag"] . "')":'') . ((isset($filter["name"]))?" AND `name` LIKE '%" . $filter["name"] . "%'":''), __FILE__, __LINE__);
						if(mysql_num_rows($restest) > 0) $subpages = true;
					}
					
					echo '<li id="managementtree_' . $type . '_' . stripslashes($row["id"]) . '" ' . (($subpages)?'class="submenu"':'') . '><div the_id="' . stripslashes($row["id"]) . '" type="' . $type . '" onclick="select_me_please(\'treeview_contacts\', this); ' . (($formail)?'':'management_tree_select(this);') . '" ' . (($formail)?((trim($row["email"])!=""||trim($row["email2"])!="")?'ondblclick="if($(\'#to\').val() != \'\') $(\'#to\').val(\', \' + $(\'#to\').val()); $(\'#to\').val(\'' . ((trim($row["email"])!="")?trim($row["email"]):trim($row["email2"])) . '\' + $(\'#to\').val());"':''):'ondblclick="management_tree_open(this);"') . ' style="padding: 2px 4px 2px 4px; backgound-color:#456123; min-height:18px; ">';
					switch($type)
					{
						case 'group':
							echo '<img src="/css/back/icon/management/tree_group.gif">&nbsp;<span style="color: #666666;">' . htmlentities(stripslashes($row["name"])) . '</span>';
							break;
						case 'entity':
							echo '<img src="/css/back/icon/management/tree_entity.gif">&nbsp;<span style="' . (($row["active"])?'color: #000"':'color:#666666') . '">' .  htmlentities(stripslashes($row["name"])) . '</span>';
							break;
						case 'contact':
							echo '<img src="/css/back/icon/management/tree_contact.gif">&nbsp;<span style="color: #666666;">' .  htmlentities(stripslashes($row["name"])) . '</span>';
							break;
					}
					echo '</div>';
					
					management::tree_node_publish(stripslashes($row["id"]), ($level+1), $type, $filter, $formail);
					
					echo '</li>';
				}
			}
			if($firstfound)
				echo '</ul>';
		}
		
		static function delete_treenode($type, $id)
		{
			switch($type)
			{
				case 'group':
					//kind groepen
					$res = DBConnect::query("SELECT * FROM man_entity_group WHERE parent_id='" . $id . "'", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						management::delete_treenode('group', $row["id"]);
					}
					//kind entities
					$res = DBConnect::query("SELECT * FROM man_entity WHERE group_id='" . $id . "'", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						management::delete_treenode('entity', $row["id"]);
					}
					//kind contacten
					$res = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='" . $id . "' AND entity_id='0'", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						management::delete_treenode('contact', $row["id"]);
					}
					//zelf deleten
					DBConnect::query("DELETE FROM man_entity_group WHERE `id`='" . $id . "'",__FILE__, __LINE__);
					break;
				case 'entity':
					//kind contacten
					$res = DBConnect::query("SELECT * FROM man_contact WHERE entity_group_id='0' AND entity_id='" . $id . "'", __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						management::delete_treenode('contact', $row["id"]);
					}
					//zelf deleten
					DBConnect::query("DELETE FROM man_entity WHERE `id`='" . $id . "'",__FILE__, __LINE__);
					break;
				case 'contact':
					//zelf deleten
					DBConnect::query("DELETE FROM man_contact WHERE `id`='" . $id . "'",__FILE__, __LINE__);
					break;
			}
		}
	}
?>