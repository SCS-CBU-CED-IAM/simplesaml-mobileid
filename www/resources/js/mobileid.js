/**
 * @version     1.0.0
 * @package     mobileid-simplesamlphp
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

(function($){
	$(document).on('submit', '#mobileid_form', function(e) {
		e.preventDefault()
		prepareSubmit();
	})

	$(document).on('click', '#submit_btn_send', function(e) {
		e.preventDefault()
		$('#mobileid_form').submit();
	})

	$(document).on('click', '#submit_btn_cancel', function(e) {
		e.preventDefault()
		$('#mobileid_form').attr('action', 'cancel.php');
		$('#mobileid_form').submit();
	})

	function prepareSubmit() {
		// Show waiting message
		$('#msg_wait').show();
		$('#msg_error').hide();
		// Disable submit and clear button
		$('#submit_btn_send').attr('disabled', 'true');
		$('#submit_btn_cancel').attr('disabled', 'true');
	}
}(jQuery))
