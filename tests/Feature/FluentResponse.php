<?php

namespace Tests\Feature;

use CodeIgniter\Test\TestResponse;

/**
 * Pembungkus TestResponse agar assertion bisa dirangkai:
 * $this->get('/')->assertOK()->assertSee('Halo');
 *
 * @mixin TestResponse
 */
final class FluentResponse
{
    public function __construct(private TestResponse $response)
    {
    }

    public function __call(string $method, array $args): mixed
    {
        $result = $this->response->{$method}(...$args);

        return str_starts_with($method, 'assert') ? $this : $result;
    }
}
