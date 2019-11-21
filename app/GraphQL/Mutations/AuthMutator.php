<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use App\Models\User;
use Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Cookie;

class AuthMutator extends BaseAuthResolver
{
    use SendsPasswordResetEmails;

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return mixed
     * @throws \Nuwave\Lighthouse\Exceptions\AuthenticationException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // TODO implement the resolver
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return mixed
     * @throws \Nuwave\Lighthouse\Exceptions\AuthenticationException
     */
    public function login($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $credentials = $this->buildCredentials($args);
        $response = $this->makeRequest($credentials);
        $user = User::where(config('lighthouse-graphql-passport.username'), $args['username'])->firstOrFail();
        $response['user'] = $user;
        return $response;
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return mixed
     * @throws \Nuwave\Lighthouse\Exceptions\AuthenticationException
     */
    public function refreshToken($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $credentials = $this->buildCredentials($args, 'refresh_token');
        return $this->makeRequest($credentials);
    }

    /**
     *
     */
    public function logout()
    {
        if (!Auth::guard('api')->check()) {
            throw new AuthenticationException("Not Authenticated");
        }
        // revoke user's token
        Auth::guard('api')->user()->token()->revoke();
        Cookie::forget('api_token');

        return [
            'status' => 'TOKEN_REVOKED',
            'message' => 'Your session has been terminated'
        ];
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return array
     */
    public function forgotPassword($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $response = $this->broker()->sendResetLink(['email' => $args['email']]);
        if ($response == Password::RESET_LINK_SENT) {
            return [
                'status' => 'EMAIL_SENT',
                'message' => trans($response)
            ];
        }
        return [
            'status' => 'EMAIL_NOT_SENT',
            'message' => trans($response)
        ];
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return mixed
     * @throws \Nuwave\Lighthouse\Exceptions\AuthenticationException
     */
    public function register($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $model = app(config('auth.providers.users.model'));
        $input = collect($args)->except('password_confirmation')->toArray();
        $input['password'] = bcrypt($input['password']);
        $model->fill($input);
        $model->save();
        $credentials = $this->buildCredentials([
            'username' => $args['email'],
            'password' => $args['password'],
        ]);
        $user = $model->where('email', $args['email'])->first();
        $response = $this->makeRequest($credentials);
        $response['user'] = $user;
        return $response;
    }
}
