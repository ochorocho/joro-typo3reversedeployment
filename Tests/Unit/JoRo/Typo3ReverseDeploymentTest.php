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
     * Check if the setExclude function override the exclude definition
     * with function addExclude
     * @test
     */
    public function folderSetExcludeOverrideAddExclude()
    {
        $testAddExcludeArray = ['foo', 'bar', 'test'];
        $testSetExcludeArray = ['folder1', 'folder2'];
        $this->subject->addExclude($testAddExcludeArray);
        $this->subject->setExclude($testSetExcludeArray);

        $this->assertSame($testSetExcludeArray, $this->subject->getExclude());
    }

    /**
     * Test if the temporary local path has an trailing slash
     * @test
     */
    public function hasLocalTemporaryPathATrailingSlash()
    {
        $testPathWithoutSlash= '/var/www/my-project';
        $expectedPath = $testPathWithoutSlash . '/';

        $this->subject->setLocalTempPath($testPathWithoutSlash);
        $this->assertSame($expectedPath, $this->subject->getLocalTempPath());

        $testPathWithSlash= '/var/www/my-project/';
        $this->subject->setLocalTempPath($testPathWithSlash);
        $this->assertSame($expectedPath, $this->subject->getLocalTempPath());
    }
}
