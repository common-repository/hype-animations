<?
add_action( "admin_init", 'hypeanimations_panel_upload' );
function hypeanimations_panel_upload() {	
	global $wpdb;
	global $version;
	global $table_name;
	$upload_dir = wp_upload_dir();
	$anims_dir=$upload_dir['basedir'].'/hypeanimations/';
	if (isset($_FILES['file'])) {
		$uploaddir = $anims_dir.'tmp/';
		$uploadfinaldir = $anims_dir;
		$uploadfile = $uploaddir . basename($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			WP_Filesystem();
			$unzipfile = unzip_file( $uploadfile, $uploaddir);
			if (file_exists($uploadfile)) {
				unlink($uploadfile);
			}
			if (file_exists($uploaddir.'/config.xml')) {
				unlink($uploaddir.'/config.xml');
			}
			$files = scandir($uploaddir.'Assets/');
			for ($i=0;isset($files[$i]);$i++) {
				if (preg_match('~.html~',$files[$i])) {
					$actfile=explode('.html',$files[$i]);
					$maxid = $wpdb->get_var("SELECT id FROM ".$table_name." ORDER BY id DESC LIMIT 1");
					if ($maxid>0) {
						$maxid=$maxid+1;
					}
					else { 
						$maxid=1;
					}
					$insert = $wpdb -> query("INSERT ".$table_name." SET id='',nom='".$actfile[0]."',slug='".str_replace(' ','',strtolower($actfile[0]))."',code='',updated='".time()."',container='none'");
					$lastid = $wpdb->insert_id;
					$jsfiles = scandir($uploaddir.'Assets/'.$actfile[0].'.hyperesources/');
					for ($j=0;isset($jsfiles[$j]);$j++) {
						if (preg_match('~_hype_generated_script.js~',$jsfiles[$j])) {
							$jshandle = fopen($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], "r");
							if ($jshandle) {
								$newfile='';
								while (($jsline = fgets($jshandle)) !== false) {
									$jsline=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$lastid,$jsline);
									$newfile.=$jsline;
								}
								//reecrire
								unlink($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j]);
								file_put_contents($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], $newfile);
							}
						}
					}
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.hyperesources/')) {
						rename($uploaddir.'Assets/'.$actfile[0].'.hyperesources/', $uploadfinaldir.$lastid.'/');
					}
					$agarder1='';
					$recordlines=0;
					$handle = fopen($uploaddir.'Assets/'.$actfile[0].'.html', "r");
					if ($handle) {
						while (($line = fgets($handle)) !== false) {
							$line=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$lastid,$line);
							if (preg_match('~<div id="~',$line)) {
								$recordlines=1;
							}
							if ($recordlines==1) {
								$agarder1.=$line;
							}
							if (preg_match('~div>~',$line)) {
								$recordlines=0;
							}
							//echo htmlentities($line);
						}

						fclose($handle);
					} else {
						//echo 'error';
					}
					$update = $wpdb -> query("UPDATE ".$table_name." SET code='".addslashes(htmlentities($agarder1))."' WHERE id='".$lastid."' LIMIT 1");
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.html')) {
						unlink($uploaddir.'Assets/'.$actfile[0].'.html');
					}
					if (file_exists($uploaddir.'Assets/')) {
						hyperrmdir($uploaddir.'Assets/');
					}
				}
			}
		} 
		else {
			echo "Erreur";
		}
		//print_r($_FILES);
		echo $lastid;
		exit();
	}
}
add_action( "admin_footer", 'add_hypeanimations_shortcode_newbutton_footer' );
function add_hypeanimations_shortcode_newbutton_footer() { 
	global $table_name;
	global $wpdb;
	$output='	
	<div id="oModal1" class="oModal">
		<div>	
			<header>
				<a href="#fermer"  class="droitefermer">X</a>
				<h2>'.__( 'Upload new animation' , 'hype-animations' ).'</h2>
			</header>
			<section>
				<form action="" class="dropzone" id="hypeanimdropzone" method="post" accept-charset="utf-8" enctype="multipart/form-data">
				</form>
			</section>
		</div>
	</div>
	


	<script>
	jQuery(".droitefermer").click(function(e) {
		window.location.href=window.location.href.substr(0, window.location.href.indexOf("#"));
	});
	</script>
	';
	echo $output;
}
function hypeanimations_panel() {	
	global $wpdb;
	global $version;
	global $table_name;
	$upload_dir = wp_upload_dir();
	$anims_dir=$upload_dir['basedir'].'/hypeanimations/';
	echo '<br><h1>Hype Animations (version '.$version.')</h1>
	<p>&nbsp;</p>
	<div class="eraabout"><h2>'.__( 'About' , 'hype-animations' ).' <a href="http://www.eralion.com" target="_blank" class="eralink">ERALION.com</a></h2><div class="hypeanimbloc">'.__( 'If you have any problem with this plugin, you can' , 'hype-animations' ).' <a href="http://www.eralion.com/contactez-nous/" target="_blank">'.__( 'contact us' , 'hype-animations' ).'</a>.<br>'.__( 'We can also create customs plugins and others web services.' , 'hype-animations' ).'</div></div>
	<h2>'.__( 'Add new animation' , 'hype-animations' ).'</h2>
	<div class="hypeanimbloc">
	'.__( 'Upload a .OAM file exported by Tumult Hype and it will generate a shortcode that you will can insert everywhere you want on your website.' , 'hype-animations' ).'<br><br>
	<a href="#oModal1" class="button" id="add_hypeanimations_shortcode_newbutton" style="outline: medium none !important; cursor: pointer;" ><i class="dashicons-before dashicons-plus-alt"></i> '.__( 'Upload new animation' , 'hype-animations' ).'</a>
	</div>';
	if ($_GET['delete']>0) {
		$animtitle = $wpdb->get_var("SELECT nom FROM ".$table_name." WHERE id='".ceil($_GET['delete'])."' LIMIT 1");
		$delete = $wpdb -> query("DELETE FROM ".$table_name." WHERE id='".ceil($_GET['delete'])."' LIMIT 1");
		hyperrmdir($anims_dir.ceil($_GET['delete']).'/');
		if ($animtitle!='') {
			echo '<p>&nbsp;</p><p><span style="padding:10px;color:#FFF;background:#cc0000;">'.$animtitle.' has been deleted !</style></p>';
		}
	}
	/*if (isset($_FILES['file'])) {
		$uploaddir = $anims_dir.'tmp/';
		$uploadfinaldir = $anims_dir;
		$uploadfile = $uploaddir . basename($_FILES['file']['name']);
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			WP_Filesystem();
			$unzipfile = unzip_file( $uploadfile, $uploaddir);
			if (file_exists($uploadfile)) {
				unlink($uploadfile);
			}
			if (file_exists($uploaddir.'/config.xml')) {
				unlink($uploaddir.'/config.xml');
			}
			$files = scandir($uploaddir.'Assets/');
			for ($i=0;isset($files[$i]);$i++) {
				if (preg_match('~.html~',$files[$i])) {
					$actfile=explode('.html',$files[$i]);
					$maxid = $wpdb->get_var("SELECT id FROM ".$table_name." ORDER BY id DESC LIMIT 1");
					if ($maxid>0) {
						$maxid=$maxid+1;
					}
					else { 
						$maxid=1;
					}
					$insert = $wpdb -> query("INSERT ".$table_name." SET id='',nom='".$actfile[0]."',slug='".str_replace(' ','',strtolower($actfile[0]))."',code='',updated='".time()."',container='none'");
					$lastid = $wpdb->insert_id;
					$jsfiles = scandir($uploaddir.'Assets/'.$actfile[0].'.hyperesources/');
					for ($j=0;isset($jsfiles[$j]);$j++) {
						if (preg_match('~_hype_generated_script.js~',$jsfiles[$j])) {
							$jshandle = fopen($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], "r");
							if ($jshandle) {
								$newfile='';
								while (($jsline = fgets($jshandle)) !== false) {
									$jsline=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$lastid,$jsline);
									$newfile.=$jsline;
								}
								//reecrire
								unlink($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j]);
								file_put_contents($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], $newfile);
							}
						}
					}
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.hyperesources/')) {
						rename($uploaddir.'Assets/'.$actfile[0].'.hyperesources/', $uploadfinaldir.$lastid.'/');
					}
					$agarder1='';
					$recordlines=0;
					$handle = fopen($uploaddir.'Assets/'.$actfile[0].'.html', "r");
					if ($handle) {
						while (($line = fgets($handle)) !== false) {
							$line=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$lastid,$line);
							if (preg_match('~<div id="~',$line)) {
								$recordlines=1;
							}
							if ($recordlines==1) {
								$agarder1.=$line;
							}
							if (preg_match('~div>~',$line)) {
								$recordlines=0;
							}
							//echo htmlentities($line);
						}

						fclose($handle);
					} else {
						//echo 'error';
					}
					$update = $wpdb -> query("UPDATE ".$table_name." SET code='".addslashes(htmlentities($agarder1))."' WHERE id='".$lastid."' LIMIT 1");
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.html')) {
						unlink($uploaddir.'Assets/'.$actfile[0].'.html');
					}
					if (file_exists($uploaddir.'Assets/')) {
						hyperrmdir($uploaddir.'Assets/');
					}
				}
			}
		} 
		else {
			echo "Erreur";
		}
		//print_r($_FILES);
	}*/
	if (isset($_FILES['updatefile']) && $_POST['dataid']>0) {
		$actdataid=ceil($_POST['dataid']);
		$uploaddir = $anims_dir.'tmp/';
		$uploadfinaldir = $anims_dir;
		$uploadfile = $uploaddir . basename($_FILES['updatefile']['name']);
		if (move_uploaded_file($_FILES['updatefile']['tmp_name'], $uploadfile)) {
			WP_Filesystem();
			$unzipfile = unzip_file( $uploadfile, $uploaddir);
			if (file_exists($uploadfile)) {
				unlink($uploadfile);
			}
			if (file_exists($uploaddir.'/config.xml')) {
				unlink($uploaddir.'/config.xml');
			}
			$files = scandir($uploaddir.'Assets/');
			for ($i=0;isset($files[$i]);$i++) {
				if (preg_match('~.html~',$files[$i])) {
					$actfile=explode('.html',$files[$i]);
					$maxid = $wpdb->get_var("SELECT id FROM ".$table_name." ORDER BY id DESC LIMIT 1");
					if ($maxid>0) {
						$maxid=$maxid+1;
					}
					else { 
						$maxid=1;
					}
					if (file_exists($uploadfinaldir.$actdataid.'/')) {
						hyperrmdir($uploadfinaldir.$actdataid.'/');
					}
					$jsfiles = scandir($uploaddir.'Assets/'.$actfile[0].'.hyperesources/');
					for ($j=0;isset($jsfiles[$j]);$j++) {
						if (preg_match('~_hype_generated_script.js~',$jsfiles[$j])) {
							$jshandle = fopen($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], "r");
							if ($jshandle) {
								$newfile='';
								while (($jsline = fgets($jshandle)) !== false) {
									$jsline=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$actdataid,$jsline);
									$newfile.=$jsline;
								}
								//reecrire
								unlink($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j]);
								file_put_contents($uploaddir.'Assets/'.$actfile[0].'.hyperesources/'.$jsfiles[$j], $newfile);
							}
						}
					}
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.hyperesources/')) {
						rename($uploaddir.'Assets/'.$actfile[0].'.hyperesources/', $uploadfinaldir.$actdataid.'/');
					}
					$agarder1='';
					$recordlines=0;
					$handle = fopen($uploaddir.'Assets/'.$actfile[0].'.html', "r");
					if ($handle) {
						while (($line = fgets($handle)) !== false) {
							$line=str_replace($actfile[0].'.hyperesources',$upload_dir['baseurl'].'/hypeanimations/'.$actdataid,$line);
							if (preg_match('~<div id="~',$line)) {
								$recordlines=1;
							}
							if ($recordlines==1) {
								$agarder1.=$line;
							}
							if (preg_match('~div>~',$line)) {
								$recordlines=0;
							}
							//echo htmlentities($line);
						}

						fclose($handle);
					} else {
						//echo 'error';
					}
					$update = $wpdb -> query("UPDATE ".$table_name." SET code='".addslashes(htmlentities($agarder1))."',updated='".time()."' WHERE id='".$actdataid."' LIMIT 1");
					if (file_exists($uploaddir.'Assets/'.$actfile[0].'.html')) {
						unlink($uploaddir.'Assets/'.$actfile[0].'.html');
					}
					if (file_exists($uploaddir.'Assets/')) {
						hyperrmdir($uploaddir.'Assets/');
					}
					$hypeupdated=$actdataid;
					$hypeupdatetd_title=$actfile[0];
				}
			}
		} 
		else {
			echo "Erreur";
		}
		//print_r($_FILES);
	}
	echo '<p style="clear:both">&nbsp;</p>
	'.($hypeupdated>0 ? '<p><span style="padding:10px;color:#FFF;background:#009933;">'.$hypeupdatetd_title.' has been updated !</style></p><p>&nbsp;</p>' : '').'
	<h2>'.__( 'Manage animations' , 'hype-animations' ).'</h2>
	<table cellpadding="0" cellspacing="0" id="hypeanimations">
		<thead>
			<tr>
				<th>Animation</th>
				<th>Shortcode</th>
				<th>Options</th>
				<th>'.__( 'Last file update' , 'hype-animations' ).'</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>';
		$sql = "SELECT id,nom,slug,updated,container,containerclass FROM ".$table_name." ORDER BY id DESC";
		$result = $wpdb->get_results($sql);
		foreach( $result as $results ) {
			echo '<tr><td>'.$results->nom.'</td><td><pre>[hypeanimations_anim id="'.$results->id.'"]</pre></td><td>'.__( 'Add a container around the animation' , 'hype-animations' ).' : <select class="hypeanimations_container" name="container">
<option value="none" '.($results->container=='none' ? 'selected' : '').'>'.__( 'No' , 'hype-animations' ).'</option>
<option value="div" '.($results->container=='div' ? 'selected' : '').'>&lt;div&gt;</option>
<option value="iframe" '.($results->container=='iframe' ? 'selected' : '').'>&lt;iframe&gt;</option>
</select> <input type="button" value="'.__( 'Update' , 'hype-animations' ).'" class="updatecontainer" data-id="'.$results->id.'"><div '.($results->container=='none' ? 'style="display:none;"' : '').'>'.__( 'Container CSS class' , 'hype-animations' ).' : <input type="text" name="class" placeholder="Myclass1 Myclass2" value="'.$results->containerclass.'"></div></td><td>'.($results->updated==0 ? '<em>'.__( 'No data' , 'hype-animations' ).'</em>' : date('d/m/Y',$results->updated).'<br>'.date('H:i:s',$results->updated)).'</td><td><a href="admin.php?page=hypeanimations_panel&update='.$results->id.'" class="animupdate" data-id="'.$results->id.'">'.__( 'Update' , 'hype-animations' ).'</a> <a href="admin.php?page=hypeanimations_panel&delete='.$results->id.'" class="animdelete">'.__( 'Delete' , 'hype-animations' ).'</a></td></tr>';
		}
	echo '</tbody>
	</table>
	

	
	<script>
	jQuery(document).ready(function(jQuery){
		jQuery(".hypeanimations_container").change(function(){
			if (jQuery(this).val()!="none") {
				jQuery(this).parent().find("div").css("display","block");
			}
			else {
				jQuery(this).parent().find("div").css("display","none");
			}
		});
		jQuery(".updatecontainer").click(function(e){
			e.preventDefault();
			actbutton=jQuery(this);
			actdataid=actbutton.attr("data-id");
			actcontainer=actbutton.parent().find("select[name=container]").val();
			actcontainerclass=actbutton.parent().find("input[name=class]").val();
			jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				data: {
					"action": "hypeanimations_updatecontainer",
					"dataid": actdataid,
					"container": actcontainer,
					"containerclass": actcontainerclass
				}					
			}).done(function( msg ) {
				resp=msg.response;
				if (resp=="ok") {
					if (jQuery(".hypeanimupdated[data-id="+actdataid+"]").length ) { }
					else {
						actbutton.after(\'<div class="hypeanimupdated" data-id="\'+actdataid+\'">'.__( 'Updated !' , 'hype-animations' ).'</div>\');
						setTimeout(function(){
							jQuery(".hypeanimupdated[data-id="+actdataid+"]").remove();
						}, 3000);
					}
				}
				else {
					alert("'.__( 'Error, please try again !' , 'hype-animations' ).'");
				}
			});
		});
		jQuery(".animupdate").click(function(e){
			e.preventDefault();
			dataid=jQuery(this).attr("data-id");
			jQuery(this).parent().html(\'<form action="" method="post" accept-charset="utf-8" enctype="multipart/form-data"><input type="hidden" name="dataid" value="\'+dataid+\'"><input type="file" name="updatefile"> <input type="submit" name="btn_submit_update" value="'.__( 'Update file' , 'hype-animations' ).'" /></form>\');
		});
		jQuery("#hypeanimations").DataTable({
            responsive: true,
			"columns": [
				null,
				{ "width": "300px" },
				null,
				{ "width": "100px" },
				{ "width": "250px" }
			],
			language: {
				processing:     "'.__( 'Processing ...' , 'hype-animations' ).'",
				search:         "'.__( 'Search :' , 'hype-animations' ).'",
				lengthMenu:    "'.__( 'Show' , 'hype-animations' ).' _MENU_ '.__( 'animations' , 'hype-animations' ).'",
				info:           "'.__( 'Showing' , 'hype-animations' ).' _START_ '.__( 'to' , 'hype-animations' ).' _END_ '.__( 'of' , 'hype-animations' ).' _TOTAL_ '.__( 'animations' , 'hype-animations' ).'",
				infoEmpty:      "'.__( 'Showing animation 0 to 0 of 0 entries' , 'hype-animations' ).'",
				loadingRecords: "'.__( 'Loading ...' , 'hype-animations' ).'",
				zeroRecords:    "'.__( 'No animation has been found' , 'hype-animations' ).'",
				emptyTable:     "'.__( 'No animation has been added' , 'hype-animations' ).'",
				paginate: {
					first:      "'.__( 'First' , 'hype-animations' ).'",
					previous:   "'.__( 'Previous' , 'hype-animations' ).'",
					next:       "'.__( 'Next' , 'hype-animations' ).'",
					last:       "'.__( 'Last' , 'hype-animations' ).'"
				}
			}
		});
	});
	</script>';
}
add_action('wp_ajax_hypeanimations_updatecontainer', 'hypeanimations_updatecontainer');
function hypeanimations_updatecontainer(){
	global $wpdb;
	global $table_name;
    $response = array();
    if(!empty($_POST['dataid']) && !empty($_POST['container'])){
		$post_dataid=$_POST['dataid'];
		$post_container=$_POST['container'];
		$post_containerclass=$_POST['containerclass'];
		$update = $wpdb -> query("UPDATE ".$table_name." SET container='".$post_container."',containerclass='".$post_containerclass."' WHERE id='".$post_dataid."' LIMIT 1");
		$response['response'] = "ok";
    }
	else { $response['response'] = "error"; }
    header( "Content-Type: application/json" );
    if (isset($response)) { echo json_encode($response); }
    exit();
}
add_action('wp_ajax_hypeanimations_getanimid', 'hypeanimations_getanimid');
function hypeanimations_getanimid(){
	global $wpdb;
	global $table_name;
    $response = array();
    if(!empty($_POST['dataid']) && !empty($_POST['container'])){
		$post_dataid=$_POST['dataid'];
		$post_container=$_POST['container'];
		$post_containerclass=$_POST['containerclass'];
		$update = $wpdb -> query("UPDATE ".$table_name." SET container='".$post_container."',containerclass='".$post_containerclass."' WHERE id='".$post_dataid."' LIMIT 1");
		$response['response'] = "ok";
    }
	else { $response['response'] = "error"; }
    header( "Content-Type: application/json" );
    if (isset($response)) { echo json_encode($response); }
    exit();
}
?>