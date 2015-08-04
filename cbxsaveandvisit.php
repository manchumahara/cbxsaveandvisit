<?php

/**
 * Plugin Name: CBX Save and Visit
 * Plugin URI:  http://wpboxr.com/product/cbx-save-and-visit
 * Description: Adds a button to the Edit Post page which saves the post and redirects back to the post details page.
 * Version:     1.0.2
 * Author:      wpboxr
 * Author URI:  http://wpboxr.com
 * License:     GPL2
 */

// Set up the plugin if the user has access to the admin area
add_action('admin_init', array('CBXSaveandVisit', 'init'));

class CBXSaveandVisit {

	/**
	 * Hook into various WordPress events
	 */
	public static function init() {
		add_action('post_submitbox_misc_actions', array('CBXSaveandVisit', 'add_button')); // add button
		add_filter('redirect_post_location', array('CBXSaveandVisit', 'redirect'), '99'); // change redirect URL

	}

	/**
	 * Adds the custom button into the post edit page
	 */
	public static function add_button() {
		// work out if post is published or not
		$post_id = (int) $_GET['post'];
		$status = get_post_status($post_id);

		$button_label = ($status == 'publish' || $status == 'private') ? 'Update and Visit' : 'Publish and Visit';

		?>

		<div id="major-publishing-actions" style="overflow:hidden">
			<div id="publishing-action">
				<input type="submit" tabindex="5" value="<?php echo $button_label ?>" class="button-primary" id="cbxsaveandvisitbtn" name="save-visit" />
			</div>
		</div>

		<?php
	}

	/**
	 * Generates the URL to redirect to
	 * @param $location The redirect location (we're overwriting this)
	 * @return string The new URL to redirect to, which should be the post listing page of the relevant post type
	 */
	public static function redirect($location) {
		if (!isset($_POST['save-visit'])) return $location;


		// we want to publish new posts
		$post_status = 'publish';
		$post_id = (int) $_POST['post_ID'];


		// if the post was published, allow the status to be changed to something else (eg. draft)
		if (get_post_status_object($_POST['original_post_status']) && ($_POST['original_post_status'] == 'publish' || $_POST['original_post_status'] == 'private')) {
			//$post_status = $_POST['post_status'];
			$post_status = sanitize_text_field($_POST['post_status']);
		}
		// handle private post visibility
		if (get_post_status_object($_POST['post_status']) && $_POST['post_status'] == 'private') {
			$post_status = 'private';
		}

		wp_update_post(array('ID' => $post_id, 'post_status' => $post_status));
		return get_permalink($post_id);

	}

}