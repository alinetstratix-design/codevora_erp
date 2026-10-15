<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_sees_the_landing_page_at_root()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Codevora AI')
            ->assertSee('Get started', false);
    }
}
