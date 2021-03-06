<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PropertyInfo\Tests\Extractors;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PropertyInfo\Extractors\DoctrineExtractor;
use PropertyInfo\Type;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DoctrineExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineExtractor
     */
    private $extractor;

    public function setUp()
    {
        $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__.DIRECTORY_SEPARATOR.'Fixtures'), true);
        $entityManager = EntityManager::create(array('driver' => 'pdo_sqlite'), $config);

        $this->extractor = new DoctrineExtractor($entityManager->getMetadataFactory());
    }

    public function testGetProperties()
    {
        $this->assertEquals(
             array(
                'id',
                'guid',
                'time',
                'json',
                'bool',
                'binary',
                'foo',
                'bar',
            ),
            $this->extractor->getProperties('PropertyInfo\Tests\Fixtures\DoctrineDummy')
        );
    }

    /**
     * @dataProvider typesProvider
     */
    public function testExtract($property, array $type = null)
    {
        $this->assertEquals($type, $this->extractor->getTypes('PropertyInfo\Tests\Fixtures\DoctrineDummy', $property, array()));
    }

    public function typesProvider()
    {
        return array(
            array('id', array(new Type(Type::BUILTIN_TYPE_INT))),
            array('guid', array(new Type(Type::BUILTIN_TYPE_STRING))),
            array('bool', array(new Type(Type::BUILTIN_TYPE_BOOL))),
            array('binary', array(new Type(Type::BUILTIN_TYPE_RESOURCE))),
            array('json', array(new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true))),
            array('foo', array(new Type(Type::BUILTIN_TYPE_OBJECT, false, 'PropertyInfo\Tests\Fixtures\DoctrineRelation'))),
            array('bar', array(new Type(
                Type::BUILTIN_TYPE_OBJECT,
                false,
                'Doctrine\Common\Collections\Collection',
                true,
                new Type(Type::BUILTIN_TYPE_INT),
                new Type(Type::BUILTIN_TYPE_OBJECT, false, 'PropertyInfo\Tests\Fixtures\DoctrineRelation')
            ))),
            array('notMapped', null),
        );
    }
}
