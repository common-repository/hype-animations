<?

add_action( 'media_buttons_context', 'add_hypeanimations_shortcode_button', 1 );
function add_hypeanimations_shortcode_button($output) {
	$output .= '<a href="#oModal2" class="button" id="add_hypeanimations_shortcode_button" style="outline: medium none !important; cursor: pointer;" ><i class="dashicons-before dashicons-format-video"></i> Hype Animations</a>';
	return $output;
}	
add_action( "admin_footer", 'add_hypeanimations_shortcode_button_footer' );
function add_hypeanimations_shortcode_button_footer() { 
	global $table_name;
	global $wpdb;
	$verifaumoinsun = $wpdb->get_var("SELECT id FROM ".$table_name." LIMIT 1");
	$output='
	
	<div id="oModal2" class="oModal">
		<div>	
			<header>
				<a href="#fermer" class="droitefermer">X</a>
				';
				if ($verifaumoinsun>0) {
					$output.='<h2>'.__( 'Choose an animation' , 'hype-animations' ).' :</h2>';
				}
				else {
					$output.='<h2>'.__( 'Upload new animation' , 'hype-animations' ).'</h2>';
				}
				$output.='
			</header>
			<section>
				';
				if ($verifaumoinsun>0) {
					$output.='
					<select id="hypeanimationchoosen">';
					$sql = "SELECT id,nom FROM ".$table_name." ORDER BY id DESC";
					$result = $wpdb->get_results($sql);
					foreach( $result as $results ) {
						$output.='<option value="'.$results->id.'">'.$results->nom.'</a>';
					}
					$output.='</select> <input type="button" id="choosehypeanimation" value="'.__( 'Insert' , 'hype-animations' ).'"
					<p>&nbsp;</p>
					<h2>'.__( 'Or upload a new one' , 'hype-animations' ).' :</h2>
					<p>&nbsp;</p>';
				}
				$output.='
				<form action="" class="dropzone" id="hypeanimdropzone2" method="post" accept-charset="utf-8" enctype="multipart/form-data">
				</form>
			</section>
		</div>
	</div>


	<script>
	Dropzone.autoDiscover = false;
	jQuery(document).ready(function(jQuery){
		jQuery("#hypeanimdropzone").dropzone({
			url: "admin.php?page=hypeanimations_panel",
			method: "post",
			uploadMultiple: false,
			maxFiles: 1,
			acceptedFiles: ".oam",
			dictDefaultMessage: "'.__( 'Drop .OAM file or click here to upload' , 'hype-animations' ).'",
			success: function(file,resp) {
				jQuery(".dropzone").after("<div class=\"dropzone2\" style=\"display:none\">'.__( 'You can now insert this shortcode where you want to insert the animation' , 'hype-animations' ).' : <b>[hypeanimations_anim id=\""+resp+"\"]</b></div>");
			},
			complete: function(file) {
				jQuery(".dropzone2").css("display","block");
				jQuery(".dropzone").remove();
			}
		});
		jQuery("#hypeanimdropzone2").dropzone({
			url: "admin.php?page=hypeanimations_panel",
			method: "post",
			uploadMultiple: false,
			maxFiles: 1,
			acceptedFiles: ".oam",
			dictDefaultMessage: "'.__( 'Drop .OAM file or click here to upload' , 'hype-animations' ).'",
			success: function(file,resp) {
				wp.media.editor.insert("[hypeanimations_anim id=\""+resp+"\"]");
				this.removeFile(file);
				document.location.hash = "";
			}
		});
		
		jQuery("#choosehypeanimation").click(function(e) {
			e.preventDefault();
			dataid=jQuery("#hypeanimationchoosen").val();
			wp.media.editor.insert("[hypeanimations_anim id=\""+dataid+"\"]");
			document.location.hash = "";
		});
	});
	</script>
	';
	echo $output;
}
?>