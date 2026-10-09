<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_include_the_async_widget_once_inside_the_body(): void
    {
        $this->seed();
        $script = '<script async src="https://widget.wenetasystent.ai/?code=rBAad0rd"></script>';

        foreach (['', '/uk', '/en'] as $prefix) {
            foreach (['/', '/menu/rameny', '/menu/rameny/chashu-ramen', '/koszyk'] as $path) {
                $url = $path === '/' ? ($prefix ?: '/') : $prefix.$path;
                $html = $this->get($url)->assertOk()->assertSee($script, false)->getContent();

                $this->assertSame(1, substr_count($html, $script), $url);
                $this->assertGreaterThan(strpos($html, '<body'), strpos($html, $script), $url);
                $this->assertLessThan(strpos($html, '</body>'), strpos($html, $script), $url);
            }
        }
    }

    public function test_widget_is_not_loaded_on_admin_login(): void
    {
        $this->get('/admin/login')->assertOk()->assertDontSee('widget.wenetasystent.ai');
    }
}
