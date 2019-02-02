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
        $this->assertEquals($testArray, $this->subject->getSqlExcludeTable());
    }

    /**
     * Test if the temporary local path has an trailing slash
     * @test
     */
    public function hasLocalTemporaryPathATrailingSlash()
    {
        $testPathWithoutSlash= '/var/www/my-project';
        $assertetPath = $testPathWithoutSlash . '/';

        $this->subject->setLocalTempPath($testPathWithoutSlash);
        $this->assertSame($assertetPath, $this->subject->getLocalTempPath());

        $testPathWithSlash= '/var/www/my-project/';
        $this->subject->setLocalTempPath($testPathWithSlash);
        $this->assertSame($assertetPath, $this->subject->getLocalTempPath());
    }
}
