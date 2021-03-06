<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\TagRewriterExtension\Tests\Service;

use VIPSoft\TagRewriterExtension\Service\TagRewriterService;

/**
 * Tag Rewriter service test
 *
 * @group Service
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class TagRewriterServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * rewrite
     *
     * @param array $original Original tags
     * @param array $custom   Custom tags
     * @param array $expected Expected tags
     *
     * @dataProvider provideDataForRewrite
     */
    public function testRewrite($original, $custom, $expected)
    {
        $service = new TagRewriterService();
        $service->setTags($custom);

        $this->assertEquals($expected, $service->rewrite($original));
    }

    /**
     * data provider
     *
     * @return array
     */
    public function provideDataForRewrite()
    {
        return array(
            array(
                null, null, null,
            ),
            array(
                null, array('add' => array('add', 'this')), null,
            ),
            array(
                array('javascript'), null, array('javascript'),
            ),
            array(
                array('javascript'), array('javascript' => array('replace')), array('replace'),
            ),
            array(
                array('javascript'), array('javascript' => 'replace'), array('replace'),
            ),
            array(
                array('a', 'b'), array('a' => array('c')), array('c', 'b'),
            ),
            array(
                array('a', 'b'), array('a' => array('b', 'c')), array('b', 'c'),
            ),
            array(
                array('a', 'b'), array('a' => array('b', null, 'c')), array('b', 'c'),
            ),
            array(
                array('a', 'b'), array('a' => array('b', '', 'c')), array('b', 'c'),
            ),
            array(
                array('a', 'b'), array('a' => array('b', '0', 'c')), array('b', '0', 'c'),
            ),
            array(
                array('javascript'), array('javascript' => array('javascript', 'add')), array('javascript', 'add'),
            ),

            array(
                array('javascript'), array('javascript' => null), null,
            ),
            array(
                array('javascript'), array('javascript' => array()), null,
            ),
        );
    }
}
