<?php

namespace IdBasedEntity\Test;

use Entity\IdBasedEntity;
use Entity\Exception\InvalidCallException;
use PHPUnit\Framework\TestCase;

class IdBasedEntityTest extends TestCase
{
    public function test__get(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertEquals('a', $entity->a);

        $entity->b = 'd';
        $entity->a = 'd';

        $this->assertEquals('d', $entity->a);
        $this->assertEquals('d', $entity->b);

        $item = new Item(1);

        $this->assertEquals('succes', $item->magicProperty);
        $this->assertEquals('unset', $item->magicA);

        $item = new Item(1, ['writeOnlyProperty' => 1]);

        $this->assertEquals(1, $item->writeOnlyProperty);

        $item = new Item(1);

        $this->expectException(InvalidCallException::class);
        $this->expectExceptionMessage('Property: writeOnlyProperty is write only');
        echo $item->writeOnlyProperty;
    }

    public function test__set(): void
    {
        $item = new Item(1);

        $item->magicA = '5';
        $this->assertEquals('5', $item->magicA);

        $item->writeOnlyProperty = '6';

        $this->assertEquals('6', $item->testSetWriteOnlyProperty());

        $this->expectException(InvalidCallException::class);
        $this->expectExceptionMessage('Property: magicProperty is read only');
        $item->magicProperty = '5';
    }

    public function test__isset(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertTrue(isset($entity->a));
        $this->assertFalse(isset($entity->b));

        $item = new Item(1);

        $this->assertTrue(isset($item->magicProperty));
        $this->assertTrue(isset($item->magicA));
        $this->assertFalse(isset($item->writeOnlyProperty));
    }

    public function testJsonSerialize(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertJsonStringEqualsJsonString(json_encode(['a' => 'a']), json_encode($entity));

        $item = new Item(1, [
            'a' => 1,
            'b' => 0,
            'c' => -1,
        ]);

        $item->setHidden(['a']);

        $this->assertJsonStringEqualsJsonString(json_encode([
            'b' => 0,
            'c' => -1,
            'magicA' => 'unset',
            'magicProperty' => 'succes',
        ]), json_encode($item));

        $item->setVisible(['b']);

        $this->assertJsonStringEqualsJsonString(json_encode(['b' => 0]), json_encode($item));

        $item->setHidden([]);
        $this->assertJsonStringEqualsJsonString(json_encode(['b' => 0]), json_encode($item));

    }

    public function testOffsetExists(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertTrue(isset($entity['a']));
        $this->assertFalse(isset($entity['b']));
    }

    public function testOffsetGet(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertEquals('a', $entity['a']);

        $entity['b'] = 'd';
        $entity['a'] = 'd';

        $this->assertEquals('d', $entity['a']);
        $this->assertEquals('d', $entity['b']);

        $entity[] = 'g';
        $entity[] = 'f';

        $this->assertEquals('g', $entity[0]);
        $this->assertEquals('f', $entity[1]);
    }

    public function testOffsetUnset(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        unset($entity['a']);

        $this->assertJsonStringEqualsJsonString(json_encode([]), json_encode($entity));
    }

    public function testToArray(): void
    {
        $entity = new IdBasedEntity(1, ['a' => 'a']);

        $this->assertEquals(['a' => 'a'], $entity->toArray());
    }
}

/**
 * @property string magicProperty
 * @property string magicA
 * @property string writeOnlyProperty
 */
class Item extends IdBasedEntity
{

    /**
     * @var string
     */
    private $magicA;

    /**
     * @var string
     */
    private $magicSetter;

    public function setHidden(array $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function setVisible(array $visible): void
    {
        $this->visible = $visible;
    }

    public function getMagicProperty(): string
    {
        return "succes";
    }

    public function setMagicA(string $val): void
    {
        $this->magicA = $val;
    }

    public function getMagicA(): string
    {
        if ($this->magicA !== null) {
            return $this->magicA;
        }

        return 'unset';
    }

    public function setWriteOnlyProperty(string $val): void
    {
        $this->magicSetter = $val;
    }

    public function testSetWriteOnlyProperty(): string
    {
        return $this->magicSetter;
    }
}
