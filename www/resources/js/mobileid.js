/**
 * @version     1.0.0
 * @package     mobileid-simplesamlphp
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

jQuery(document).ready(function() {

	// Submit the form
	jQuery('#mobileid_form').submit(function() {
		prepareSubmit();
	});

	// Click on submit form button
	jQuery('#submit_btn_send').click(function() {
		jQuery('#mobileid_form').submit();
	});

	// Click on submit form button
	jQuery('#submit_btn_cancel').click(function() {
		jQuery("#mobileid_form").attr("action", "cancel.php");
		jQuery('#mobileid_form').submit();
	});
});

function prepareSubmit() {

	// Show waiting message
	jQuery('#msg_wait').show();
	jQuery('#msg_error').hide();

	// Disable submit and clear button
	jQuery('#submit_btn_send').attr("disabled", "true");
	jQuery('#submit_btn_cancel').attr("disabled", "true");
}
