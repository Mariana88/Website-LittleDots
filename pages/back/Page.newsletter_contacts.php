<?php
	if(!login::right("backpage_newsletter_contacts", "view"))
	{
		echo '<div id="superdiv"><div id="content">
				<div style="text-align:center; color:#CCCCCC; font-weight:bold;"><br><br><br>You don\'t have the permissions to be here!<br><br><br><br></div>
			</div></div>';
	}
	else
	{
?>
<?php
		echo '<div id="superdiv"><div style="padding-left:8px; padding-right:8px;" name="form_siteconfig" id="form_siteconfig">';
		
		echo '<div class="contentheader">
					<h1>Newsletter: Contacts</h1>
				</div>';
		//hier echoen we de filter
		$extra_where = "";
		//var_dump($_POST);
		if(isset($_POST["FILTER_site_user_front_id"]))
		{
			//we maken de extra_where voor de datagrid
			foreach($_POST as $key => $value)
			{
				if(trim($value) == "" || trim($value) == "0")
					continue;
				if(substr($key, 0, 7) != "FILTER_")
					continue;
				else
					$key = substr($key, 7);
				$test = explode("site_user_front_", $key);
				$table = "site_user_front";
				$field = $test[1];
				//een nofield negeren we
				$test = explode("_", $key);
				if($test[count($test) - 1] == "nofield")
					continue;
				//checken of het een veld is met checkbox opties
				if(isset($_POST[$key . "_chk_nofield"]))
				{
					//we moeten alle opties checken
					$opties = explode(";", $value);
					$counter = 0;
					if($extra_where == "")
						$extra_where .= " (";
					else
						$extra_where .= " AND (";
					foreach($opties as $optie)
					{
						
						if ($counter == 0)
							$extra_where .= " `" . $field . "` LIKE '%" . $optie . "%' ";
						else
							$extra_where .= " OR `" . $field . "` LIKE '%" . $optie . "%' ";
						
						$counter++;
					}
					$extra_where .= ") ";
				}
				else
				{
					if($key == "site_user_front_creatie")
					{
						if ($extra_where == 0)
							$extra_where .= " (`" . $field . "`>='" . DateAndTime::check_date("d/m/Y", $value) . "' AND `" . $field . "`<='" . (DateAndTime::check_date("d/m/Y", $value) + 86400) . "') ";
						else
							$extra_where .= " AND (`" . $field . "`>='" . DateAndTime::check_date("d/m/Y", $value) . "' AND `" . $field . "`<='" . (DateAndTime::check_date("d/m/Y", $value) + 86400) . "') ";
					}
					elseif($key == "site_user_front_ezine")
					{
						if($extra_where == "")
							$extra_where .= " `" . $field . "`>'0' ";
						else
							$extra_where .= " AND `" . $field . "`>'0' ";
					}
					else
					{
						if($extra_where == "")
							$extra_where .= " `" . $field . "` LIKE '%" . $value . "%' ";
						else
							$extra_where .= " AND `" . $field . "` LIKE '%" . $value . "%' ";
					}
				}
			}
		}
		//var_dump($extra_where);
		echo '<div class="splitter"><span>Filter</span></div>';
		echo '<span style="cursor: pointer" hidden="true" onClick="
				if(this.getAttribute(\'hidden\')==\'true\')
				{
					document.getElementById(\'newsletter_contacts_filter\').style.display = \'Block\'; 
					this.innerHTML = \'Hide filter\';
					this.setAttribute(\'hidden\', \'false\');
				}
				else
				{
					document.getElementById(\'newsletter_contacts_filter\').style.display = \'None\'; 
					this.innerHTML = \'Show filter\';
					this.setAttribute(\'hidden\', \'true\');
				}
				">Show filter</span>';
		echo '<div id="newsletter_contacts_filter" style="display: none;">';
		echo '<form id="newsletter_contacts_filter_form" method="post">';
		form::show_autoform("site_user_front", $_POST, array("id" => "id", "user_group" => "user_group", "lname" => "lname", "lpass" => "lpass", "can_login" => "can_login"), "FILTER_");
		echo '<input type="submit" value="Filter"/>';
		
		echo '</form>';
		echo '</div>';
		echo '<div class="splitter"><span>Contacts</span></div>';
		//tonen van selectie mechanisme
		
		/*
		$ds = new datasource();
		$ds->type = "DATABASE";
		$ds->db_table = "site_user_front";
		
		if($extra_where != "")
			$ds->db_extra_where = $extra_where;
		
		$dg = new datagridnew();
		$dg->title = "Select Contacts";
		$dg->show_title_bar = false;
		$dg->checkbox = false;
		$dg->id = "dg_newsletter_selectcontacts";
		$dg->datasource = $ds;
		$dg->id_field = "email";
		//if(trim($autosortfield) != "")
		//	$dg->sort_field = $autosortfield;
		//$dg->rowdblclick = 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'\', cms2_fill_form);';
		$dg->paging = true;
		$dg->perpage = 100;
		//$dg->addicon("new", "/css/back/icon/twotone/plus.gif", "/css/back/icon/twotone/gray/plus.gif", 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=new\', \'\', cms2_fill_form);', false, false, false);
		//$dg->addicon("edit", "/css/back/icon/twotone/edit.gif", "/css/back/icon/twotone/gray/edit.gif", 'tb_show(\'Edit or add a data entry\', \'#TB_inline?height=500&width=650&inlineId=tb_de_' . $this->id . '&modal=false\', false); send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&popup_id=' . $this->id . '&showform=\' + dg_dg_de_' . $this->id . '.selected_id, \'\', cms2_fill_form);', true, true, true);
		//$dg->addicon("delete", "/css/back/icon/twotone/trash.gif", "/css/back/icon/twotone/gray/trash.gif", 'show_question_message(\'Are you sure you want to delete the selected entries?\', function(){dataeditor_delete_accept(dg_dg_de_' . $this->id . '.selected_ids, \'' . $this->id . '\', \'dg_de_' . $this->id . '\', dg_dg_de_' . $this->id . '_html_panel);}, tb_remove);', true, true, true);
		
		$result_fields = DBConnect::query("SHOW COLUMNS FROM `site_user_front`", __FILE__, __LINE__);
		
		while($row_field = mysql_fetch_array($result_fields))
		{
			if($row_field["Field"] != "id" && $row_field["Field"] != "user_group" && $row_field["Field"] != "lname" && $row_field["Field"] != "lpass" && $row_field["Field"] != "can_login")
			{
				$ddesc = new data_description("","","DATABASE", "site_user_front." . $row_field["Field"]);
				
				$dg->addfield($row_field["Field"], $row_field["Field"], true, true, true, 0, false, $ddesc->type);
			}
		}
			
		$dg->publish(false);
		*/
			
		
		$de = new dataeditor("site_user_front", 200, 200);
		$de->set_table("site_user_front");
		//$de->add_hiddenfield("id");
		$de->add_hiddenfield("user_group");
		$de->add_hiddenfield("lname");
		$de->add_hiddenfield("lpass");
		$de->add_hiddenfield("can_login");
		
		$de->add_hiddenfield_dg("adres");
		$de->add_hiddenfield_dg("nummer");
		$de->add_hiddenfield_dg("woonplaats");
		$de->add_hiddenfield_dg("postcode");
		$de->add_hiddenfield_dg("land");
		$de->set_perpage(100);
		//if($extra_where != "")
			$de->set_extra_where($extra_where);
		$de->publish(false);
		
		echo '</div></div>';
?>
<?php
	}
?>