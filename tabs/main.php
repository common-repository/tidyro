<?php 	

	

	$su = get_option( 'td_username' );
	$sp = get_option( 'td_password' );
	$sw = get_option( 'td_sites' );

	$active_all_widget_id = get_option( 'td_widget_all_id' );

	$td_selected_web =  json_decode(get_option('td_selected_web')); 
	$site_categories = 	get_categories();

?>
	<div class="<?php if( $su  ): ?> updated <?php else: ?> error <?php endif; ?> fade" style="overflow:hidden">
			<h2>Sincronizare cu contul de Tidy.ro</h2>
			<form method="post" action="" >

				<input type="hidden" name="td_update_account" value="1"/>	
				<p><label>Nume utilizator tidy.ro: </label><input type="text" size="70" name="td_username" id="td_username" value="<?php echo $su  ?>"></p>
				<p><label>Parola tidy.ro: </label><input type="password" size="70" name="td_password" id="td_password" value="<?php echo $sp  ?>"></p>
				<p><input  style=" margin:0 15px; margin-left:0px;" type="submit" value="Logare" class="button"></p>

			
			</form>

		</div>
		<?php if($su != ""): ?>

			<?php 

				$ch = curl_init();


				$string_to_send = "action=check_secret_key&td_username=".$su.'&td_password='.$sp;

				if( $td_selected_web->td_sweb )
					$string_to_send = "action=check_secret_key&td_username=".$su.'&td_password='.$sp.'&sweb='.$td_selected_web->td_sweb;

				curl_setopt($ch, CURLOPT_URL,'http://tidy.ro/processor');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,
				            $string_to_send  );

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($ch);
				curl_close ($ch);
		
				$answer = json_decode($server_output);		
				$widgets = $answer->widgets;

			
				$widget_size = sizeof( json_decode(json_encode($widgets),true));



	 ?>
			<br/><br/>
			<div class="<?php if( $td_selected_web->td_sweb  ): ?> updated <?php else: ?> error <?php endif; ?> fade">

				<h2>Selectati website-ul</h2>
				<?php $websites = json_decode($sw)  ?>
				<form method="post" action="">
					<input type="hidden" value="1" name="td_select_web" />	
					<p>
						<select name="td_sweb" id="td_sweb">
							<?php foreach ( $websites as $web ): ?>
								<option value="<?php echo $web->ID ?>" <?php if( $td_selected_web->td_sweb == $web->ID ): ?> selected="selected" <?php endif; ?> ><?php echo $web->website ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p><input  style=" margin:0 15px; margin-left:0px;" type="submit" value="Select website" class="button"></p>
				</form>
				
	
				<?php if( $td_selected_web->td_sweb  ): ?>

					<hr/>
					<p>Nume website: <strong><?php echo $td_selected_web->td_name  ?></strong></p>
					<p>URL website: <strong><?php echo $td_selected_web->td_url  ?></strong></p>

				<?php endif;  ?>
				

			</div>
			<?php if( $widget_size > 0 ) :?>
			<br/><br/>
			<div  class="error fade" ><h2>Administreaza widgeturile</h2></div>

			<?php foreach( $widgets as $widget): ?>
				<div  class="<?php if( $active_all_widget_id ==  $widget->widget_id   ): ?> updated <?php else: ?> error <?php endif; ?> fade" >
		
							<p>Nume widget: <strong><?php echo $widget->widget_name ?></strong></p>
							<p>Categorii widget: <strong><?php echo $widget->widget_categories ?></strong></p>
							
							<form method="post" action="">

								<?php if( $active_all_widget_id !=  $widget->widget_id   ): ?> 
									 
									 <p><input  class="button button-primary" style=" margin:0 15px; margin-left:0px;" type="submit" value="Adauga acest widget la finalul fiecarui articol" ></p>
									 <input type="hidden" name="widget_id_add" value="<?php echo $widget->widget_id ?>" />
								     <input type="hidden" name="widget_id_add_script" value="<?php echo $widget->widget_script ?>" />	
								 <?php else: ?>
								 	<input type="hidden" name="widget_id_remove" value="<?php echo $widget->widget_script ?>" />
									 <p><input  style=" margin:0 15px; margin-left:0px;" type="submit"  value="Eliminare widget" class="button"></p> 
								<?php endif; ?>
								
							</form>


							 <?php if( $widget_size  > 1 ): ?>
							 	
							 	<form method="post">
							 		<input type="hidden" name="widget_cat_script" value="<?php echo $widget->widget_script ?>" />
							 		<input type="hidden" name="widget_script_id_ref" value="<?php echo $widget->widget_id ?>" />
							 	
							 	
								<p>OR</p>
								<?php 
									$category_widgets = json_decode(get_option('category_widgets'),true);
					
									if( ! isset($category_widgets[$widget->widget_id])): 

								?>
								<p>
									<select name="widget_script_id">
									 	<?php 	$categories = get_categories(  );  ?>
										<?php foreach( $categories as $cat ): 
											$content_widget = get_option( 'sexcat_'.$cat->term_id );
										?>
											<option <?php if($content_widget != ''): echo 'selected="selected"'; endif; ?> value="<?php echo $cat->term_id ?>"><?php echo $cat->name ?></option>
										<?php endforeach; ?>
									 </select>
									 <input   style=" margin:0 15px; margin-left:0px;" type="submit" value="Adauga acest widget la sfarsitul unei categorii specifice" class="button"></p></form>
									<?php else: ?>
										<form method="post">
											<input type="hidden" name="widget_script_id_ref" value="<?php echo $widget->widget_id ?>" />			
											<input type="hidden" name="widget_script_id" value="<?php echo $category_widgets[$widget->widget_id] ?>" />
											<p><input name="remove_from_category"  style=" margin:0 15px; margin-left:0px;" type="submit" value="Elimina din categoria -> <?php $c = get_the_category_by_ID( $category_widgets[$widget->widget_id] );  echo $c; ?> " class="button"></p>	
										</form>
									<?php endif; ?>

								<?php endif; ?>
							<a href="#" class="see_extra_info">+ Vezi cod widget si vizualizeaza</a><br/><br/>
							<div class="extra_info" style="display:none">
								<p style="color:red; font-size:10px">* Daca aveti un plugin de cache instalat, va rugam sa goliti cache-ul .</p>
								<hr/>
								<p>Codul widgetului pentru implementare manuala</p>
								<p style="color:red; font-size:10px">* Daca implementati widgetul manual, puteti sarii peste acest pas.</p>
								<textarea style="width:450px; height:170px;  "><?php echo $widget->widget_script ?></textarea>
					

								<hr/>
								<p>Previzualizare widget</p>
								<div style="text-align:left;float:left"><?php echo $widget->widget_script ?></div>
								<div style="clear:both"></div>
							</div>
				

				</div>
			<?php endforeach; ?>

			<?php else: ?>
				<div  class="error fade" ><p>Nu exista nici un widget creat pentru acest website.</p></div>

			<?php endif; ?>



<script>

jQuery(document).ready(function(){
	jQuery('.see_extra_info').click(function(){
		jQuery(this).parent().find('.extra_info').toggle();
		return false;
	});

});
		

	</script>




		<?php endif; ?>

