================================
Building a Behat 2.4 Extension
================================

This is a walkthrough of how I built a simple Behat 2.4 extension that
rewrites Gherkin tags, such that:

::

    @javascript
    Feature: tags

would be loaded and rewritten (in memory) at runtime as if we had written:

::

    @javascript @firefox
    Feature: tags

With this extension, we'll be able to add, replace, and remove tags.

Note:  This is not a tutorial.  I will gloss over details.  However, I have
open sourced the extension, so you can follow along as I ramble about
dependency injection and best practices, and share some of my thought processes.


Let's Get Started!
==================
First, I create the top level directory for my extension.  I chose the name,
``GherkinTagRewriterExtension``.  Choosing a descriptive name should take less
time than brainstorming a "catchy" one.

Best practice dictates that I create a ``src`` folder to organize my code.
Below that, I create a directory hierarchy for the project's namespace.  In this
case, I chose ``VIPSoft\TagRewriterExtension``.

Every Behat extension has an ``Extension`` class that implements the ``ExtensionInterface``.
This is where it sets up and loads the extension's configuration.  I decide
that my config file will have a ``tags`` section, followed by an array of tags,
where the key would be the original tag, and the value would be a single tag (string),
an array of replacement tags, or null.

Thus, my ``behat.yml`` file might contain:

::

    default:
      extensions:
        VIPSoft\TagRewriterExtension\Extension:
          tags:
            # add @newTag when you see @originalTag1
            originalTag1:
              - originalTag1
              - newTag

            # replace @originalTag2 with @replacementTag
            originalTag2: replacementTag

            # remove @deleteThisTag
            deleteThisTag: ~

To parse and load my configuration, we implement the following methods in
``Extension.php``:

::

    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                arrayNode('tags')->
                    useAttributeAsKey('name')->
                    prototype('variable')->end()->
                end()->
            end()->
        end();
    }

    function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
        $loader->load('core.xml');

        $container->setParameter('behat.tagrewriter.tags', $config['tags']);
    }

The configuration details will vary for your own extensions.  Refer to the
Symfony Config component for more information.  I won't need any Mink compiler
passes, so getCompilerPasses() is just a stub method that returns an empty array.

Now, we'll create ``services/core.xml`` (and expand it later):

::

    <?xml version="1.0" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <parameters>
            <parameter key="behat.tagrewriter.tags"></parameter>
        </parameters>

    </container>

And that's the skeleton.


Dependency Injection
====================
We know what we want do, but how do we do it?

Well, we know that Behat 2.4 uses dependency injection to configure just about
everything.  Theoretically, we should be able to override Behat's default
parameters, classes, and/or services with whatever we want, to do whatever we
want.

With that assumption, I browse the source code to find out where tags are stored
when a ``.feature`` file is loaded.  I soon learn in the Behat framework that
tags are stored in tokens by Gherkin's ``Lexer.php``.

Now I check my assumption.  I grep for ``Behat\Gherkin\Lexer`` and (pleasantly)
find ``vendor/behat/behat/src/Behat/Behat/DependencyInjection/config/behat.xml``
contains:

::

    <parameter key="gherkin.lexer.class">Behat\Gherkin\Lexer</parameter>

This means I can override ``gherkin.lexer.class``.  Looking good.

I create ``Gherkin/Lexer.php`` as a subclass of ``Behat\Gherkin\Lexer``, and
stub the inherited method that I want to override/extend:

::

    protected function scanTags()
    {
        $token = parent::scanTags();

        if ($token) {
            return $token;
        }
    }

Hang on.  I need to access my configuration parameters.  Please tell me the Lexer
is a service.  Yes!  I find:

::

    <service id="gherkin.lexer" class="%gherkin.lexer.class%">

This means we can pass arguments to the constructor, and/or call our own setters.
For the sake of clarity, I add a setContainer() method to Lexer.php.  I then add
the following parameters and services to ``core.xml``:

::

        ...
        <parameter key="gherkin.lexer.class">VIPSoft\TagRewriterExtension\Gherkin\Lexer</parameter>
    </parameters>

    <services>
        <service id="gherkin.lexer" class="%gherkin.lexer.class%">
            <argument type="service" id="gherkin.keywords" />
            <call method="setContainer">
                <argument type="service" id="service_container" />
            </call>
        </service>
        ...


Service Oriented Architecture
=============================
Here is where the influence of my day job kicks in.  At Instaclick, our
Scrum developers routinely build SOA services, follow "object calisthenics",
and write tests.

I create a stub ``Service/TagRewriterService.php`` with a rewrite() method,
and then write data-driven unit tests in ``Tests/Service/TagRewriterServiceTest.php``.

At this point, the unit tests will obviously fail.

I revisit the rewrite() method, and initially code:

::

    public function rewrite($tags)
    {
        $newTags = array();

        foreach ($tags as $tag) {
            if (isset($this->tags[$tag])) {
                $newTags = array_merge($newTags, $this->tags[$tag]);
            } else {
                $newTags[] = $tag;
            }
        }

        return count($newTags) ? $newTags : null;
    }

But the unit tests still fail!  DOH.  However, this is the beauty of TDD.
We write tests first to codify our expectations.  We then write the code to
later test those assumptions.

Once the tests passed (and I couldn't think of more tests to add), I can
refactor with confidence.

::

    public function rewrite($tags)
    {
        $newTags = array();

        foreach ((array) $tags as $tag) {
            $newTags = array_merge($newTags, array_key_exists($tag, (array) $this->tags) ? (array) $this->tags[$tag] : array($tag));
        }

        $newTags = array_unique($newTags);

        return count($newTags) ? $newTags : null;
    }


Almost Done!
============
We have to configure our service and wire up the lexer to use the service.

In ``services/core.xml``, I add:

::

        ...
        <parameter key="behat.tagrewriter.service.tagrewriter.class">VIPSoft\TagRewriterExtension\Service\TagRewriterService</parameter>
    </parameters>

    <services>
        <service id="behat.tagrewriter.service.tagrewriter" class="%behat.tagrewriter.service.tagrewriter.class%">
            <call method="setTags">
                <argument>%behat.tagrewriter.tags%</argument>
            </call>
        </service>
        ...

The service name may look like it is repeating itself, but it follows the pattern
of ``behat.name_of_extension.service.name_of_service``.

Finally, in ``Gherkin/Lexer.php``, we have:

::

    protected function scanTags()
    {
        $token = parent::scanTags();

        if ($token) {
            $token->tags = $this->container->get('behat.tagrewriter.service.tagrewriter')->rewrite($token->tags);

            return $token;
        }
    }

And there you have it.


Open Source It!
===============
In the top level directory, I include:

* ``LICENSE`` (i.e., this extension is released under the MIT license)
* ``README.md``
* a sample configuration in ``behat.yml.dist``, and
* ``composer.json``


References
==========
* `Behat code <http://github.com/behat/>`_
* `Behat documentation <http://docs.behat.org/>`_ 
* `Behat 2.4: The most extendable testing framework <http://everzet.com/post/22899229502/behat-240>`_
