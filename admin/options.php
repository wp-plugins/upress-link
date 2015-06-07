<div class="wrap upress-wrap">
	<h2><?php _ex($this->plugin_name, 'admin page title', $this->text_domain); ?></h2>

	<?php if( $error_message ) : ?>
		<div class="error init-error"><p><strong><?php _e( 'uPress error:', $this->text_domain ); ?></strong> <?php echo $error_message; ?></p></div>
	<?php endif ?>

	<div class="notice notice-success api-success"><p></p></div>
	<div class="notice notice-info no-api-key"><p><?php _e( 'You have not yet entered your API key. You must have an API key before you can use this plugin', $this->text_domain ); ?></p></div>
	<div class="error api-error"><p><strong><?php _e( 'uPress error:', $this->text_domain ); ?></strong> <span></span></p></div>
	<p>
		<?php /* translators: allowed HTML tags: <br>, <em>, <strong>, <a href='' title=''> */ ?>
		<?php echo wp_kses( __('uPress Link allows you to manage some of the features available in uPress.<br>You will have to generate an API key to link the plugin to your uPress account.', $this->text_domain ), array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
		) ); ?>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( $this->plugin_slug ); ?>
		<?php do_settings_sections( $this->plugin_slug ); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'uPress API Key:', $this->text_domain ); ?></th>
				<td>
					<input type="text" id="api_key" name="<?php echo $opt_name; ?>[api_key]" value="<?php echo esc_attr( $options['api_key'] ); ?>" size="80" />
					<br>
					<a href="https://my.upress.co.il/account/websites" target="_blank"><?php _e( 'Go to uPress and get your API key', $this->text_domain ); ?></a>
					<p><?php _e( 'Select your website from the list, then go to "Settings" tab and click "Generate API Key" button', $this->text_domain ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Delete cache after post update:', $this->text_domain ); ?></th>
				<td>
					<input type="checkbox" name="<?php echo $opt_name; ?>[clear_post_cache]" value="1" <?php checked( $options['clear_post_cache'], '1', TRUE ); ?> size="80" />
					<span class="description"><?php esc_attr_e( 'This will remove the CDN cache for the post URL so the updates will show immediately*. This is applicable only if the CDN is available and enabled.', $this->text_domain ); ?></span>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>


	<hr>
	<h3><?php esc_attr_e( 'More actions', $this->text_domain ); ?></h3>
	<div class="wrap">
		<h4><?php esc_attr_e( 'Fix media upload path', $this->text_domain ); ?></h4>
		<p><?php esc_attr_e( 'Sometimes after migrating a WordPress installation the media path is not set correctly and breaks uploading media content. This will fix the issue and correct the upload path.', $this->text_domain ); ?></p>
		<p><button class="button-secondary media-path-fix-button" type="button" data-ajax-spinner><?php esc_attr_e( 'Run media path fix', $this->text_domain ); ?></button></p>

		<h4><?php esc_attr_e( 'Database search and replace', $this->text_domain ); ?></h4>
		<p><?php esc_attr_e( 'This will allow you to replace a text in your entire website in a simple click of a button', $this->text_domain ); ?></p>
		<p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_attr_e( 'Search:', $this->text_domain ); ?></th>
					<td><input type="text" class="regular-text search-and-replace-from" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_attr_e( 'Replace:', $this->text_domain ); ?></th>
					<td><input type="text" class="regular-text search-and-replace-to" /></td>
				</tr>
			</table>

			<button class="button-secondary search-and-replace-button" type="button" data-ajax-spinner><?php esc_attr_e( 'Search and replace', $this->text_domain ); ?></button>
		</p>
	</div>


	<?php if( $is_api_key_set ) : ?>
	<hr>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">

					<div class="postbox upress-section" id="upress-tab-general" data-not-available-text="<?php _e( 'General settings not available', $this->text_domain ); ?>">
						<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
						<div class="reload-btn"><a href="" title="<?php _e( 'Refresh section', $this->text_domain ); ?>"><span class="dashicons dashicons-update"></span></a></div>
						<!-- Toggle -->
						<h3 class="hndle"><span><?php _ex('General', 'section title', $this->text_domain); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php _e( 'Auto Update', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="auto_update">
										<span class="description"><?php esc_attr_e( 'uPress can make sure your WordPress is up to date with its automatic core updates.', $this->text_domain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Auto Redirect WWW', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="auto_redirect_www">
										<span class="description"><?php esc_attr_e( 'Automatically redirect your domain to the www.domain.name counterpart.', $this->text_domain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Web Firewall', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="web_firewall">
										<span class="description"><?php esc_attr_e( 'Web Firewall block tens of thousands of known attacks, and we update our system every day to ensure even the newest attacks are blocked right away. Our tools will allow you to easily fix any number of hacks and exploits.', $this->text_domain ); ?></span>
									</td>
								</tr>
							</table>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->

					<div class="postbox upress-section" id="upress-tab-cdn" data-check-availability="cdn_available" data-not-available-text="<?php _e( 'CDN settings not available', $this->text_domain ); ?>">
						<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
						<div class="reload-btn"><a href="" title="<?php _e( 'Refresh section', $this->text_domain ); ?>"><span class="dashicons dashicons-update"></span></a></div>
						<!-- Toggle -->
						<h3 class="hndle"><span><?php _ex('CDN Network', 'section title', $this->text_domain); ?></span></h3>
						<div class="inside">
							<p><?php esc_attr_e( 'Our CDN (Content Delivery Network) option will make sure your images, CSS, and other files get served from one of a dozen datacenters around the world, whichever is closest to where each visitor is located.', $this->text_domain ); ?></p>

							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php _e( 'Enable CDN', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="enable_cdn">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Development Mode', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="enable_devmode">
										<span class="description"><?php esc_attr_e( 'Note: Development mode will automatically toggle off 3 hours after initial setup.', $this->text_domain ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Minify HTML', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="min_html">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Minify CSS', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="min_css">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Minify JavaScript', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="min_js">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Cache', $this->text_domain ); ?></th>
									<td>
										<button type="button" class="button button-default" data-action="clear_cache" data-success-message="<?php _e( 'Cache cleared successfully', $this->text_domain ); ?>"><?php _e( 'Clear Cache', $this->text_domain ); ?></button>
									</td>
								</tr>
							</table>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->

					<div class="postbox upress-section" id="upress-tab-optimization" data-check-availability="optimization_available" data-not-available-text="<?php _e( 'Optimization settings not available', $this->text_domain ); ?>">
						<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
						<div class="reload-btn"><a href="" title="<?php _e( 'Refresh section', $this->text_domain ); ?>"><span class="dashicons dashicons-update"></span></a></div>
						<!-- Toggle -->
						<h3 class="hndle"><span><?php _ex('Optimization', 'section title', $this->text_domain); ?></span></h3>
						<div class="inside">
							<p><?php esc_attr_e( 'Optimizes and boosts performance of your website.', $this->text_domain ); ?></p>

							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php _e( 'Enable Optimization', $this->text_domain ); ?></th>
									<td>
										<input type="checkbox" class="switch" data-action="enable_optimization">
										<span class="description"><?php _e( 'Note: This only toggles optimization on/off. <a href="https://my.upress.co.il/account/websites">Mange all optimization settings from uPress</a>', $this->text_domain ); ?></span>
									</td>
								</tr>
							</table>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables .ui-sortable -->
			</div>
			<!-- post-body-content -->
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<div class="postbox">
						<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br></div>
						<!-- Toggle -->
						<h3 class="hndle"><span><?php _ex('Resources and more information', 'section title', $this->text_domain); ?></span></h3>
						<div class="inside">
							<ul>
								<li><a href="https://upress.co.il"><?php _e( 'uPress Homepage', $this->text_domain ); ?></a></li>
								<li><a href="https://my.upress.co.il"><?php _e( 'Login to uPress', $this->text_domain ); ?></a></li>
							</ul>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables -->
			</div>
			<!-- #postbox-container-1 .postbox-container -->
		</div>
		<!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
	</div>

	<?php endif //$is_api_key_set ?>
</div>
<input type="hidden" id="upress-available" value="<?php echo (int)$upress_available; ?>">
<input type="hidden" id="api-key-valid" value="<?php echo (int)$is_api_key_correct; ?>">


<hr>
<span class="description"><?php esc_attr_e( '*Updates are queued and refreshed 1 minute after the update.', $this->text_domain ); ?></span>