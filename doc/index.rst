====================
TagRewriterExtension
====================

The TagRewriter extension allows tags to be replaced, removed, or augmented at
runtime.

Installation
============
This extension requires:

* Behat 2.4+
* Mink 1.4+

Through Composer
----------------
1. Set dependencies in your **composer.json**:

.. code-block:: js

    {
        "require": {
            ...
            "vipsoft/tag-rewriter-extension": "*"
        }
    }

2. Install/update your vendors:

.. code-block:: bash

    $ curl http://getcomposer.org/installer | php
    $ php composer.phar install

Through PHAR
------------
Download the .phar archive:

* `tag_rewriter_extension.phar <http://behat.org/downloads/tag_rewriter_extension.phar>`_

Configuration
=============
Activate extension in your **behat.yml** and define your tags:

.. code-block:: yaml

    # behat.yml
    default:
      # ...
      extensions:
        VIPSoft\TagRewriterExtension\Extension:
          tags: ~

Settings
--------
* To replace a tag, simply don't include the original tag in the set of new tags:

.. code-block:: yaml

          tags:
            # replace @xbrowser with @winxpie @firefox @chrome
            xbrowser: winxpie firefox chrome

* To augment a tag, include the original tag in the set of new tags:

.. code-block:: yaml

          tags:
            # augment @javascript with @winxpie @firefox @chrome
            javascript: javascript winxpie firefox chrome

* To remove a tag, use null:

.. code-block:: yaml

          tags:
            # remove @javascript
            javascript: ~

Source
======
`Github <https://github.com/vipsoft/GherkinTagRewriterExtension>`_

Copyright
=========
Copyright (c) 2012 Anthon Pang.  See **LICENSE** for details.

Contributors
============
* Anthon Pang `(robocoder) <http://github.com/robocoder>`_
* `Others <https://github.com/vipsoft/GherkinTagRewriterExtension/graphs/contributors>`_
