<?php
/**
 * Plugin Name:  WP Admin Todo List
 * Description:  Admin side Todo list , which helps you to remember any task easily.
 * Plugin URI:   http://shyammakwana.me/wp-admin-todo-list-wordpress-plugin
 * Version:      1.2.7
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

		//var cookieName = 'sm_at_div_wrapper';
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
						//console.log(dataReturn);
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

				var d = new Date();
				clearTimeout(settime);
				settime = setTimeout(function(){
					sm_at_remove_empty();
				}, 1000);
			}
		}

		// check and remove empty fields, then pass data to save
		function sm_at_remove_empty() {
			var inputArr = [];
			jQuery('#sm_at_todos .sm_at_textarea_div').each(function(){
				var val_input = jQuery(this).find('input').val() ;
				if (jQuery.trim(val_input) != '') {
					inputArr.push(val_input) ;
				}

			});
			sm_at_save_data(inputArr);
		}

		// function saves data passed in array format.
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

			if ( jQuery(document).find('#sm_at_textarea_div').length > 0 ) {
				jQuery('#sm_at_textarea_div').clone().attr('id','').find('input').val('').parent().insertAfter('.sm_at_textarea_div:last').find('input').focus();
			}
			else {
				var newField = '';
				newField += '<p class="sm_at_textarea_div" id="sm_at_textarea_div" contenteditableXX  onkeyup="">';
				newField += 	'<span class="draggable_handle">::</span>';
				newField += 	'<input type="text" oninput="return sm_at_process_textarea(this,event);" onkeyup="return check_key(event, this);" name="sm_at_textarea_div_input" class="sm_at_textarea_div_input" value=""/>';
				newField += 	'<span class="sm_delete_todo">x</span>';
				newField += '</p>';

				jQuery("#sm_at_todos").append(newField);
			}

			adjust_div_height(); 

		}

		
		// adjust div height - to apply overflow scroll and height 
		function adjust_div_height() {
			
			var a = jQuery(window).height();
			var b = jQuery('.sm_at_div_wrapper').offset().top;
			var c = jQuery('.sm_at_div_wrapper').height();
			
			//jQuery(window).height() -  jQuery('.sm_at_div_wrapper').offset().top - 86 - 25 
			var maxheight = a - b - 86 - 25 ; 
			jQuery('#sm_at_todos').css('max-height', maxheight+'px');	
		}


		// check pressed keys and then do action accordingly.
		function check_key(e , ele) {
			//detect enter 
			if(e.which == 13) {
				add_new_note();
			}
			//detect backspace 
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

			// hide whole div based on cookie.
			var myCookie = document.cookie.replace(/(?:(?:^|.*;\s*)sm_at_div_wrapper\s*\=\s*([^;]*).*$)|^.*$/, "$1");
			if (myCookie == 'none')
				jQuery('.sm_at_div_wrapper').hide();

			// hide whole div based on cookie.
			var sm_at_todo_main_block_cookie = document.cookie.replace(/(?:(?:^|.*;\s*)sm_at_todo_main_block_cookie\s*\=\s*([^;]*).*$)|^.*$/, "$1");
			
			if (sm_at_todo_main_block_cookie == 'none') {
				jQuery('.sm_at_todo_main_block').hide();
				jQuery('.slidetoggle-button').html('&xwedge;');
			}


			// make todo sortable.
			jQuery( ".todos" ).sortable({
				revert: true,
				handle : '.draggable_handle',
			});
			
			
			// make div's draggables 
			jQuery( ".sm_at_div_wrapper" ).draggable({
				containment: "window",
				handle: "p.sm_at_status,h2",
				cancel : ".add_new_note, .slidetoggle-button",
				scroll: false,
			});


			/*jQuery( ".sm_at_div_wrapper" ).on( "dragstop", function( event, ui ) {
				//console.log(ui.position);
			});*/


			// save on sort
			jQuery( ".todos" ).on( "sortstop", function( event, ui ) {
				sm_at_remove_empty();
			});


			// remove todo
			//.sm_at_textarea_div .sm_delete_todo
			jQuery(document).on( "click",".sm_at_textarea_div .sm_delete_todo", function( event, ui ) {
				jQuery(this).closest('.sm_at_textarea_div').remove();
				sm_at_remove_empty();
				adjust_div_height(); 

			});
			
			
			// + - button 
			jQuery(document).on( "click",".slidetoggle-button", function( event, ui ) {
				
				var toggleButton = jQuery('.slidetoggle-button'); 
				jQuery('.sm_at_todo_main_block').slideToggle({
					done : function(){

						// variable used for ajax saving , as a data
						var sm_at_todo_main_block_cookie_block_state = '' ;
				
						if ( jQuery('.sm_at_todo_main_block').is(':visible') == true ) {
							//console.log('if = ' + jQuery('.sm_at_todo_main_block').is(':visible')) ; 
							toggleButton.html('&xvee;');
							document.cookie = 'sm_at_todo_main_block_cookie=block';
							sm_at_todo_main_block_cookie_block_state = 'block';
							
						} else {
							//console.log('else  = ' + jQuery('.sm_at_todo_main_block').is(':visible')) ; 
							toggleButton.html('&xwedge;');
							document.cookie = 'sm_at_todo_main_block_cookie=none';
							sm_at_todo_main_block_cookie_block_state = 'none';
						}
					}
				});
				
			});
			
			// adjust div's height to control div going outside of window
			adjust_div_height();
			

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
		border: 1px solid #e5e5e5;
	}
	.sm_at_div_wrapper h2 {
		cursor: move;
		background: #0073aa;
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
		background: #f7fcfe;
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
		border-top: 1px solid #e5e5e5;
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
		margin-left: 15px;
	}

	.sm_at_controls {
		padding: 3px;
		background: #eaeaea;
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
	
	.draggable_handle {
		display: none;
		position: absolute;
		top: 9px;
	}
	
	.sm_at_div_wrapper .sm_at_textarea_div:hover  .draggable_handle
	{
		display:inline-block;
	}
	
	span.slidetoggle-button {
		position: absolute;
		right: 10px;
		padding: 0 4px;
		cursor: pointer;
	}
	
	#sm_at_todos {
		overflow-y : auto; 
	}
	
	</style>';

	if (isset($_GET['debug']) or 1) {

		$sm_at_data = unserialize(( get_option('sm_at_data_'.get_current_user_id()) )) ;
		
		//restore data back when upgrading from 1.2 to 1.2.4 +
		if (get_option('sm_at_bkp') != '1')  {
			$sm_at_data2 = unserialize(( get_option('sm_at_data' ) )) ;		
				
			if (is_array($sm_at_data) && is_array($sm_at_data2) ) { 
				$sm_at_data = array_merge($sm_at_data, $sm_at_data2);
			}			
		}
		
						
		if ($sm_at_data == null OR count($sm_at_data) <= 0  ) {
			$sm_at_data = array('Enter your todo list.');
		}
	

		$sm_at_block_visibility = get_option('sm_at_block_visibility_'.get_current_user_id() );
		if ($sm_at_block_visibility == null OR $sm_at_block_visibility  == '' ) {
			$sm_at_block_visibility = 'block';
		}

		echo '<div class="sm_at_div_wrapper" style="display:'.$sm_at_block_visibility.'">
			<h2>Todo List <span class="slidetoggle-button" >&xvee;</span></h2>';
		echo '<div class="sm_at_todo_main_block">
			<div class="sm_at_controls"><i class="add_new_note" onclick="add_new_note()"><button>Add</button></i></div>
			<!--<textarea onkeyup="return sm_at_process_textarea(this);" onchange="return sm_at_process_textarea(this);" class="sm_at_textarea" rows="5" cols="20">'.get_option('sm_at_data').'</textarea>
			<textarea onkeyup="return sm_at_process_textarea(this);" onchange="return sm_at_process_textarea(this);" class="sm_at_textarea" rows="5" cols="20">'.$sm_at_data.'</textarea>-->';
		?>
		<div id="sm_at_todos" class="todos">
			<?php
			foreach($sm_at_data as $key=>$line) {
			?>
			<p class="sm_at_textarea_div" <?php echo ($key == 0 )? 'id="sm_at_textarea_div"':''; ?> contenteditableXX  onkeyup="">
				<span class="draggable_handle">::</span>
				<input type="text" oninput="return sm_at_process_textarea(this,event);" onkeyup="return check_key(event, this);" name="sm_at_textarea_div_input" class="sm_at_textarea_div_input" value="<?php echo $line ;  ?>"/>
				<span class="sm_delete_todo">x</span>
			</p>
			<?php } ?>
		</div>
		<p class="sm_at_status">Status: </p>
		</div>	
		<?php echo '</div>';
	}
}

add_action('wp_ajax_sm_at_save_data' , 'sm_at_save_data') ;
function sm_at_save_data() {
	$data = array_filter($_REQUEST['sm_at_data']);

	update_option('sm_at_bkp', '1' );
	
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
