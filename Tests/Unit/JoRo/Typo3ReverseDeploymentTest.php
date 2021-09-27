<?php

namespace Unit\JoRo;

use JoRo\Typo3ReverseDeployment;
use PHPUnit\Framework\TestCase;

class Typo3ReverseDeploymentTest extends TestCase
{
    /**
     * @var Typo3ReverseDeployment
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = new Typo3ReverseDeployment;
    }

    /**
     * @test
     */
    public function sqlExcludeTableSetterAndGetter(): void
    {
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
    public function folderSetExcludeOverrideAddExclude(): void
    {
        $testAddExcludeArray = ['foo', 'bar', 'test'];
        $testSetExcludeArray = ['folder1', 'folder2'];
        $this->subject->addExclude($testAddExcludeArray);
        $this->subject->setExclude($testSetExcludeArray);

        $this->assertSame($testSetExcludeArray, $this->subject->getExclude());
    }

    /**
     * Test if the temporary local path has a trailing slash
     * @test
     */
    public function hasLocalTemporaryPathATrailingSlash(): void
    {
        $testPathWithoutSlash = '/var/www/my-project';
        $expectedPath = $testPathWithoutSlash . '/';

        $this->subject->setLocalTempPath($testPathWithoutSlash);
        $this->assertSame($expectedPath, $this->subject->getLocalTempPath());

        $testPathWithSlash = '/var/www/my-project/';
        $this->subject->setLocalTempPath($testPathWithSlash);
        $this->assertSame($expectedPath, $this->subject->getLocalTempPath());
    }
}
