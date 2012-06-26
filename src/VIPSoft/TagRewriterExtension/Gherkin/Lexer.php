<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\TagRewriterExtension\Gherkin;

use Behat\Gherkin\Lexer as BaseLexer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lexer subclass.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class Lexer extends BaseLexer
{
    private $container;

    /**
     * Set container
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function scanTags()
    {
        $token = parent::scanTags();

        if ($token) {
            $token->tags = $this->container->get('behat.tagrewriter.service.tagrewriter')->rewrite($token->tags);

            return $token;
        }
    }
}
