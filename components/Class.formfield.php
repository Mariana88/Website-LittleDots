<?php
	require_once "aidclasses/Class.DateAndTime.php";
	require_once "aidclasses/Class.email.php";
	require_once "aidclasses/Class.Pictures.php";
	require_once "aidclasses/data/Class.Files.php";
	require_once "aidclasses/data/Class.data_description.php";
	
	//Formfiel component
	class formfield
	{
		static function publish_dbfield($name, $value, $language = NULL, $width = 0)
		{
			$chomps = explode(".", $name);
			//we zoeken de db_meta
			$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
			if($row = mysql_fetch_array($res))
			{
				$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
				return formfield::publish($name, $value, $row["lang_dep"], $row["datadesc"], $options, $row["obligated"], $row["fieldlabel"] .(($row["lang_dep"]<=0 && mainconfig::$multilanguage)?" **":""), NULL, NULL, $width, 0, $row["data_help"], $language);
			}
			else
			{
				return formfield::publish($name, $value, 0, 1, array("length" => "255"), 0, $chomps[count($chomps)-1], NULL, NULL, $width, 0, "", $language);
			}
		}
		
		//Displays the component
		static function publish($postname, $value, $lang_dep, $desc_id, $options, $obligated, $label = NULL, $attributes = NULL, $javascript_onchange = NULL, $width=0, $height=0, $data_help="", $language)
		{
			//var_dump($value);
			$res_desc = DBConnect::query("SELECT * FROM `sys_datadescriptions` WHERE `id`='" . $desc_id . "'", __FILE__, __LINE__);
			$row_desc = mysql_fetch_array($res_desc);
			
			//extra attributes
			$attr = "";
			if(is_array(attributes))
			{
				foreach(attributes as $key => $value)
					$attr .= ' ' . $key . '="' . $value . '" ';
			}
			if($label != NULL && $row_desc["name"] != "HIDDEN ID" && $row_desc["name"] != "HIDDEN VARCHAR" && $row_desc["name"] != "HIDDEN NUMERIC" && $row_desc["name"] != "PICTURE FORMAT")
			{	
				echo '<div style="clear:both; height:0px;"></div><label name="' . $postname . '_label">' . str_replace(" ", "&nbsp;", $label);
				if(trim($data_help) != "")
					echo ' <img icontype="help" src="/css/back/label-help.gif" title="' . trim(stripslashes($data_help)) . '" style=""/>';
				echo '</label>';
			}
			switch($row_desc["name"])
			{
				case "VARCHAR":
				case "EMAIL":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" ' . (($width>0)?'style="width: ' . $width . 'px" class="no_standard_width"':'') . ' value="' . str_replace('€', '&euro;', htmlentities($value)) . '" ' . (($options["length"] > 0)? ' maxlenght="' . $options["length"] . '"' : '') . '>';
					break;
				case "TEXT":
					$value = htmlentities($value);
					echo '<textarea ' . $attr . ' onblur="' . $javascript_onchange . '" name="' . $postname . '" style="' . (($width>0)?'width:' . $width . 'px; ':'') . (($height > 0)? 'height:' . $height . 'px;':'height:90px;') . '">' . $value . '</textarea>';
					break;
				case "DATE":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" name="' . $postname . '" id="' . str_replace(".", "_", $postname) . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' value="' . ((!is_numeric($value))? $value: (($value <= 0)?'':date("d/m/Y" , $value))) . '" AUTOCOMPLETE="OFF">';
					echo '<script>
								$(document).ready(function () { 
									$( "#' . str_replace(".", "_", $postname) . '" ).datepicker({
										changeMonth: true,
										changeYear: true,
										showAnim: "slideDown",
										dateFormat: "dd/mm/yy"
									});
								}); 
							</script>';
					break;
				case "HTML BASIC":
				case "HTML FULL":
					$value = htmlentities($value);
					echo '<div style="float: left;"><textarea ' . $attr . ' id="' . $postname . '" onblur="' . $javascript_onchange . '" name="' . $postname . '" style="width:370px;' . (($height > 0)? 'height:' . $height . 'px;':'height:370px;') . '" htmleditor="1">' . $value . '</textarea></div>';
					echo '<script type="text/javascript">
							$(document).ready(function(){
								tinyMCE.settings = mce_config_array[' . ((trim($options["editor_index"]) == "")? '0':$options["editor_index"]) . '];
								setTimeout(function () {
									   tinyMCE.execCommand(\'mceAddEditor\', false, \'' . $postname . '\');
								}, 300);
								
							});
						</script>';

					break;
				case "HIDDEN VARCHAR":
					$value = htmlspecialchars($value);
				case "HIDDEN ID":
				case "HIDDEN NUMERIC":
				case "PICTURE FORMAT":
					$split = explode('.', $postname);
					echo '<input ' . $attr . ' type="hidden" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . $value . '" masterpic="' . $split[0] . '.' . $options["master_pic_field"] . '" label="' . $label . '">';
					break;
				case "LINK":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . $value . '" blicsmfield="true" blicsmtype="' . $row_desc["name"] . '" link_extern="' . $options["extern"] . '" link_intern="' . $options["intern"] . '" link_file="' . $options["file"] . '">';
					break;
				case "TIME":
					//echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' value="' . $value . '">';
					$value = DateAndTime::format_time($value, $options["hours"], $options["minutes"], $options["seconds"]);
					echo '<div style="float:left;">
								<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" ' . (($options["extern"])?'':'onkeypress="return false;"') . ' name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' value="' . $value . '">
								<div autopost="no" id="' . str_replace('.', '_', $postname) . '_drop" style="display: none; border: 1px solid #CCCCCC; padding: 0px; background-color: #FFFFFF; width: 250px;">
									<div class="field-dromdown-header" >
										<div style="float: left">Time settings</div>
										<img style="float: right; padding-top: 4px;" src="/css/back/icon/twotone/multiply.gif" id="' . str_replace('.', '_', $postname) . '_drop_close"/>
									</div>
									<div style="padding:0px 4px 4px 4px">';
					$timesplit = explode(":", $value);
					$index = 0;
					if($options["hours"])
					{
						echo '<label>Hours</label>
								<select id="' . str_replace('.', '_', $postname) . '_time_hours" style="width: 60px;" onChange="cms2_formfield_timechange(\'' . str_replace('.', '_', $postname) . '\');">';
						for($i = 0 ; $i < 24 ; $i++)
							echo '<option value="' . (($i < 10)?'0': '') . $i . '" ' . (($i==$timesplit[$index])?'selected':'') . '>' . (($i < 10)?'0': '') . $i . '</option>';
						
						echo '</select><br>';
						$index++;
					}
					if($options["minutes"])
					{
						echo '<label>Minutes</label>
								<select id="' . str_replace('.', '_', $postname) . '_time_minutes" style="width: 60px;" onChange="cms2_formfield_timechange(\'' . str_replace('.', '_', $postname) . '\');">';
						for($i = 0 ; $i < 60 ; $i++)
							echo '<option value="' . (($i < 10)?'0': '') . $i . '" ' . (($i==$timesplit[$index])?'selected':'') . '>' . (($i < 10)?'0': '') . $i . '</option>';
						
						echo '</select><br>';
						$index++;
					}	
					if($options["seconds"])
					{
						echo '<label>Seconds</label>
								<select id="' . str_replace('.', '_', $postname) . '_time_seconds" style="width: 60px;" onChange="cms2_formfield_timechange(\'' . str_replace('.', '_', $postname) . '\');">';
						for($i = 0 ; $i < 60 ; $i++)
							echo '<option value="' . (($i < 10)?'0': '') . $i . '" ' . (($i==$timesplit[$index])?'selected':'') . '>' . (($i < 10)?'0': '') . $i . '</option>';
						
						echo '</select><br>';
						$index++;
					}		
					echo		'</div>
								</div>
							</div>';
					formfield::add_dropdown_script(str_replace('.', '_', $postname), str_replace('.', '_', $postname) . '_drop', str_replace('.', '_', $postname) . '_drop_close');
					break;
				case "YESNO":
					if($value)
						echo '<div style="float:left; width:575px"><input ' . $attr . ' type="checkbox" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="checkbox" checked></div>';
					else
						echo '<div style="float:left; width:575px"><input ' . $attr . ' type="checkbox" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="checkbox"></div>';
					break;
				case "NUMERIC":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' value="' . $value . '">';
					break;
				case "FILE":
				case "PICTURE":
				case "VIDEO":
				case "AUDIO":
					$filepath = Files::get_dbfile_path($value);
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . $value . '" blicsmfield="true" blicsmtype="' . $row_desc["name"] . '">';
					//echo '<div id="' . str_replace('.', '_', $postname) . '_filepath" style="border:1px solid #CCCCCC; padding:3px; width:555px; height:14px; float:left;" dropopen="false">' . (($filepath && trim($filepath) != "" && $filepath != 0)?$filepath:"Drag a file from the browser") . '</div>';
								
					break;
					
				case "ENUM LANGUAGE":
					echo '<select ' . $attr . ' onchange="' . $javascript_onchange . '"  name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' >';
					if(!$obligated) echo '<option value=""></option>';
					foreach(mainconfig::$languages as $key => $one_value)
					{
						if($value == $key)
							echo '<option value="' . $key . '" selected="selected">' . $one_value . '</option>';
						else
							echo '<option value="' . $key . '">' . $one_value . '</option>';
					}
					echo '</select>';
					break;
				case "ENUM FROM TABLE":
					echo '<select ' . $attr . ' onchange="' . $javascript_onchange . '"  name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' >';
					if(!$obligated) echo '<option value=""></option>';
					$res_enum = DBConnect::query($options["sql"], __FILE__, __LINE__);
					while($row_enum = mysql_fetch_array($res_enum))
					{
						if($value == $row_enum[0])
							echo '<option value="' . $row_enum[0] . '" selected="selected">' . htmlentities((($row_enum[1] != NULL)?$row_enum[1]:$row_enum[0])) . '</option>';
						else
							echo '<option value="' . $row_enum[0] . '">' . htmlentities((($row_enum[1] != NULL)?$row_enum[1]:$row_enum[0])) . '</option>';
					}
					echo '</select>';
					break;
				case "ENUM PAGES FROM TEMPLATE":
					//we doorlopen heel de pagetree.
					echo '<select ' . $attr . ' onchange="' . $javascript_onchange . '"  name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' >';
					if(!$obligated) echo '<option value=""></option>';
					$items = formfield::enum_pages_from_template(0, 0, $options["template_id"], ((trim($options["min_level"]) != '')?$options["min_level"]:NULL), ((trim($options["max_level"]) != '')?$options["max_level"]:NULL), "");
					foreach($items as $item)
					{
						if($value == $item[0])
							echo '<option value="' . $item[0] . '" selected="selected">' . $item[1] . '</option>';
						else
							echo '<option value="' . $item[0] . '">' . $item[1] . '</option>';
					}
					echo '</select>';
					break;
				case "ENUM PAGES FROM PARENT":
					//we doorlopen heel de pagetree.
					echo '<select ' . $attr . ' onchange="' . $javascript_onchange . '"  name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' >';
					if(!$obligated) echo '<option value=""></option>';
					$res_enum = DBConnect::query("SELECT site_page.id, site_page_lang.name FROM site_page, site_page_lang WHERE site_page_lang.lang_parent_id=site_page.id AND site_page_lang.lang='" . mainconfig::$standardlanguage . "' AND `parent_id`='" . $options["parent_id"] . "' ORDER BY menu_order", __FILE__, __LINE__);
					while($row_enum = mysql_fetch_array($res_enum))
					{
						if($value == $row_enum["id"])
							echo '<option value="' . $row_enum["id"] . '" selected="selected">' . $row_enum["name"] . '</option>';
						else
							echo '<option value="' . $row_enum["id"] . '">' . $row_enum["name"] . '</option>';
					}
					echo '</select>';
					break;
				case "ENUM STATIC":
					//we doorlopen heel de pagetree.
					echo '<select ' . $attr . ' onchange="' . $javascript_onchange . '"  name="' . $postname . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . ' >';
					if(!$obligated) echo '<option value=""></option>';
					$items = explode("#", $options["values"]);
					foreach($items as $item)
					{
						$chomps = explode(":", $item);
						if($value == $chomps[0])
							echo '<option value="' . $chomps[0] . '" selected="selected">' . $chomps[1] . '</option>';
						else
							echo '<option value="' . $chomps[0] . '">' . $chomps[1] . '</option>';
					}
					echo '</select>';
					break;
				case "TABLE":
					$te = new tableeditor(str_replace(".", "_", $postname) . '_' . rand(0, 10000), 980, 800);
					if(trim($the_value) != "")
						$te->set_table_html($the_value);
					else
						$te->set_table_html('<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>');
					$te->set_styles(array("table_bold" => "vet", "table_header" => "titel", "table_header_bold" => "titel vet"));
					$te->set_postname($postname);
					$te->publish(false);
					break;
				case "PASSWORD":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . ' ' . (($options["encrypted"] && trim($value) != "")?' if(this.value.replace(/^s+|s+$/,\'\') == \'\') {$(this).next().css(\'display\', \'inline\'); $(this).css(\'display\', \'none\');}':'') . '" type="password" ' . (($options["encrypted"] && trim($value) != "")?'value="" style="display:none;"':'value="' . $value . '" style="display:inline;"') . ' name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" ' . (($width>0)?'style="width:' . $width . 'px" class="no_standard_width"':'') . '>';
					if($options["encrypted"] && trim($value) != "")
					echo '<div autopost="no" style="float:left; display:inline;">
							<input style="background-color:#CCCCCC;" type="text" value="leave this blank if you dont want to change the password" onclick="$(this).parent(\'div\').css(\'display\', \'none\'); $(\'#' . str_replace('.', '_', $postname) . '\').css(\'display\', \'inline\'); $(\'#' . str_replace('.', '_', $postname) . '\').focus();">
						</div>';
					break;
				case "AUTOCOMPLETE":
					$value = htmlspecialchars($value);
					echo '<input autocomplete="off" ' . $attr . ' oldvalue="' . $value . '" onblur="' . $javascript_onchange . ' ' . (($options["auto_insert"] > 0)?'if($(this).attr(\'oldvalue\') != this.value) { send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&formfield=' .  str_replace(".", "_splitter_", $postname) . '&action=autocompletesave&q=\' + this.value, \'\', null); $(this).attr(\'oldvalue\',this.value);}':'') . '" type="text" name="' . $postname . '" id="' . str_replace(".", "_", $postname) . '" ' . (($width>0)?'style="width: ' . $width . 'px" class="no_standard_width"':'') . ' value="' . $value . '">';
					echo '<script>
							$("#' . str_replace(".", "_", $postname) . '").autocomplete("/ajax.php?sessid=' . session_id() . '&formfield=' .  str_replace(".", "_splitter_", $postname) . '&action=autocompletequery");
					</script>';
					break;
				case "WORDLIST":
					echo '<div style="float:left">';
					echo '<input ' . $attr . ' type="hidden" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . htmlentities($value) . '" wordlist="true">';
					if($options["autocomplete"] > 0)
					{
						echo '<input autocomplete="off" ' . $attr . ' 
								oldvalue="" 
								onblur="' . $javascript_onchange . ' ' . (($options["auto_insert"] > 0)?'if($(this).attr(\'oldvalue\') != this.value) { send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&formfield=' .  str_replace(".", "_splitter_", $postname) . '&action=autocompletesave&q=\' + this.value, \'\', null); $(this).attr(\'oldvalue\',this.value);}':'') . '" 
								type="text" 
								name="' . $postname . '_insert" 
								id="' . str_replace(".", "_", $postname) . '_insert" 
								style="width: 555px;"
								onkeypress="e = event||window.event; var key=e.keyCode || e.which; if(key == 13) {cms_wordlist_add(\'' . str_replace(".", "_", $postname) . '\', \'' . $options["seperation"] . '\'); ' . (($options["auto_insert"] > 0)?'send_ajax_request(\'GET\', \'/ajax.php?sessid=' . session_id() . '&formfield=' .  str_replace(".", "_splitter_", $postname) . '&action=autocompletesave&q=\' + this.value, \'\', null); ':'') . ' this.value=\'\';}">
								<img src="/css/back/icon/twotone/plus.gif" onclick="cms_wordlist_add(\'' . str_replace(".", "_", $postname) . '\', \'' . $options["seperation"] . '\');" style="cursor:pointer;">';
						echo '<script>
								$("#' . str_replace(".", "_", $postname) . '_insert").autocomplete("/ajax.php?sessid=' . session_id() . '&formfield=' .  str_replace(".", "_splitter_", $postname) . '&action=autocompletequery", {selectFirst: false});
						</script>';
					}
					else
					{
						echo '<input ' . $attr . ' onblur="' . $javascript_onchange . ' type="text" name="' . $postname . '_insert" id="' . str_replace(".", "_", $postname) . '_insert" style="width: 555px;" onkeypress="e = event||window.event; var key=e.keyCode || e.which; if(key == 13) {cms_wordlist_add(\'' . str_replace(".", "_", $postname) . '\', \'' . $options["seperation"] . '\'); this.value=\'\';}">
							<img src="/css/back/icon/twotone/plus.gif" onclick="cms_wordlist_add(\'' . str_replace(".", "_", $postname) . '\', \'' . $options["seperation"] . '\');" style="cursor:pointer;">';
					}
					echo '<div class="wordlist" id="' . str_replace(".", "_", $postname) . '_wordlist" seperation="' . $options["seperation"] . '">';
					$values = explode($options["seperation"], $value);
					foreach($values as $one)
					{
						if(trim($one) != "")
						echo '<div><div>' .trim(htmlentities($one)). '</div><img src="/css/back/label-cross.gif" onclick="cms2_wordlist_remove($(this).parent().get(0), \'' . str_replace(".", "_", $postname) . '\', \'' . $options["seperation"] . '\');"/></div>';
					}
					echo '</div>';
					echo '</div><div style="clear:both"></div>';
					break;
				case "WORDDATALIST":
					echo '<div style="float:left">';
					echo '<input ' . $attr . ' type="hidden" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . $value . '" worddatalist="true">';
					echo '<select autocomplete="off" ' . $attr . ' 
							onblur="' . $javascript_onchange . '" 
							type="text" 
							name="' . $postname . '_insert" 
							id="' . str_replace(".", "_", $postname) . '_insert" 
							style="width: 555px;" 
							onkeypress="e = event||window.event; var key=e.keyCode || e.which; if(key == 13) cms_worddatalist_add(\'' . str_replace(".", "_", $postname) . '\');">';
					$sql = $options["sql"];
					if($lang_dep > 0)
						$sql = str_replace("[LANG]", $language, $sql);
					$res = DBConnect::query($sql, __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						echo '<option value="' . $row[0] . '">' . htmlentities($row[1]) . '</option>';
					}
					echo '</select><img src="/css/back/icon/twotone/plus.gif" onclick="cms_worddatalist_add(\'' . str_replace(".", "_", $postname) . '\');" style="cursor:pointer;">';
					echo '<div class="wordlist" id="' . str_replace(".", "_", $postname) . '_worddatalist" seperation="' . $options["seperation"] . '">';
					
					$sql = $options["sql"];
					$tmp = explode("WHERE", $sql);
					if(count($tmp) > 1)
					{
						$tmp2 = explode("ORDER BY", $tmp[1]);
						$sql = $tmp[0] . " WHERE " . $tmp2[0] . " AND `" . $options["idfield"] . "` IN ('" . implode("','",explode(";", $value)) . "')";
						if(count($tmp2) > 1)
							 $sql .= " ORDER BY " . $tmp2[1];
					}
					else
					{
						$tmp2 = explode("ORDER BY", $sql);
						$sql = $tmp2[0] . " WHERE `" . $options["idfield"] . "` IN ('" . implode("','",explode(";", $value)) . "')";
						if(count($tmp2) > 1)
							 $sql .= " ORDER BY " . $tmp2[1];
					}
					if($lang_dep > 0)
						$sql = str_replace("[LANG]", $language, $sql);
					
					$res = DBConnect::query($sql, __FILE__, __LINE__);
					while($row = mysql_fetch_array($res))
					{
						echo '<div dataid="' . $row[0] . '"><div>' .trim($row[1]). '</div><img src="/css/back/label-cross.gif" onclick="cms2_worddatalist_remove($(this).parent().get(0), \'' . str_replace(".", "_", $postname) . '\');"/></div>';
					}
					echo '</div>';
					//if($lang_dep > 0)
					//{
						//tonen van divs die Per lang de options tonen
						foreach(mainconfig::$languages as $abr => $lang)
						{
							echo '<div id="' . str_replace('.', '_', $postname) . '_worddatalist_lang_' . $abr . '" style="display: none;">';
							$sql = str_replace("[LANG]", $abr, $options["sql"]);
							$res = DBConnect::query($sql, __FILE__, __LINE__);
							while($row = mysql_fetch_array($res))
							{
								echo '<div value="' . $row[0] . '" caption="' . htmlentities($row[1]) . '"></div>';
							}
							echo '</div>';
						}
					//}
					echo '</div><div style="clear:both"></div>';
					break;
				case "VIDEOLINK":
					echo '<input ' . $attr . ' onblur="' . $javascript_onchange . '" type="text" name="' . $postname . '" id="' . str_replace('.', '_', $postname) . '" value="' . $value . '" blicsmfield="true" blicsmtype="' . $row_desc["name"] . '">';
					break;
			}
			/*if($row_desc["name"] != "HIDDEN ID" && $row_desc["name"] != "HIDDEN VARCHAR" && $row_desc["name"] != "HIDDEN NUMERIC")
				echo '<br>';*/
		}
		
		static function enum_pages_from_template($parent_id, $level, $template_id, $min_level, $max_level, $prefix)
		{
			$results = array();
			$res_pages = DBConnect::query("SELECT `site_page`.`id`, `parent_id`, `template_id`, `menu_order`, `name` FROM `site_page`, `site_page_lang` WHERE `site_page`.`id`=`site_page_lang`.`lang_parent_id` AND `site_page_lang`.`lang`='" . mainconfig::$standardlanguage . "' AND `parent_id`='" . $parent_id . "' ORDER BY `name`", __FILE__, __LINE__);
			while($row = mysql_fetch_array($res_pages))
			{
				if($row["template_id"] == $template_id)
				{
					//$results[] = array($row["id"], $prefix . $row["menu_name"]);
					if(($min_level == NULL || ($min_level != NULL && $level >= $min_level)) &&
						($max_level == NULL || ($max_level != NULL && $level <= $max_level)))
					{
						$results[] = array($row["id"], $prefix . $row["name"]);
					}
				}
				$new_results = formfield::enum_pages_from_template($row["id"], $level+1, $template_id, $min_level, $max_level, $prefix . $row["name"] . ' > ');
				foreach($new_results as $new_result)
					$results[] = $new_result;
			}
			return $results;
		}
		
		static function add_dropdown_script($id_field, $id_div, $id_close)
		{
			echo '<script>
						//check if in a dialog
						
						
						//document.getElementById(\'' . $id_div . '\').style.top = position.top + $(\'#' . $id_field . '\').height() + 6;
						//document.getElementById(\'' . $id_div . '\').style.left = position.left;
						
						$(\'#' . $id_field . '\').click(function(){
							if(document.getElementById(\'' . $id_field . '\').dropopen != \'true\')
							{
								//var pos_dialog = $(\'#' . $id_div . '\').closest(\'.ui-dialog\').offset();
								//var position = $(this).position();
								//position.top += 20;
								//position.top += $(window).scrollTop();
								/*if(pos_dialog != null)
								{
									position.left -= pos_dialog.left;
									position.top -= pos_dialog.top + 27;
								}*/
								//$(\'#' . $id_div . '\').position(position);
								
								$(\'#' . $id_div . '\').animate( { height: "show" } );
								document.getElementById(\'' . $id_field . '\').dropopen = \'true\';
							}
							else
							{
								$(\'#' . $id_div . '\').animate( { height: "hide" } );
								document.getElementById(\'' . $id_field . '\').dropopen = \'false\';
							}
						});
						$(\'#' . $id_close . '\').click(function(){
							if(document.getElementById(\'' . $id_field . '\').dropopen == \'true\')
							{
								$(\'#' . $id_div . '\').animate( { height: "hide" } );
								document.getElementById(\'' . $id_field . '\').dropopen = \'false\';
							}
						});
				</script>';
		}
		
		/*static function create_swf_upload($field_id, $fieldname, $extentions)
		{
			$str_extentions = "*.*";
			if(count($extentions) > 0 && trim($extentions[0]) != "")
			 	$str_extentions = "*." . implode("; *.", $extentions);
?>
			<form id="form1" action="index.php" method="post" enctype="multipart/form-data">
				<span class="contentheader" id="<?php echo $field_id . '_'; ?>fsUploadProgress"></span>
				<div style="clear:both; lin-height: 28px; height: 28px; overflow:hidden;">
					<div style="float:left;">
						<span id="<?php echo $field_id . '_'; ?>spanButtonPlaceHolder" style="height: 28px; width:100px;"></span>
					</div>
					<div style="float:left;">
						<input id="<?php echo $field_id . '_'; ?>btnCancel" type="button" value="Cancel Upload" onclick="<?php echo $field_id . '_'; ?>swfu.cancelQueue();" style="width:100px; margin-left:4px; margin-top:0px; " />
					</div>
				<div style="clear:both; "></div>
				</div>
			</form>
			<script type="text/javascript">
				var <?php echo $field_id . '_'; ?>swfu;
				new function(){
					var <?php echo $field_id . '_swfu_'; ?>settings = {
						flash_url : "/plugins/swfupload/swfupload.swf",
						upload_url: "/ajax.php?sessid=<?php echo session_id();?>&formfield=<?php echo str_replace('.', '_splitter_',$fieldname);?>&action=fileupload",
						post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
						file_size_limit : "100 MB",
						file_types : "<?php echo $str_extentions; ?>",
						file_types_description : "All Files",
						file_upload_limit : 100,
						file_queue_limit : 1,
						custom_settings : {
							progressTarget : "<?php echo $field_id . '_'; ?>fsUploadProgress",
							cancelButtonId : "<?php echo $field_id . '_'; ?>btnCancel"
						},
						debug: false,
			
						// Button settings
						button_image_url: "/plugins/swfupload/Button 120x20.png",
						button_width: "100",
						button_height: "27",
						button_placeholder_id: "<?php echo $field_id . '_'; ?>spanButtonPlaceHolder",
						button_text: '<span class="theFont">Upload File</span>',
						button_text_style: ".theFont { font-size: 11; font-family: verdana; color: #FFFFFF; font-weight: bold;}",
						button_text_left_padding: 10,
						button_text_top_padding: 4,
						button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE, 
						button_disabled : false, 
						button_cursor : SWFUpload.CURSOR.HAND, 
						// The event handler functions are defined in handlers.js
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : uploadSuccessFilefield,
						upload_complete_handler : uploadComplete,
						queue_complete_handler : queueComplete	// Queue plugin event
					};
			
					<?php echo $field_id . '_'; ?>swfu = new SWFUpload(<?php echo $field_id . '_swfu_'; ?>settings);
					<?php echo $field_id . '_'; ?>swfu.field_id = "<?php echo $field_id; ?>";
				};
			</script>
<?php
		}*/
		
		static function handle_ajax_new($type, $action, $xml)
		{
			$doc = new SimpleXMLElement(urldecode(stripslashes($xml)));
			switch($type)
			{
				case "PICTURE":
					switch($action)
					{
						case "picInfo":
							$relPath = Files::get_dbfile_path(((string)$doc->pic_id[0][0]));
							$absPath = $_SERVER['DOCUMENT_ROOT'] . substr($relPath, 1);
							$link = "http://" . $_SERVER['HTTP_HOST'] . $relPath;
							$pathinfo = pathinfo($relPath);
							echo '<img class="systemthumb" src="' . Pictures::system_thumb($relPath) . '" style="float: left; width: 120px; height: 120px; margin-right: 8px;"/><div style="float: left; width: 445px;">';	
							
							//the formats
							//we checken of er formats zijn. En of deze al gemaakt zijn
							$field_name = (string)$doc->field_id[0][0];
							$tmp = explode('.', $field_name);
							$field_table = $tmp[0];
							$field_name = $tmp[1];
							
							$res = DBConnect::query("SELECT * FROM sys_database_meta WHERE `tablename`='" . $field_table . "' AND `datadesc`='22'", __FILE__, __LINE__);
							$one_format = false;
							while($row = mysql_fetch_array($res))
							{
								//checken of het wel voor deze pic is
								$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
								if($options["master_pic_field"] == $field_name)
								{
									//--------------------------------------------------------------------------
									$derpath = str_replace("/userfiles/", "picformats/", $relPath);
									$derpath_parts = pathinfo($derpath);
									//plaatsen van suffix id_name
									$derpath_parts['filename'] = $derpath_parts['filename'] . '-' . $field_table . '_' . $field_name . '-' . $row["fieldname"];
									$derpath = $derpath_parts['dirname'] . '/' . $derpath_parts['filename'] . '.' . $derpath_parts['extension'];
									if(!file_exists($derpath))
									{
										//creëren van thumb
										Pictures::create_thumb(str_replace('/userfiles/', 'userfiles/', $relPath), $derpath, $options["format_x"],$options["format_y"], $options["watermark"]);
									}								
									//DB aanvullen
									$the_id = NULL;
									$res_derived = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `file_id`='" . (string)$doc->pic_id[0][0] . "' AND `thumb_meta`='" . $row["id"] . "'", __FILE__, __LINE__);
									if($row_derived = mysql_fetch_array($res_derived))
									{
										//even updaten
										DBConnect::query("UPDATE `site_files_derived` SET `path`='" . addslashes('/' . $derpath) . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
										$the_id = $row_derived["id"];
									}
									else
									{
										DBConnect::query("INSERT INTO `site_files_derived` (`id`, `file_id`, `path`, `name`, `type`, `thumb_meta`) VALUES ('', '" . (string)$doc->pic_id[0][0] . "', '" . addslashes('/' . $derpath) . "', '" . $row["fieldlabel"] . "', 'thumb', '" . $row["id"] . "')", __FILE__, __LINE__);
										$the_id = DBConnect::get_last_inserted('site_files_derived', 'id');
									}
									
									//javascriptje dat de derived field invult
									?>
                                    	<script language="javascript">
                                        	$('input[name="<?php echo $row["tablename"] . '.' . $row["fieldname"]; ?>"]').val('<?php echo $the_id;?>');
                                        </script>
                                    <?php
									$fieldtoedit[$row["tablename"] . '_' . $row["fieldname"]] = $the_id;
									
									//echo button
									echo '<p><div class="computerdata man_button" style="margin: 0px 4px 2px 0px;" onclick="cms2_open_pic_edit_new(\'' . $the_id . '\');">edit ' . $row["fieldlabel"] .  '</div></p>';
									$one_format = true;
								}
							}
							if($one_format)
								echo '<div style="clear:both; height: 0px;"></div>';
							
							//main info
							echo '<p class="computerdata"><b>Image size:</b> ' . Pictures::get_pic_width($absPath) . ' x ' . Pictures::get_pic_height($absPath) . '</p>';
							echo '<p class="computerdata"><b>File size:</b> ' . Files::formatBytes(filesize($absPath)) . '</p>';
							echo '<p class="computerdata"><b>link: </b><a style="darklink" href="' . $link . '" target="blank">' . $link . '</a></p>';
							//functionalities
							echo '<div class="computerdata formField_file_clear">clear picture</div>';
							
							echo '</div>';
							break;
					}
				case "AUDIO":
					switch($action)
					{
						case "audioInfo":
							$relPath = Files::get_dbfile_path(((string)$doc->audio_id[0][0]));
							$absPath = $_SERVER['DOCUMENT_ROOT'] . substr($relPath, 1);
							$link = "http://" . $_SERVER['HTTP_HOST'] . $relPath;
							$pathinfo = pathinfo($relPath);
							//main info
							echo '<p class="computerdata"><b>File size:</b> ' . Files::formatBytes(filesize($absPath)) . '</p>';
							echo '<p class="computerdata"><b>link: </b><a style="darklink" href="' . $link . '" target="blank">' . $link . '</a></p>';
							//functionalities
							echo '<div class="computerdata formField_file_clear">clear audio</div>';
							
							echo '</div>';
							break;
					}
					break;
				case "FILE":
					switch($action)
					{
						case "fileInfo":
							$relPath = Files::get_dbfile_path(((string)$doc->audio_id[0][0]));
							$absPath = $_SERVER['DOCUMENT_ROOT'] . substr($relPath, 1);
							$link = "http://" . $_SERVER['HTTP_HOST'] . $relPath;
							$pathinfo = pathinfo($relPath);
							//main info
							echo '<p class="computerdata"><b>File size:</b> ' . Files::formatBytes(filesize($absPath)) . '</p>';
							echo '<p class="computerdata"><b>link: </b><a style="darklink" href="' . $link . '" target="blank">' . $link . '</a></p>';
							//functionalities
							echo '<div class="computerdata formField_file_clear">clear file</div>';
							
							echo '</div>';
							break;
					}
					break;
				case "VIDEOLINK":
					switch($action)
					{
						case "videoInfo":
							$link = (string)$doc->videolink[0][0];
							Video::echoVideoInfoBack($link);
						break;
					}
					break;
			}
		}
		
		static function handle_ajax($field_id, $action)
		{
			debug::message($action); 
			$field_id = str_replace('_splitter_', '.', $field_id);
			switch($action)
			{
				case 'echofilepath':
					echo Files::get_dbfile_path($_GET["file_id"]);
					break;
				case 'fileupload':
					//we zoeken naar het veldoptie 'direct upload folder'
					$chomps = explode(".", $field_id);
					$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
					//plaatsen van de nieuwe file
					if (is_uploaded_file($_FILES['Filedata']['tmp_name']))	 
					{
						$uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "userfiles/" . $options["direct_upload_folder"];
						//als we ergens anders zitten in de minibrowser dan gaan we daar uploaden
						if(trim($_SESSION["popup_vars"]["mb_" . $chomps[0] . "_" . $chomps[1]]["folder"]) != "")
							$uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "/" . $_SESSION["popup_vars"]["mb_" . $chomps[0] . "_" . $chomps[1]]["folder"];
						
						$uploadDirectory = str_replace('//', '/', $uploadDirectory);
						if(!is_dir(str_replace('//', '/', "userfiles/" . $options["direct_upload_folder"])))
						{
							mkdir(str_replace('//', '/', "userfiles/" . $options["direct_upload_folder"]), 0777, true);
						}
						$uploadFile = $uploadDirectory . '/' . basename($_FILES['Filedata']['name']);
						$uploadFile = Files::make_unique($uploadFile);
						
						move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadFile);
						//inserten in db
						DBConnect::query("INSERT INTO `site_files` (`id`, `path`) VALUES ('', '" . addslashes(str_replace($_SERVER['DOCUMENT_ROOT'], '/', $uploadFile)) . "')");
						$newid = DBConnect::get_last_inserted('site_files', 'id');
						
						echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $uploadFile) . "_splitter_" . $newid;
						if($options["mini_browser"])
							echo '_splitter_' . 'mb_' . str_replace('.', '_', $field_id);
						
						//als het een picture is dan een thumb maken
						/*$file_info = getimagesize($uploadFile);
						if(!empty($file_info))
							Pictures::create_system_thumb($uploadFile);*/
						Pictures::system_thumb($uploadFile);
					}
					break;
				case 'file_select':
					$res_file = DBConnect::query("SELECT * FROM `site_files` WHERE `id`='" . $_GET["file_id"] . "'", __FILE__, __LINE__);
					$row_file = mysql_fetch_array($res_file);
					if(!$row_file)
						break;
					$path_parts = pathinfo(stripslashes($row_file["path"]));
					echo '<img src="' . Files::file_type_icon(stripslashes($row_file["path"]), true) . '" style="float:left; margin-right: 8px; margin-bottom: 4px;">';
					echo '<p style="padding:8px; line-height: 16px;">';
					echo '<b>File:</b>&nbsp;<a href="javascript:cms2_open_file(\'' . ((substr($row_file["path"], 0, 1) == '/')?stripslashes(substr($row_file["path"],1)):stripslashes($row_file["path"])) . '\', \'' . $path_parts['extension'] . '\', null);">View</a>&nbsp;
						<a href="javascript:cms2_open_file_options(\'' . stripslashes($row_file["path"]) . '\');">Edit Info</a><br>';
					
					//we zoeken naar field meta
					$chomps = explode(".", $field_id);
					$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					if($row["datadesc"] == 14)
					{
						//zoeken naar de PICTURE FORMAT FIELDS
						$res_fromats = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `datadesc`='22'", __FILE__, __LINE__);
						
						$fieldtoedit = array();
						while($row_formats = mysql_fetch_array($res_fromats))
						{
							//zoeken naar de opties
							$options = data_description::options_convert_to_array($row_formats["datadesc"], $row_formats["data_options"]);
							if($options["master_pic_field"] == $chomps[1])
							{
								//zoeken naar pic format. Als niet bestaat: creëren
								$path = str_replace("/userfiles/", "picformats/", stripslashes($row_file["path"]));
								$path_parts = pathinfo($path);
								//plaatsen van suffix id_name
								$path_parts['filename'] = $path_parts['filename'] . '-' . str_replace('.', '_', $field_id) . '-' . $row_formats["fieldname"];
								$path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $path_parts['extension'];
								if(!file_exists($path))
								{
									//creëren van thumb
									Pictures::create_thumb(str_replace('/userfiles/', 'userfiles/', stripslashes($row_file["path"])), $path, $options["format_x"],$options["format_y"], $options["watermark"]);
								}								
								//DB aanvullen
								$the_id = NULL;
								$res_derived = DBConnect::query("SELECT * FROM `site_files_derived` WHERE `file_id`='" . $row_file["id"] . "' AND `thumb_meta`='" . $row_formats["id"] . "'", __FILE__, __LINE__);
								if($row_derived = mysql_fetch_array($res_derived))
								{
									//even updaten
									DBConnect::query("UPDATE `site_files_derived` SET `path`='" . addslashes('/' . $path) . "' WHERE `id`='" . $row_derived["id"] . "'", __FILE__, __LINE__);
									$the_id = $row_derived["id"];
								}
								else
								{
									DBConnect::query("INSERT INTO `site_files_derived` (`id`, `file_id`, `path`, `name`, `type`, `thumb_meta`) VALUES ('', '" . $row_file["id"] . "', '" . addslashes('/' . $path) . "', '" . $row_formats["fieldlabel"] . "', 'thumb', '" . $row_formats["id"] . "')", __FILE__, __LINE__);
									$the_id = DBConnect::get_last_inserted('site_files_derived', 'id');
								}
								
								$fieldtoedit[$row_formats["tablename"] . '_' . $row_formats["fieldname"]] = $the_id;
								
								echo '<b>' . $row_formats["fieldlabel"] . ':</b>&nbsp;<a href="javascript:cms2_open_file(\'' . $path . '\', \'' . $path_parts['extension'] . '\', null);">View</a>&nbsp;
									<a href="javascript:cms2_open_pic_edit_new(\'' . $the_id . '\');">Edit</a><br>';
							}
						}
						//script die de picformat velden vult
						echo '<script>';
						foreach($fieldtoedit as $fieldid => $fieldvalue)
						{
							echo '$("#' . $fieldid . '").val(\'' . $fieldvalue . '\');';
						}
						echo '</script>';
					}
					echo '</p>';
					break;
				case "autocompletequery":
					debug::message("inautocomplete");
					$chomps = explode(".", $field_id);
					$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
					if(isset($options["auto_table"]))
					{
						$options["table"] = $options["auto_table"];
						$options["field"] = $options["auto_field"];
						$options["field_rank"] = $options["auto_field_rank"];
					}
					if(DBConnect::check_if_table_exists($options["table"]))
					{
						$res = DBConnect::query("SELECT `" . $options["field"] . "` FROM `" . $options["table"] . "` WHERE `" . $options["field"] . "` LIKE '" . addslashes(urldecode($_GET["q"])) . "%' ORDER BY `" . $options["field_rank"] . "` DESC LIMIT 0, 50", __FILE__, __LINE__);
						//debug::message("SELECT `" . $options["field"] . "` FROM `" . $options["table"] . "` WHERE `" . $options["field"] . "` LIKE '" . addslashes(urldecode($_GET["q"])) . "%' ORDER BY `" . $options["field_rank"] . "` DESC LIMIT 0, 50");
						$first = true;
						while($row = mysql_fetch_array($res))
						{
							echo ((!$first)?"\n":'') . stripslashes($row[$options["field"]]);
							$first = false;
						}
					}
					break;
				case "autocompletesave":
					$chomps = explode(".", $field_id);
					$res = DBConnect::query("SELECT * FROM `sys_database_meta` WHERE `tablename`='" . $chomps[0] . "' AND `fieldname`='" . $chomps[1] . "'", __FILE__, __LINE__);
					$row = mysql_fetch_array($res);
					$options = data_description::options_convert_to_array($row["datadesc"], $row["data_options"]);
					if(isset($options["auto_table"]))
					{
						$options["table"] = $options["auto_table"];
						$options["field"] = $options["auto_field"];
						$options["field_rank"] = $options["auto_field_rank"];
					}
					if(DBConnect::check_if_table_exists($options["table"]))
					{
						$ranking = DBConnect::check_if_column_exists($options["table"], $options["field_rank"]);
						
						$res = DBConnect::query("SELECT * FROM `" . $options["table"] . "` WHERE `" . $options["field"] . "` LIKE '" . addslashes(urldecode($_GET["q"])) . "'", __FILE__, __LINE__);
						if($row = mysql_fetch_array($res))
						{
							if($ranking)
								DBConnect::query("UPDATE `" . $options["table"] . "` SET `" . $options["field"] . "`='" . addslashes(urldecode($_GET["q"])) . "', `" . $options["field_rank"] . "`=`" . $options["field_rank"] . "`+1 WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
							else
								DBConnect::query("UPDATE `" . $options["table"] . "` SET `" . $options["field"] . "`='" . addslashes(urldecode($_GET["q"])) . "' WHERE `id`='" . $row["id"] . "'", __FILE__, __LINE__);
						}
						else
						{
							if($ranking)
								DBConnect::query("INSERT INTO `" . $options["table"] . "` (`" . $options["field"] . "`, `" . $options["field_rank"] . "`) VALUES('" . addslashes(urldecode($_GET["q"])) . "', '1')", __FILE__, __LINE__);
							else
								DBConnect::query("INSERT INTO `" . $options["table"] . "` (`" . $options["field"] . "`) VALUES('" . addslashes(urldecode($_GET["q"])) . "')", __FILE__, __LINE__);
						}
					}
					break;
			}
		}
	}
?>