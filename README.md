swisscom-mobileid-simplesamlphp
===============================

Mobile ID custom auth module for simplesamlphp

Refer to http://simplesamlphp.org/docs/stable/simplesamlphp-modules for details.

## Overview

mobileid:auth is a module that only asks about MSISDN
mobileid:AuthAlias is module where the username can be aliased in a database and optional validation of a password.



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
