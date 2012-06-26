<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\TagRewriterExtension\Service;

/**
 * Tag Rewriter service
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class TagRewriterService
{
    /**
     * Tags
     *
     * @var array
     */
    private $tags;

    /**
     * Set tags
     *
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Rewrite tags
     *
     * @param array $tags
     *
     * @return array
     */
    public function rewrite($tags)
    {
        $newTags = array();

        foreach ((array) $tags as $tag) {
            $newTags = array_merge($newTags, array_key_exists($tag, (array) $this->tags) ? (array) $this->tags[$tag] : array($tag));
        }

        $newTags = array_values(array_unique(array_filter($newTags, 'strlen')));

        return count($newTags) ? $newTags : null;
    }
}
