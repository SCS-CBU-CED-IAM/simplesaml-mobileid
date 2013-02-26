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
?>

<img style="height:100px; display:inline;padding:0px;margin:0 20px 0 0; float:right;" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />

<div class="mobileid-main">
<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?></h2>
<div id="msg_error" class="alert alert-block alert-error">
        <button type="button" class="close" data-dismiss="alert">×</button>
	<strong><?php echo $this->t('{mobileid:errors:error_header}'); ?></strong>
	<p><?php echo $this->t('{mobileid:errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
</div>
<<<<<<< HEAD
<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?>
	<img style="height:80px; padding:0px;margin:0 30px 0 0; float:right;" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />
</h2>
<form action="?" method="post" name="f" id="mobileid_form">
	<table>
		<tbody>
			<tr width="100%">
				<td style="padding: .3em;"><?php echo $this->t('{mobileid:Auth:intro}'); ?></td>
				<td><input id="msisdn" size="30" name="msisdn" tabindex="1" class="msisdn mobileid-input" type="tel" value="<?php if (isset($_COOKIE["msisdn"])) echo $_COOKIE["msisdn"]; ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_cancel}'); ?>" class="float-l mobileid-btn-cancel" id="submit_btn_cancel" />
					<input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_send}'); ?>" class="float-r mobileid-btn-send" id="submit_btn_send" />
				</td>
			</tr>
		</tbody>
	</table>
=======
<form action="?" method="post" name="f" id="mobileid_form">
	
              <div class="control-group">
			<div class="mobileid-controls">
				<h4><?php echo $this->t('{mobileid:Auth:intro}'); ?></h4>
				<input id="msisdn" name="msisdn" tabindex="1" class="mobileid-msisdn" type="tel" value="<?php if (isset($msisdn_cookie)) echo $msisdn_cookie; ?>" />
                                <input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_send}'); ?>" class="float-r mobileid-btn-send" id="submit_btn_send" />
			</div>
               </div>
                                
                 <div class="form-actions">       
                        
                       <input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_cancel}'); ?>" class="float-l mobileid-btn-cancel" id="submit_btn_cancel" />
			
		</div>
                                        
>>>>>>> Content-Layout changes
	<?php
	foreach ($this->data['stateparams'] as $name => $value) {
		echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
	}
	?>
</form>
<div id="msg_wait" style="display:none;">
	<img class="mobileid-ajax-loader" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/ajax-loader.gif')); ?>" alt="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" title="<?php echo $this->t('{mobileid:Auth:msg_wait}'); ?>" />
	<p><?php echo $this->t('{mobileid:Auth:msg_wait}'); ?></p>
</div>
</div>
<?php if ($this->data['errorcode'] !== NULL) { ?>
<script>
	jQuery('#msg_wait').hide();
	jQuery('#msg_error').show();
</script>
<?php } ?>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
