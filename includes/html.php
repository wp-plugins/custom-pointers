<?php 

/**
 * Overlay form for pointer creation
 * 
 * @return string
 */
function wpcp_overlay_auto() { 
	global $wpcp;

	ob_start();
	?>
		<div class="wpcp-overlay">
			<div class="wpcp-overlay-wrap wpcp-overlay-wrap-left">
				<span class="arrow"></span>
				<div class="wpcp-overlay-content clear">
					<h3 class="header"><span class="wpcp-el-name"></span> <a href="#" class="wpcp-close"><?php _e( 'x', 'wpcp' ); ?></a></h3>
					<form class="wpcp-overlay-form">
						<?php wp_nonce_field( 'wpcp_add_pointer', 'wpcp_nonce' ); ?>
						<input type="hidden" id="action" name="action" value="wpcp_add_pointer" />

						<table>
							<tr>
								<td><label for="wpcp-order"><?php _e( 'Order:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select type="text" id="wpcp-order" name="order" >
											<?php $count = 0; ?>
											<?php while ( ++$count <= 25 ) : ?>
												<option value="<?php echo $count ?>"><?php _e( $count, 'wpcp' ); ?></option>
											<?php endwhile; ?>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The order of this Pointer in relation to all Pointers for this page. This will determine at which point the Pointer will show in the Tour." />
									</p>
								</td>
							</tr>
							<tr>
								<td><label for="wpcp-title"><?php _e( 'Title:', 'wpcp' ); ?></label></td>
								<td><p><input id="wpcp-title" type="text" name="title" /></p></td>
							</tr>
							<tr>
								<td><label for="wpcp-content"><?php _e( 'Content:', 'wpcp' ); ?></label></td>
								<td><p><textarea id="wpcp-content" name="content" rows="5" cols="28" ></textarea></p></td>
							</tr>
							<tr>
								<td><label for="wpcp-edge"><?php _e( 'Edge:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select id="wpcp-edge" name="edge">
											<option value="top"><?php _e( 'Top', 'wpcp' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'wpcp' ); ?></option>
											<option value="left"><?php _e( 'Left', 'wpcp' ); ?></option>
											<option value="right"><?php _e( 'Right', 'wpcp' ); ?></option>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The direction the Pointer will point to. If you choose Left, the Pointer will point to the left." />
									</p>
								</td>
							</tr>
							<tr>
								<td><label for="wpcp-align"><?php _e( 'Align:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select id="wpcp-align" name="align">
											<option value="top"><?php _e( 'Top', 'wpcp' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'wpcp' ); ?></option>
											<option value="left"><?php _e( 'Left', 'wpcp' ); ?></option>
											<option value="right"><?php _e( 'Right', 'wpcp' ); ?></option>
											<option value="middle"><?php _e( 'Middle', 'wpcp' ); ?></option>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The placement of the Arrow of the Pointer relative to its content." />
									</p>
								</td>
							</tr>
							<?php if ( wpcp_is_active() ) : ?>
							<tr>
								<td><label for="wpcp-collection"><?php _e( 'Collection:', 'wpcp' ); ?></label></td>
								<td>
									<p class="clear"><?php wpcp_collections_dropdown(); ?><span class="wpcp-add-collection"></span></p>
									<p class="clear" style="display:none;">
										<input type="text" class="wpcp-new-collection" placeholder="<?php _e( 'Press enter when done...', 'wpcp' ); ?>" />
										<span class="wpcp-cancel-add-collection"></span>
									</p>
								</td>
							</tr>
							<?php endif; ?>
						</table>
						<p class="footer">
							<input class="button-primary" type="submit" value="Create" />
						</p>
					</form>
				</div>
			</div>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Overlay form for pointer creation
 * 
 * @return string
 */
function wpcp_overlay_manual() { 
	global $wpcp;

	ob_start();
	?>
		<div class="wpcp-overlay wpcp-manual">
			<div class="wpcp-overlay-wrap wpcp-overlay-wrap-left">
				<div class="wpcp-overlay-content clear">
					<h3 class="header"> <a href="#" class="wpcp-close"><?php _e( 'x', 'wpcp' ); ?></a></h3>
					<form class="wpcp-overlay-form">
						<?php wp_nonce_field( 'wpcp_add_pointer', 'wpcp_nonce' ); ?>
						<input type="hidden" id="action" name="action" value="wpcp_add_pointer" />

						<table>
							<tr>
								<td><label for="wpcp-order"><?php _e( 'Order:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select type="text" id="wpcp-order" name="order" >
											<?php $count = 0; ?>
											<?php while ( ++$count <= 25 ) : ?>
												<option value="<?php echo $count ?>"><?php _e( $count, 'wpcp' ); ?></option>
											<?php endwhile; ?>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The order of this Pointer in relation to all Pointers for this page. This will determine at which point the Pointer will show in the Tour." />
									</p>
								</td>
							</tr>
							<tr>
								<td><label for="wpcp-selector"><?php _e( 'Selector:', 'wpcp' ); ?></label></td>
								<td><p><input id="wpcp-selector" type="text" name="target" /></p></td>
							</tr>

							<tr>
								<td><label for="wpcp-title"><?php _e( 'Title:', 'wpcp' ); ?></label></td>
								<td><p><input id="wpcp-title" type="text" name="title" /></p></td>
							</tr>
							<tr>
								<td><label for="wpcp-content"><?php _e( 'Content:', 'wpcp' ); ?></label></td>
								<td><p><textarea id="wpcp-content" name="content" rows="5" cols="28" ></textarea></p></td>
							</tr>
							<tr>
								<td><label for="wpcp-edge"><?php _e( 'Edge:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select id="wpcp-edge" name="edge">
											<option value="top"><?php _e( 'Top', 'wpcp' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'wpcp' ); ?></option>
											<option value="left"><?php _e( 'Left', 'wpcp' ); ?></option>
											<option value="right"><?php _e( 'Right', 'wpcp' ); ?></option>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The direction the Pointer will point to. If you choose Left, the Pointer will point to the left." />
									</p>
								</td>
							</tr>
							<tr>
								<td><label for="wpcp-align"><?php _e( 'Align:', 'wpcp' ); ?></label></td>
								<td>
									<p>
										<select id="wpcp-align" name="align">
											<option value="top"><?php _e( 'Top', 'wpcp' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'wpcp' ); ?></option>
											<option value="left"><?php _e( 'Left', 'wpcp' ); ?></option>
											<option value="right"><?php _e( 'Right', 'wpcp' ); ?></option>
											<option value="middle"><?php _e( 'Middle', 'wpcp' ); ?></option>
										</select>
										<img class='tool-tip-icon' src="<?php echo $wpcp->plugin_uri . '/assets/images/question_mark.png' ?>" data-text="The placement of the Arrow of the Pointer relative to its content." />
									</p>
								</td>
							</tr>
							<?php if ( wpcp_is_active() ) : ?>
							<tr>
								<td><label for="wpcp-collection"><?php _e( 'Collection:', 'wpcp' ); ?></label></td>
								<td>
									<p class="clear"><?php wpcp_collections_dropdown(); ?><span class="wpcp-add-collection"></span></p>
									<p class="clear" style="display:none;">
										<input type="text" class="wpcp-new-collection" placeholder="<?php _e( 'Press enter when done...', 'wpcp' ); ?>" />
										<span class="wpcp-cancel-add-collection"></span>
									</p>
								</td>
							</tr>
							<?php endif; ?>
						</table>
						<p class="footer">
							<input class="button-primary" type="submit" value="Create" />
						</p>
					</form>
				</div>
			</div>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Manual mode splash
 */
function wpcp_splash() {
	ob_start(); ?>
		<div class="wpcp-splash">
			<div class="wpcp-splash-content">
				<p>Press <span class="command"> &nbsp;CTRL+ALT+n&nbsp;  </span> to add a pointer.</p>
				<p><label for="wpcp-dismiss-splash">Do not show this dialog again?&nbsp; </label><input type="checkbox" id="wpcp-dismiss-splash" /></p>
			</div>
			<p class="footer"><input class="button-primary" type="button" value="Okay" /></p>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Pointer categories dropdown
 * 
 * @return string
 */
function wpcp_collections_dropdown() {
	$args = array(
		'id'                 => 'wpcp-collection',
		'hide_empty'         => 0, 
		'echo'               => 1,
		'hierarchical'       => 1, 
		'name'               => 'collection',
		'taxonomy'           => 'wpcp_collection',
	);

	return wp_dropdown_categories( $args );
}

/**
 * Contextual help content
 *
 * @param boolean $has_pointers
 * @return string
 */
function wpcp_contextual_help_content( $has_pointers = false, $finished = false  ) { 
	ob_start(); ?>
	
	<?php if ( !$has_pointers ) : ?>
	
	<div class="wpcp-help-content">
		There are no Pointer Collections for this section. Contact the website administrator to request a custom Pointer Collection if you're in need of assistance and training.
	</div>

	<?php else : ?>
	
	<div class="wpcp-help-content"> 
		<p> Lost? You are in the right place! Click the button below to start or restart tour for this page. </p>
		<input type="button" class="wpcp-restart-collection button button-primary button-hero" value="<?php _e( 'Start Tour!', 'wpcp' ) ?>" />
	</div>
	
	<?php endif;
	
	return ob_get_clean();
}