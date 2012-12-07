<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) 
	exit;
	
add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', 'bp_reshare_admin_menu', 21);

function bp_reshare_admin_menu() {
	global $bp;
	
	if( version_compare( BP_RESHARE_PLUGIN_VERSION, get_option( 'bp-reshare-version' ), '>' ) )
		do_action('bp_reshare_plugin_updated');

	if ( !$bp->loggedin_user->is_site_admin )
		return false;
		
	$admin_page = bp_reshare_16_new_admin();
	
	if( $admin_page == 'bp-general-settings.php' )
		$submenu = 'bp-general-settings';
	else
		$submenu = $admin_page;
		
	$bp_checkins_manager_admin_page = add_submenu_page( $submenu, __( 'BP Reshare Settings', 'bp-reshare' ), __( 'BP Reshare Settings', 'bp-reshare' ), 'manage_options', 'bp-reshare-admin', 'bp_reshare_settings' );
	
}

function bp_reshare_settings() {
	
	if ( isset( $_POST['bp_reshare_admin_submit'] ) && isset( $_POST['bp-reshare-admin'] ) ) {
		if ( !check_admin_referer('bp-reshare-admin') )
			return false;

		// Settings form submitted, now save the settings.
		foreach ( (array)$_POST['bp-reshare-admin'] as $key => $value )
			bp_update_option( $key, $value );

	}
	
	$amount_user = (int)bp_get_option( 'bp-reshare-user-amount' ) ? bp_get_option( 'bp-reshare-user-amount' ) : 5 ;
	
	?>
	<div class="wrap">
		
		<h2><?php _e('BP Reshare settings', 'bp-reshare');?></h2>
		
		<?php if ( isset( $_POST['bp-reshare-admin'] ) ) : ?>

			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'bp-reshare' ); ?></p>
			</div>

		<?php endif; ?>
		
		<form action="" method="post" id="bp-admin-form">
			
			<table class="form-table">
				<tbody>
						<tr>
							<th scope="row"><?php _e( "Amount of user's avatar to show when a single activity is displayed", 'bp-reshare' ) ?></th>
							<td>
								<input type="text" name="bp-reshare-admin[bp-reshare-user-amount]" value="<?php echo $amount_user;?>" id="bp-reshare-user-amount" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Use Javascript trick 1 : a link to remind where the filter dropdown is.', 'bp-reshare' ) ?></th>
							<td>
								<input type="radio" name="bp-reshare-admin[bp-reshare-js-trick-one]"<?php if ( (int)bp_get_option( 'bp-reshare-js-trick-one' ) ) : ?> checked="checked"<?php endif; ?> id="bp-reshare-js-trick-one-yes" value="1" /> <?php _e( 'Yes', 'bp-reshare' ) ?> &nbsp;
								<input type="radio" name="bp-reshare-admin[bp-reshare-js-trick-one]"<?php if ( !(int)bp_get_option( 'bp-reshare-js-trick-one' ) || '' == bp_get_option( 'bp-reshare-js-trick-one' ) ) : ?> checked="checked"<?php endif; ?> id="bp-reshare-js-trick-one-no" value="0" /> <?php _e( 'No', 'bp-reshare' ) ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Use Javascript trick 2 : automatically activate all members tab when activity textarea receives the focus', 'bp-reshare' ) ?></th>
							<td>
								<input type="radio" name="bp-reshare-admin[bp-reshare-js-trick-two]"<?php if ( (int)bp_get_option( 'bp-reshare-js-trick-two' ) ) : ?> checked="checked"<?php endif; ?> id="bp-reshare-js-trick-one-two" value="1" /> <?php _e( 'Yes', 'bp-reshare' ) ?> &nbsp;
								<input type="radio" name="bp-reshare-admin[bp-reshare-js-trick-two]"<?php if ( !(int)bp_get_option( 'bp-reshare-js-trick-two' ) || '' == bp_get_option( 'bp-reshare-js-trick-two' ) ) : ?> checked="checked"<?php endif; ?> id="bp-reshare-js-trick-two-no" value="0" /> <?php _e( 'No', 'bp-reshare' ) ?>
							</td>
						</tr>
				</tbody>
			</table>
		
			<p class="submit">
				<input class="button-primary" type="submit" name="bp_reshare_admin_submit" id="bp-reshare-admin-submit" value="<?php _e( 'Save Settings', 'bp-reshare' ); ?>" />
			</p>

			<?php wp_nonce_field( 'bp-reshare-admin' ); ?>
		
		</form>
		
	</div>
	<?php
}

function bp_reshare_16_new_admin(){
	global $bp;
	
	if( !defined( 'BP_VERSION' ) )
		$version = BP_VERSION;
	
	else
		$version = $bp->version;
	
	
	if( version_compare( $version, '1.6', '>=' ) ){
		$page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
		return $page;
	}
	else return 'bp-general-settings.php';
}