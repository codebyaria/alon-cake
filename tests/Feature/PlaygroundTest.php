<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlaygroundTest extends TestCase
{
    public function test_playground_renders_for_guests()
    {
        $response = $this->get(route('playground'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('playground'));
    }
}
