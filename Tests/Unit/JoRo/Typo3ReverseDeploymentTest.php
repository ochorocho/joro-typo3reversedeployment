<?php

namespace JoRo\Tests\Unit;

use JoRo\Typo3ReverseDeployment;
use PHPUnit\Framework\TestCase;

class Typo3ReverseDeploymentTest extends TestCase
{
    /**
     * @var Typo3ReverseDeployment
     */
    protected $subject;

    public function setUp() {
        $this->subject = new Typo3ReverseDeployment;
    }

    /**
     * @test
     */
    public function sqlExcludeTableSetterAndGetter() {
        // set value
        $testArray = ['foo', 'bar'];
        $this->subject->setSqlExcludeTable($testArray);
        // test getter
        $this->assertEquals([], $this->subject->getSqlExcludeTable());
    }
}
