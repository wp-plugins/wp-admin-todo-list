<?php 
/**
 * Plugin Name:  WP Admin Todo List
 * Description:  Admin side Todo list , which helps you to remember any task easily. 
 * Plugin URI:   http://shyammakwana.me
 * Version:      1.2.3
 * Text Domain:  wp_admin_todo_list
 * Author:       Shyam Makwana
 * Author URI:   http://shyammakwana.me/
 * License:      GPLv3 or later
 * Network:      false
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
**/



add_action( 'admin_bar_menu', 'toolbar_link_to_mypage', 999 );
function toolbar_link_to_mypage( $wp_admin_bar ) {
	$args = array(
		'id'    => 'sm_admin_todo',
		'title' => 'Todo',
		'href'  => 'javascript:;',
		'meta'  => array( 'class' => 'sm_admin_todo', 
					'onclick' => 'return show_todo(this);',
		 )
	);
	$wp_admin_bar->add_menu( $args );
}

add_action( 'admin_print_scripts', 'sm_admin_todo_js' , 100 ); 
function sm_admin_todo_js() {
	wp_enqueue_script( 'jquery-ui-core');
	wp_enqueue_script( 'jquery-ui-dialog');
	wp_enqueue_script( 'jquery-ui-sortable');
	wp_enqueue_script( 'jquery-ui-draggable');
	wp_enqueue_script( 'jquery-ui-droppable');
	
	//print_r ( get_theme_mod('background_color') ); die;
	
?>		
	<script type="text/javascript">
		
		var cookieName = 'sm_at_div_wrapper';
		
		function show_todo(el) {
			var block_state = '' ; 
			jQuery('.sm_at_div_wrapper').toggle();
			if(jQuery('.sm_at_div_wrapper').is(':visible')) {
				//console.log('visible');
				document.cookie = 'sm_at_div_wrapper=block';
				block_state = 'block';
			}
			else {
				//console.log('none');
				document.cookie = 'sm_at_div_wrapper=none';
				block_state = 'none';
			}
			
			
			jQuery.ajax({
					url : "<?php echo admin_url('admin-ajax.php'); ?>" ,				
					data : {action : 'sm_at_visibility'  , 'sm_at_block_visibility' : block_state} ,
					method: 'post',
					success: function (dataReturn){
						console.log(dataReturn); 
					}
				});
				
			return false; 
		}
		
		var settime = '';
		function sm_at_process_textarea(el, event) {
			jQuery(document).find('.sm_at_status').text('Status:');
			jQuery(document).find('.sm_at_status').show();
			
			//sm_at_remove_empty(el); 
			//console.log(event);
			
			if(typeof ajaxurl != 'undefined' && ajaxurl != '' ) {
			
				//console.log('start time ');
				
				var d = new Date(); 
				clearTimeout(settime);
				settime = setTimeout(function(){
					//console.log(d.getTime() + '  ' + jQuery(el).val() );										
					sm_at_remove_empty();
					
				}, 1000);
			}
			else {
				//alert('doesnot exists');
			}		
		}
		
		
		
		function sm_at_remove_empty() {
			var inputArr = [];	
			jQuery('#sm_at_todos .sm_at_textarea_div').each(function(){
				var val_input = jQuery(this).find('input').val() ; 
				if (jQuery.trim(val_input) != '') {
					inputArr.push(val_input) ; 
				}
				
			});
			
			//console.log(inputArr);
			sm_at_save_data(inputArr);
			
		}
		
		function sm_at_save_data(data) {
			//console.log(data);
			jQuery(document).find('.sm_at_status').text('Status: Saving...');
			jQuery.ajax({
				url : "<?php echo admin_url('admin-ajax.php'); ?>" ,				
				data : {action : 'sm_at_save_data'  , 'sm_at_data' : data} ,
				method: 'post',
				success: function (dataReturn){
					//console.log(dataReturn); 
					jQuery(document).find('.sm_at_status').text('Status: Saved.');
				}
			});
		}
		
		
		// add_new_note
		function add_new_note(){
			var handle = jQuery('#sm_at_textarea_div').find('.draggable_handle').clone();
			jQuery('#sm_at_textarea_div').clone().attr('id','').find('input').val('').parent().insertAfter('.sm_at_textarea_div:last').find('input').focus();
		}
		
		function check_key(e , ele) {
			if(e.which == 13) {
				add_new_note();
			}
			if(e.which == 8) {
				if(jQuery.trim(jQuery(ele).val()) == '') {
					
					if(jQuery('.sm_at_textarea_div').length > 1 ) { 
						//console.log( jQuery(ele).closest('.sm_at_textarea_div').prev().find('input').length ) ; 
						
						jQuery('.sm_at_textarea_div:first').attr('id', 'sm_at_textarea_div').find('input').focus();
						
						if ( jQuery(ele).closest('.sm_at_textarea_div').prev().find('input').length == 0  ) {
							jQuery(ele).closest('.sm_at_textarea_div').next().find('input').focus();
						} else {
							jQuery(ele).closest('.sm_at_textarea_div').prev().find('input').focus();
						}
						jQuery(ele).closest('.sm_at_textarea_div').remove();
						
						jQuery('.sm_at_textarea_div:first').attr('id', 'sm_at_textarea_div').find('input');
					}
					
				}				
			}
		}
	
		jQuery(function() {
			
			
			var myCookie = document.cookie.replace(/(?:(?:^|.*;\s*)sm_at_div_wrapper\s*\=\s*([^;]*).*$)|^.*$/, "$1");
						
			if (myCookie == 'none')
				jQuery('.sm_at_div_wrapper').hide();
			
			
			
			jQuery( ".todos" ).sortable({
				revert: true,
				handle : '.draggable_handle',
			});
	
			jQuery( ".sm_at_div_wrapper" ).draggable({
				containment: "window",
				handle: "p.sm_at_status,h2",
				cancel : ".add_new_note",
				scroll: false,
			});
			
			
			jQuery( ".sm_at_div_wrapper" ).on( "dragstop", function( event, ui ) { 
				//console.log(ui.position);
			});
			
			jQuery( ".todos" ).on( "sortstop", function( event, ui ) { 
				sm_at_remove_empty();
			});
			
			
			// remove todo 
			//.sm_at_textarea_div .sm_delete_todo 
			jQuery(document).on( "click",".sm_at_textarea_div .sm_delete_todo", function( event, ui ) { 
				jQuery(this).closest('.sm_at_textarea_div').remove();
				sm_at_remove_empty();
				
			});
			
			
		});

	</script>
<?php }

