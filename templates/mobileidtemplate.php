<?php
/**
 * This is the associated template page for the Mobile ID login form
 *
 * @version     1.0.2
 * @package     simpleSAMLphp-mobileid
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
 * @author      Swisscom (Schweiz) AG
 */

$this->data['head']  = '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/jquery/jquery-1.8.3.min.js') . '"></script>';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/js/mobileid.js') . '"></script>';
$this->data['head'] .= '<link rel="stylesheet" href="' . SimpleSAML_Module::getModuleUrl('mobileid/resources/css/mobileid.css') . '"/>';
$this->data['header'] = $this->t('{mobileid:Auth:header}');
$this->data['autofocus'] = 'msisdn';
$this->includeAtTemplateBase('includes/header.php');

$this->data['cancel'] = '';
if (isset($_SESSION['enable_cancel']) && $_SESSION['enable_cancel']) {
    $this->data['cancel'] = '<input type="button" value="' . $this->t('{mobileid:Auth:form_btn_cancel}') . '" class="float-l mobileid-btn-cancel" id="submit_btn_cancel" />';
}

if ($this->data['errorcode'] !== NULL && array_key_exists('msisdn', $_REQUEST)) {
    $_COOKIE["msisdn"] = $_REQUEST['msisdn'];
}

/* Error description */
$errorDescr = $this->t('{mobileid:errors:descr_' . $this->data['errorcode'] . '}');
if (array_key_exists('errorurl', $this->data))
    $errorDescr = str_replace('#URL#', $this->data['errorurl'], $errorDescr);
if (array_key_exists('mcc', $this->data))
    $errorDescr = str_replace('#MCC#', $this->data['mcc'], $errorDescr);
if (array_key_exists('mnc', $this->data))
    $errorDescr = str_replace('#MNC#', $this->data['mnc'], $errorDescr);
?>
<style type="text/css">
	#msg_wait{display:none;}
</style>
<div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5; display:none;" id="msg_error">
    <img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" class="float-l" style="margin: 15px " />
    <h2><?php echo $this->t('{mobileid:errors:error_header}'); ?></h2>
    <p><?php echo $errorDescr; ?></p>
</div>
<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?>
    <img style="height:28px; float:right;" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/mobileid.png')); ?>" />
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
                    <?php
                    if ($this->data['cancel'] !== '')
                        echo($this->data['cancel']);
                    ?>
                    <input type="button" value="<?php echo $this->t('{mobileid:Auth:form_btn_send}'); ?>" class="float-r mobileid-btn-send" id="submit_btn_send" />
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
<div id="msg_wait">
    <div id="spinner"></div>
    <p><?php echo $this->t('{mobileid:Auth:msg_wait}'); ?></p>
</div>
<?php if ($this->data['errorcode'] !== NULL) { ?>
<script>
    jQuery('#msg_wait').hide();
    jQuery('#msg_error').show();
</script>
<?php } ?>
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>
