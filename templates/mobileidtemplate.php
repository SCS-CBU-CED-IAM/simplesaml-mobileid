<?php
/**
 * This is the associated template page for the Mobile ID login form
 *
 * @version     1.0.0
 * @package     simpleSAMLphp-mobileid
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

$this->data['head']  = '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/jquery/jquery-1.8.3.min.js') . '"></script>';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/mobileid.js') . '"></script>';
$this->data['header'] = $this->t('{mobileid:Auth:header}');
$this->data['autofocus'] = 'msisdn';
$this->includeAtTemplateBase('includes/header.php');

if (isset($_COOKIE["msisdn"])) {
	if (array_key_exists('msisdn', $_REQUEST)) {
		$msisdn_cookie = $_REQUEST['msisdn'];
	} else {
		$msisdn_cookie = $_COOKIE["msisdn"];
	}
}
?>
<div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5; display:none;" id="msg_error">
	<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" class="float-l" style="margin: 15px " />
	<h2><?php echo $this->t('{mobileid:errors:error_header}'); ?></h2>
	<p><?php echo $this->t('{mobileid:errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
</div>
<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?></h2>
<img style="height:50px; padding:2px; float:right;" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />
<form action="?" method="post" name="f" id="mobileid_form">
	<table>
		<tbody>
			<tr width="100%">
				<td style="padding: .3em;"><?php echo $this->t('{mobileid:Auth:intro}'); ?></td>
				<td><input id="msisdn" size="30" name="msisdn" tabindex="1" class="msisdn" type="tel" value="<?php if (isset($msisdn_cookie)) echo $msisdn_cookie; ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_cancel}'); ?>" class="float-l" id="submit_btn_cancel" />
					<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_send}'); ?>" class="float-r" id="submit_btn_send" />
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	foreach ($this->data['stateparams'] as $name => $value) {
		echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
	}
	?>
</form>
<div id="msg_wait" style="display:none;">
	<img src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/ajax-loader.gif')); ?>" alt="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" title="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" />
	<p><?php echo $this->t('{mobileid:Auth:msg_wait}'); ?></p>
</div>
<?php if ($this->data['errorcode'] !== NULL) { ?>
<script>
	jQuery('#msg_wait').hide();
	jQuery('#msg_error').show();
</script>
<?php } ?>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
