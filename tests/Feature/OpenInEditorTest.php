<?php

namespace Tests\Feature;

use App\Actions\OpenInEditor;
use App\LamboException;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

class OpenInEditorTest extends TestCase
{
    /** @test */
    function it_opens_the_project_folder_in_the_specified_editor()
    {
        config(['lambo.store.editor' => 'my-editor']);
        config(['lambo.store.open_editor' => true]);

        $this->shell->shouldReceive('withTTY')
            ->once()
            ->andReturnSelf();

        $this->shell->shouldReceive('execInProject')
            ->with('my-editor .')
            ->once()
            ->andReturn(FakeProcess::success());

        app(OpenInEditor::class)();
    }

    /** @test */
    function it_skips_opening_the_project_in_an_editor()
    {
        config(['lambo.store.editor' => 'my-editor']);
        config(['lambo.store.open_editor' => false]);

        app(OpenInEditor::class)();
    }

    /** @test */
    function it_throws_an_exception_if_it_fails_to_open_the_editor()
    {
        config(['lambo.store.editor' => 'my-editor']);
        config(['lambo.store.open_editor' => true]);


        $this->shell->shouldReceive('withTTY')
            ->once()
            ->andReturnSelf();

        $this->shell->shouldReceive('execInProject')
            ->with('my-editor .')
            ->once()
            ->andReturn(FakeProcess::fail('my-editor .'));

        $this->expectException(LamboException::class);

        app(OpenInEditor::class)();
    }
}
