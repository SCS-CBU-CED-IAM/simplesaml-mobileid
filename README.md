swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to project documentation for more details:
 * http://simplesamlphp.org/docs/stable/simplesamlphp-modules
 * http://simplesamlphp.org/docs/stable/simplesamlphp-authsource

## Overview

mobileid:auth is a module for login with Mobile ID.


## Install
Checkout directly from git under the simplesamlphp modules folder with git clone <git> mobileid

Enable the cas module:
  `touch modules/mobileid/default-enabled`


## Configuration

Add the module in the sources `config/authsources.php`:

    'MobileID' => array(
        'mobileid:Auth',
        ...
    ),
