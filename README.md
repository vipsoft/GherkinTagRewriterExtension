# TagRewriterExtension

The TagRewriter extension allows tags to be replaced, removed, or augmented at
runtime.

## Installation

This extension requires:

* Behat 2.4+
* Mink 1.4+

### Through Composer

1. Set dependencies in your `composer.json`:

    ``` json
    {
        "require": {
            ...
            "vipsoft/tag-rewriter-extension": "*"
        }
    }
    ```

2. Install/update your vendors:

    ``` bash
    $> curl http://getcomposer.org/installer | php
    $> php composer.phar install
    ```

3. Activate extension in your `behat.yml` and define your tags:

    ``` yaml
    # behat-client.yml
    default:
      # ...
      extensions:
        VIPSoft\TagRewriterExtension\Extension:
          tags: ~
    ```


## Configuration

To replace a tag, simply don't include the original tag in the set of new tags:

    ``` yaml
          tags:
            # replace @xbrowser with @winxpie @firefox @chrome
            xbrowser: winxpie firefox chrome
    ```

To augment a tag, include the original tag in the set of new tags:

    ``` yaml
          tags:
            # augment @javascript with @winxpie @firefox @chrome
            javascript: javascript winxpie firefox chrome
    ```

To remove a tag, use null:

    ``` yaml
          tags:
            # remove @javascript
            javascript: ~
    ```

## Copyright

Copyright (c) 2012 Anthon Pang.  See LICENSE for details.

## Contributors

* Anthon Pang [robocoder](http://github.com/robocoder)
