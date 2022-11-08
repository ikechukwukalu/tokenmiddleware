<?php

namespace Ikechukwukalu\Tokenmiddleware\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use Ikechukwukalu\Tokenmiddleware\Models\Novel;

class TokenTest extends TestCase
{
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testErrorValidationForChangeToken()
    {
        $user =  User::find(1);

        if (!isset($user->id)) {
            $user = User::factory()->create(); // Would still have the default token
        }

        $this->actingAs($user);

        $postData = [
            'current_token' => '9090', //Wrong current token
            'token' => '1uu4', //Wrong token format
            'token_confirmation' => '1234' //None matching tokens
        ];

        $response = $this->post('/api/change/token', $postData);
        $responseArray = json_decode($response->getContent(), true);

        $this->assertEquals(500, $responseArray['status_code']);
        $this->assertEquals('fail', $responseArray['status']);
    }

    public function testChangeToken()
    {
        $userData = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make("{_'hhtl[N#%H3BXe")
        ];

        $user = User::factory()->create([
            'name' => $this->faker->name(),
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'token' => Hash::make(config('tokenmiddleware.token.default', '0000')),
        ]);

        $this->actingAs($user);

        $postData = [
            'current_token' => config('tokenmiddleware.token.default', '0000'),
            'token' => '1234',
            'token_confirmation' => '1234'
        ];

        $this->assertTrue(Hash::check($postData['current_token'], $user->token));

        $response = $this->post('/api/change/token', $postData);
        $responseArray = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseArray['status_code']);
        $this->assertEquals( 'success', $responseArray['status']);
    }

    public function testRequireTokenMiddleWareForCreateNovel()
    {
        $token = 1234;

        $user = User::factory()->create([
            'token' => Hash::make($token),
            'default_token' => 0
        ]);

        $this->assertTrue(Hash::check($token, $user->token));

        $this->actingAs($user);

        if (Route::has('createNovelTest')) {
            $postData = [
                'name' => $this->faker->sentence(rand(1,5)),
                'isbn' => $this->faker->unique()->isbn13(),
                'authors' => implode(",", [$this->faker->name(), $this->faker->name()]),
                'publisher' => $this->faker->name(),
                'number_of_pages' => rand(45,1500),
                'country' => $this->faker->countryISOAlpha3(),
                'release_date' => date('Y-m-d')
            ];

            $response = $this->json('POST', route('createNovelTest'), $postData);
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);
            $this->assertTrue(isset($responseArray['data']['url']));

            $postData = [
                config('tokenmiddleware.token.input', '_token') => (string) $token
            ];
            $url = $responseArray['data']['url'];

            $response = $this->post($url, $postData);
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);

        } else {
            $this->assertTrue(true);
        }

    }

    public function testRequireTokenMiddleWareForUpdateNovel()
    {
        $token = 1234;

        $user = User::factory()->create([
            'token' => Hash::make($token),
            'default_token' => 0
        ]);

        $this->assertTrue(Hash::check($token, $user->token));

        $this->actingAs($user);

        if (Route::has('updateNovelTest')) {
            $novel = Novel::find(1);

            if (!isset($novel->id)) {
                $novel = Novel::create([
                        'name' => $this->faker->sentence(rand(1,5)),
                        'isbn' => $this->faker->unique()->isbn13(),
                        'authors' => implode(",", [$this->faker->name(), $this->faker->name()]),
                        'publisher' => $this->faker->name(),
                        'number_of_pages' => rand(45,1500),
                        'country' => $this->faker->countryISOAlpha3(),
                        'release_date' => date('Y-m-d')
                ]);
            }

            $id = $novel->id;

            $postData = [
                'name' => $this->faker->sentence(rand(1,5)),
                'isbn' => $this->faker->unique()->isbn13(),
                'authors' => implode(",", [$this->faker->name(), $this->faker->name()]),
                'publisher' => $this->faker->name(),
                'number_of_pages' => rand(45,1500),
                'country' => $this->faker->countryISOAlpha3(),
                'release_date' => date('Y-m-d')
            ];

            $response = $this->json('PATCH', route('updateNovelTest', ['id' => $id]), $postData);
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);
            $this->assertTrue(isset($responseArray['data']['url']));

            $postData = [
                config('tokenmiddleware.token.input', '_token') => (string) $token
            ];
            $url = $responseArray['data']['url'];

            $response = $this->post($url, $postData);
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);

        } else {
            $this->assertTrue(true);
        }

    }

    public function testRequireTokenMiddleWareForDeleteNovel()
    {
        $token = 1234;

        $user = User::factory()->create([
            'token' => Hash::make($token),
            'default_token' => 0
        ]);

        $this->assertTrue(Hash::check($token, $user->token));

        $this->actingAs($user);

        if (Route::has('deleteNovelTest')) {
            $novel = Novel::find(1);

            if (!isset($novel->id)) {
                $novel = Novel::create([
                    'name' => $this->faker->sentence(rand(1,5)),
                    'isbn' => $this->faker->unique()->isbn13(),
                    'authors' => implode(",", [$this->faker->name(), $this->faker->name()]),
                    'publisher' => $this->faker->name(),
                    'number_of_pages' => rand(45,1500),
                    'country' => $this->faker->countryISOAlpha3(),
                    'release_date' => date('Y-m-d')
                ]);
            }

            $id = $novel->id;

            $response = $this->json('DELETE', route('deleteNovelTest', ['id' => $id]));
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);
            $this->assertTrue(isset($responseArray['data']['url']));

            $postData = [
                config('tokenmiddleware.token.input', '_token') => (string) $token
            ];
            $url = $responseArray['data']['url'];

            $response = $this->post($url, $postData);
            $responseArray = json_decode($response->getContent(), true);

            $this->assertEquals(200, $responseArray['status_code']);
            $this->assertEquals('success', $responseArray['status']);

        } else {
            $this->assertTrue(true);
        }

    }
}
