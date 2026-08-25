<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedSessionCookieTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(string $slug = 'test-restaurant'): Restaurant
    {
        return Restaurant::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    private function sessionCookie($response)
    {
        $name = config('session.cookie');

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }

    public function test_embed_request_gets_a_partitioned_cross_site_session_cookie(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1");

        $cookie = $this->sessionCookie($response);
        $this->assertNotNull($cookie);
        $this->assertSame('none', $cookie->getSameSite());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isPartitioned());
    }

    public function test_non_embed_request_keeps_the_default_lax_session_cookie(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book");

        $cookie = $this->sessionCookie($response);
        $this->assertNotNull($cookie);
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertFalse($cookie->isPartitioned());
    }

    public function test_same_origin_embed_request_keeps_the_default_lax_session_cookie(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->withHeader('Sec-Fetch-Site', 'same-origin')
            ->get("/r/{$restaurant->slug}/book?embed=1");

        $cookie = $this->sessionCookie($response);
        $this->assertNotNull($cookie);
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertFalse($cookie->isSecure());
        $this->assertFalse($cookie->isPartitioned());
    }
}
