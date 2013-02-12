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

$this->data['head']  = '<link rel="stylesheet" media="screen" type="text/css" href="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/css/bootstrap.min.css')  . '" />';
$this->data['head'] .= '<link rel="stylesheet" media="screen" type="text/css" href="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/css/bootstrap-responsive.min.css')  . '" />';
$this->data['head'] .= '<link rel="stylesheet" media="screen" type="text/css" href="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/css/custom.css')  . '" />';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/jquery/jquery-1.8.3.min.js') . '"></script>';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/bootstrap.min.js') . '"></script>';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/mobileid.js') . '"></script>';
$this->data['header'] = $this->t('{mobileid:Auth:header}');
$this->data['autofocus'] = 'msisdn';
$this->data['hideLanguageBar'] = true;
$this->includeAtTemplateBase('includes/header.php');
?>
<img class="logo" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />
<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?></h2>
<form action="?" method="post" name="f" id="mobileid_form">
    <p><?php echo $this->t('{mobileid:Auth:intro}'); ?></p>
	<fieldset>
		<div class="control-group">
			<div class="controls">
				<input id="msisdn" name="msisdn" class="msisdn" type="tel" required />
			</div>
		</div>
		<div class="form-actions">
			<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_send}'); ?>" class="btn" id="submit_btn_send" />
			<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_cancel}'); ?>" class="btn" id="submit_btn_cancel" />
		</div>
		<?php
		foreach ($this->data['stateparams'] as $name => $value) {
			echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
		}
		?>
	</fieldset>
</form>
<div id="msg_wait">
	<img src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/ajax-loader.gif')); ?>" alt="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" title="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" />
	<p><?php echo $this->t('{mobileid:Auth:msg_wait}'); ?></p>
</div>
<?php if ($this->data['errorcode'] !== NULL) { ?>
<script>
	jQuery('#msg_wait').hide();
</script>
<div id="msg_error" class="alert alert-block alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<strong><?php echo $this->t('{mobileid:errors:error_header}'); ?></strong>
	<p><?php echo $this->t('{mobileid:errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
</div>
<?php } ?>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
