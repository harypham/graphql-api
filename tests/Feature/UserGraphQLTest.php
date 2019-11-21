<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;

class UserGraphQLTest extends TestCase
{
    use WithFaker;

//    public function testQueriesUser()
//    {
//        $user = factory(User::class)->create();
//        Passport::actingAs($user);
//        $response = $this->postGraphQL([
//            'query' => '
//                query user($id: ID!) {
//                    user(id: $id) {
//                        id name email
//                    }
//                }
//        ',
//            'variables' => [
//                'id' => $user->id
//            ],
//        ]);
//
//        $response->assertJson([
//            "data" => [
//                "user" => [
//                    "id" => $user->id,
//                    "name" => $user->name,
//                    "email" => $user->email
//                ]
//            ]
//        ]);
//    }
//
//    public function testCreateUser()
//    {
//        $user = User::findOrFail(1);
//        Passport::actingAs($user);
//        $response = $this->postGraphQL([
//            'query' => '
//                mutation createUser($name: String!, $email: String!, $password: String!) {
//                    createUser(name: $name, email: $email, password: $password) {
//                        id name email
//                    }
//                }
//        ',
//            'variables' => [
//                'name' => 'test-create-user',
//                'email' => $this->faker->email,
//                'password' => 'secret'
//            ],
//        ]);
//        $names = $response->json("data.*.name");
//        $this->assertSame(["test-create-user"], $names);
//    }
//
//    public function testDeleteUser()
//    {
//        $user = factory(User::class)->create();
//        Passport::actingAs($user);
//        $response = $this->postGraphQL([
//            'query' => '
//                mutation deleteUser($id: ID!) {
//                    deleteUser(id: $id) {
//                        id name email deleted_at
//                    }
//                }
//        ',
//            'variables' => [
//                'id' => $user->id
//            ],
//        ]);
//
//        $deleted_at = $response->json("data.*.deleted_at");
//        $this->assertNotNull($deleted_at);
//    }
}
