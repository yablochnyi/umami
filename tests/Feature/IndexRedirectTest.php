<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_index_redirects_permanently_for_get_and_head(): void
    {
        $this->get('/index.php')->assertStatus(301)->assertHeader('Location', '/');
        $this->head('/index.php')->assertStatus(301)->assertHeader('Location', '/');
    }

    public function test_query_parameters_are_preserved_without_normalization(): void
    {
        $query = 'utm_source=google&utm_campaign=sushi%20Torun&tag=a&tag=b';

        $this->get('/index.php?'.$query)->assertStatus(301)->assertHeader('Location', '/?'.$query);
    }

    public function test_front_controller_normalization_does_not_hide_the_original_url(): void
    {
        $this->withServerVariables([
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
        ]);

        $this->get('/index.php')->assertStatus(301)->assertHeader('Location', '/');
        $this->get('http://localhost/')->assertOk()->assertHeaderMissing('Location');
        $this->get('http://localhost/en')->assertOk()->assertHeaderMissing('Location');
    }

    public function test_other_paths_and_post_requests_are_not_redirected(): void
    {
        $this->get('/')->assertOk()->assertHeaderMissing('Location');
        $this->get('/index.html')->assertNotFound()->assertHeaderMissing('Location');
        $this->post('/index.php')->assertStatus(405)->assertHeaderMissing('Location');
    }
}
