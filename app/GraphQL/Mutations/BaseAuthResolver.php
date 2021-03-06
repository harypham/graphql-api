<?php

namespace App\GraphQL\Mutations;

use Illuminate\Http\Request;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

class BaseAuthResolver
{
    /**
     * @param array $args
     * @param string $grantType
     * @return mixed
     */
    public function buildCredentials(array $args = [], $grantType = "password")
    {
        $args = collect($args);
        $credentials = $args->except('directive')->toArray();
        $credentials['client_id'] = config('lighthouse-graphql-passport.client_id');
        $credentials['client_secret'] = config('lighthouse-graphql-passport.client_secret');
        $credentials['grant_type'] = $grantType;
        $credentials['scope'] = '*';
        return $credentials;
    }

    public function makeRequest(array $credentials)
    {
        $request = Request::create(
            'oauth/token',
            'POST',
            $credentials,
            [], //cookies
            [], //files
            ['HTTP_Accept' => 'application/json']
        );
        $response = app()->handle($request);
        $decodedResponse = json_decode($response->getContent(), true);

        if ($response->getStatusCode() != 200) {
            throw new AuthenticationException($decodedResponse['message']);
        }

        if (!$request->hasCookie('api_token')) {
            $cookie_name = "api_token";
            $cookie_value = $decodedResponse['access_token'];
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day -> 30 days
        }

        return $decodedResponse;
    }
}