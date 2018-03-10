<?php

namespace OpenTracing\Tests;

use OpenTracing\Exceptions\InvalidSpanOption;
use OpenTracing\NoopSpanContext;
use OpenTracing\SpanOptions;
use OpenTracing\Reference;
use PHPUnit_Framework_TestCase;

/**
 * @covers SpanOptions
 */
final class SpanOptionsTest extends PHPUnit_Framework_TestCase
{
    const REFERENCE_TYPE = 'a_reference_type';

    public function testSpanOptionsCanNotBeCreatedDueToInvalidOption()
    {
        $this->expectException(InvalidSpanOption::class);

        SpanOptions::create([
            'unknown_option' => 'value'
        ]);
    }

    public function testSpanOptionsWithInvalidCloseOnFinishOption()
    {
        $this->expectException(InvalidSpanOption::class);

        SpanOptions::create([
            'close_span_on_finish' => 'value'
        ]);
    }

    public function testSpanOptionsCanNotBeCreatedBecauseInvalidStartTime()
    {
        $this->expectException(InvalidSpanOption::class);

        SpanOptions::create([
            'start_time' => 'abc'
        ]);
    }

    /** @dataProvider validStartTime */
    public function testSpanOptionsCanBeCreatedBecauseWithValidStartTime($startTime)
    {
        $spanOptions = SpanOptions::create([
            'start_time' => $startTime
        ]);

        $this->assertEquals($spanOptions->getStartTime(), $startTime);
    }

    public function validStartTime()
    {
        return [
            [new \DateTime()],
            ['1499355363'],
            [1499355363],
            [1499355363.123456]
        ];
    }

    public function testSpanOptionsCanBeCreatedWithValidReference()
    {
        $context = NoopSpanContext::create();

        $options = [
            'references' => Reference::create(self::REFERENCE_TYPE, $context),
        ];

        $spanOptions = SpanOptions::create($options);
        $references = $spanOptions->getReferences()[0];

        $this->assertTrue($references->isType(self::REFERENCE_TYPE));
        $this->assertSame($context, $references->getContext());
    }

    public function testSpanOptionsDefaultCloseOnFinishValue()
    {
        $options = SpanOptions::create([]);

        $this->assertTrue($options->getCloseSpanOnFinish());
    }

    public function testSpanOptionsWithValidCloseOnFinishValue()
    {
        $options = SpanOptions::create([
            'close_span_on_finish' => false,
        ]);

        $this->assertFalse($options->getCloseSpanOnFinish());
    }

    public function testSpanOptionsAddsANewReference()
    {
        $context1 = NoopSpanContext::create();
        $spanOptions = SpanOptions::create([
            'child_of' => $context1,
        ]);
        $this->assertCount(1, $spanOptions->getReferences());

        $context2 = NoopSpanContext::create();
        $spanOptions = $spanOptions->withParent($context2);
        $this->assertCount(1, $spanOptions->getReferences());
        $this->assertSame($context2, $spanOptions->getReferences()[0]->getContext());
    }
}
