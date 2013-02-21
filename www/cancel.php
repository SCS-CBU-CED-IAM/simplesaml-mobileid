<?php
$e = new SimpleSAML_Error_UserAborted();
SimpleSAML_Auth_State::throwException(array(), $e);
?>
