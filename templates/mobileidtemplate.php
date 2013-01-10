<?php
$this->data['header'] = $this->t('{mobileid:Auth:header}');
$this->data['autofocus'] = 'msisdn';
$this->includeAtTemplateBase('includes/header.php');
?>

<?php
if ($this->data['errorcode'] !== NULL) {
?>

    <div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5">
		<img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png" class="float-l" style="margin: 15px " />
		<h2><?php echo $this->t('{login:error_header}'); ?></h2>
		<p><b><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></b></p>
		<p><?php echo $this->t('{errors:descr_' . $this->data['errorcode'] . '}'); ?></p>
	</div>

<?php
}
?>
	<img style="float: right" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />
	<h2 style=""><?php echo $this->t('{mobileid:Auth:header}'); ?></h2>
	<form action="?" method="post" name="f">
        <p><?php echo $this->t('{mobileid:Auth:intro}'); ?></p>
        <p><input id="msisdn" style="border: 1px solid #ccc; background: #eee; padding: .5em; font-size: medium; width: 70%; color: #aaa" type="text" tabindex="2" name="msisdn" /></p>
<?php
foreach ($this->data['stateparams'] as $name => $value) {
	echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
}
?>
	</form>

<?php
$this->includeAtTemplateBase('includes/footer.php');
?>