add_action('admin_footer', 'my_admin_footer_function');
function my_admin_footer_function() {
	echo '<style type="text/css">
	.sm_at_div_wrapper  {
		position:fixed; 
		right:40px; 
		top:100px;
		z-index: 99999;
		background: #FFF;
		width:350px;
	}
	.sm_at_div_wrapper h2 {
		cursor: move;
		background: brown;
		color: #FFF;
		padding: 7px 10px;
		display: block;
		margin: 0;
	}
	.sm_at_div_wrapper .sm_at_textarea {
		overflow: auto;
		padding: 2px 6px;
		margin: 0;
		background: lightyellow;
		padding: 7px 9px;
	}
	.sm_at_div_wrapper .sm_at_textarea_div {
		overflow: auto;
		margin: 0 0 3px 0 ;
		background: lightyellow;
		padding: 3px 2px;
		position: relative; 
		//border : 1px dashed #555;
	}
	.sm_at_div_wrapper p.sm_at_status {
		cursor: move;
		background: #fff;
		color: #777;
		padding: 7px 10px;
		display: block;
		margin: 0;
	}
	.draggable_handle {
		padding: 3px;
		margin-right: 5px;
		color: #777777;
		cursor: move;
		font-size: 20px;
		line-height: 10px;
		padding: 2px;
	}
	.sm_at_textarea_div_input {
		width: 90%;
		background: transparent !important;
		border: none !important;
		box-shadow: none !important;
	}
	
	.sm_at_controls {
		padding: 3px; 
		background: #ddd;
	}
	
	.sm_delete_todo {
		position: absolute;
		top: 8px;
		font-size: 13px;
		right: 3px;
		border: 1px solid #777;
		color: #fff;
		background: #777;
		border-radius: 25px;
		padding: 3px;
		line-height: 0.6;
		height: 8px;
		display:none;
		cursor: pointer;
	}
	.sm_at_div_wrapper .sm_at_textarea_div:hover  .sm_delete_todo {
		display:block;
	}
	</style>';
	
	if (isset($_GET['debug']) or 1) {
		
		$sm_at_data = unserialize(( get_option('sm_at_data_'.get_current_user_id()) )) ; 
		if ($sm_at_data == null OR count($sm_at_data) <= 0 ) {
			$sm_at_data = array('Enter your todo list.');
		}		
		
		$sm_at_block_visibility = get_option('sm_at_block_visibility_'.get_current_user_id() );
		if ($sm_at_block_visibility == null OR $sm_at_block_visibility  == '' ) {
			$sm_at_block_visibility = 'block';
		}		
		
		echo '<div class="sm_at_div_wrapper" style="display:'.$sm_at_block_visibility.'">
			<h2>Todo List </h2>
			<div class="sm_at_controls"><i class="add_new_note" onclick="add_new_note()"><button>Add</button></i></div>
			<!--<textarea onkeyup="return sm_at_process_textarea(this);" onchange="return sm_at_process_textarea(this);" class="sm_at_textarea" rows="5" cols="20">'.get_option('sm_at_data').'</textarea>
			<textarea onkeyup="return sm_at_process_textarea(this);" onchange="return sm_at_process_textarea(this);" class="sm_at_textarea" rows="5" cols="20">'.$sm_at_data.'</textarea>-->';
		?>	
		<div id="sm_at_todos" class="todos">
			<?php 
			foreach($sm_at_data as $key=>$line) {
			?>
			<p class="sm_at_textarea_div" <?php echo ($key == 0 )? 'id="sm_at_textarea_div"':''; ?> contenteditableXX  onkeyup="">
				<span class="draggable_handle">:::</span>	
				<input type="text" oninput="return sm_at_process_textarea(this,event);" onkeyup="return check_key(event, this);" name="sm_at_textarea_div_input" class="sm_at_textarea_div_input" value="<?php echo $line ;  ?>"/>
				<span class="sm_delete_todo">x</span>				
			</p>
			<?php } ?>
		</div>
		<p class="sm_at_status">Status: </p>
		<?php echo '</div>';
	}
}

add_action('wp_ajax_sm_at_save_data' , 'sm_at_save_data') ; 
function sm_at_save_data() {
	$data = array_filter($_REQUEST['sm_at_data']);
		
	if(update_option('sm_at_data_'.get_current_user_id(),serialize(( $data )))) {
		return 'Saved.';
	}else {
		return 'Failed.';
	}
	//return true ; 
	return 'Failed.';
	wp_die();
}

add_action('wp_ajax_sm_at_visibility' , 'sm_at_visibility') ; 
function sm_at_visibility() {
	
	$data = $_REQUEST['sm_at_block_visibility'];
		
	if(update_option('sm_at_block_visibility_'.get_current_user_id(), htmlentities($data) )) {
		echo htmlentities($data) ;
		return htmlentities($data) ;
	}else {
		echo 'Failed.';
		return 'Failed.';
	}
	//return true ; 
	return 'Failed.';
	wp_die();
}
