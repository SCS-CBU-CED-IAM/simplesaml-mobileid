swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to project documentation for more details:
 * http://simplesamlphp.org/docs/stable/simplesamlphp-modules
 * http://simplesamlphp.org/docs/stable/simplesamlphp-authsource

## Overview

mobileid:auth is a module for login with Mobile ID.


## Install
Checkout the project directly from git under the `simplesamlphp/modules` folder

Enable the cas module:
  `touch modules/mobileid/default-enabled`


## Configuration

Add the module in the sources `config/authsources.php`:

```
'MobileID' => array(
    'mobileid:Auth',
    'cert_file'    => '/opt/mobileid/mycert.crt',
    'cert_key'     => '/opt/mobileid/mycert.key',
    'mid_ca'       => '/opt/mobileid/swisscom-ca.crt',
    'mid_ocsp'     => '/opt/mobileid/swisscom-ocsp.crt',
    'ap_id'        => '<ID provided by Swisscom>',
    'ap_pwd'       => '<Password provided by Swisscom>',
    ),
```

Optional configuration elements
```
    'timeout_ws'   => 90,
    'timeout_mid'  => 80
```

## Template support

<TBD>