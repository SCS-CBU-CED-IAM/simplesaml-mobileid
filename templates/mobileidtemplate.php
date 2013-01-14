<?php
$this->data['header'] = $this->t('{mobileid:Auth:header}');
$this->data['autofocus'] = 'msisdn';
$this->includeAtTemplateBase('includes/header.php');

?>
	<img height="100" style="float: right" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/logo.gif')); ?>" />
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
    if ($this->data['errorcode'] !== NULL) {
?>

<img height="64" src="<?php echo(SimpleSAML_Module::getModuleURL('mobileid/resources/warning.png')); ?>" class="float-l" style="margin: 5px " />
<p><b><?php echo $this->t('{mobileid:errors:error_header}'); ?></b></p>
<p><?php echo $this->t('{mobileid:errors:descr_' . $this->data['errorcode'] . '}'); ?></p>

<?php
}
?>

<?php
$this->includeAtTemplateBase('includes/footer.php');
?>