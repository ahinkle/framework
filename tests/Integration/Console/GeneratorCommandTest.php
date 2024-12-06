<?php

namespace Illuminate\Tests\Integration\Console;

use Orchestra\Testbench\TestCase;
use Illuminate\Console\GeneratorCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;

class GeneratorCommandTest extends TestCase
{
    use InteractsWithPublishedFiles;

    protected $files = [
        'app/Console/Commands/FooCommand.php',
        'resources/views/foo/php.blade.php',
        'tests/Feature/fixtures.php/SomeTest.php',
    ];

    public function testItChopsPhpExtension()
    {
        $this->artisan('make:command', ['name' => 'FooCommand.php'])
            ->assertExitCode(0);

        $this->assertFilenameExists('app/Console/Commands/FooCommand.php');

        $this->assertFileContains([
            'class FooCommand extends Command',
        ], 'app/Console/Commands/FooCommand.php');
    }

    public function testItChopsPhpExtensionFromMakeViewCommands()
    {
        $this->artisan('make:view', ['name' => 'foo.php'])
            ->assertExitCode(0);

        $this->assertFilenameExists('resources/views/foo/php.blade.php');
    }

    public function testItOnlyChopsPhpExtensionFromFilename()
    {
        $this->artisan('make:test', ['name' => 'fixtures.php/SomeTest'])
            ->assertExitCode(0);

        $this->assertFilenameExists('tests/Feature/fixtures.php/SomeTest.php');

        $this->assertFileContains([
            'class SomeTest extends TestCase',
        ], 'tests/Feature/fixtures.php/SomeTest.php');
    }

    #[DataProvider('reservedNamesDataProvider')]
    public function testItCannotGenerateClassUsingReservedName($given)
    {
        $this->artisan('make:command', ['name' => $given])
            ->expectsOutputToContain('The name "'.$given.'" is reserved by PHP.')
            ->assertExitCode(0);
    }

    public function testItGeneratesFilesInCustomPath()
    {
        GeneratorCommand::enforceFileMap([
            'command.stub' => fn ($name) => app_path("Custom/{$name}.php")
        ]);
        
        $this->artisan('make:command', ['name' => 'Foo'])
            ->assertExitCode(0);

        $this->assertFilenameExists('app/Custom/Foo.php');
    }
    
    public function testItGeneratesFilesInCustomPathWithRespectedNamespaces()
    {
        GeneratorCommand::enforceFileMap([
            'model.stub' => fn($name) => [
                'path' => base_path("my/custom/path/{$name}.php"),
                'namespace' => 'Custom\\Namespace'
            ]
        ]);
    
        $this->artisan('make:model', ['name' => 'Foo'])
            ->assertExitCode(0);
    
        $this->assertFileExists(base_path('my/custom/path/Foo.php'));
    }
    
    public static function reservedNamesDataProvider()
    {
        yield ['__halt_compiler'];
        yield ['__HALT_COMPILER'];
        yield ['array'];
        yield ['ARRAY'];
        yield ['__class__'];
        yield ['__CLASS__'];
    }
}
