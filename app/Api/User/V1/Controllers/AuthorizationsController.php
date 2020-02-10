<?php

namespace Api\User\V1\Controllers;

use Illuminate\Support\Facades\Auth;
use Api\User\V1\Requests\AuthorizationRequest;
use Api\User\V1\Transformers\UserTransformer;
use App\Controller as AppController;
use League\Fractal\Serializer\DataArraySerializer;
use User\Entities\User;

/**
 * 用户认证
 *
 * @author bryan <voidpointers@hotmail.com>
 * @Resource("账户认证", uri="/users")
 */
class AuthorizationsController extends AppController
{
    /**
     * 登录
     *
     * @Post("/login")
     * @Parameters({
     *   @Parameter("username", description="用户名", required=true),
     *   @Parameter("verification_code", description="验证码", required=false),
     * })
     * @Response(200, body={"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9", "token_type": "Bearer", "expires_in": 60})
     * @param AuthorizationRequest $request
     * @return mixed
     */
    public function login(AuthorizationRequest $request)
    {
        // 校验验证码
        // check_verification_code($request->only(['username', 'verification_code']), 'login');

        $credentials = $request->only(['username', 'password']);
        if (!Auth::guard('api')->validate($credentials)) {
            return $this->response->errorunauthorized(trans('auth.failed'));
        }

        $user = User::where($request->input('username'))->first();
        if (!$user) {
            return $this->response->errorunauthorized(trans('auth.failed'));
        }

        return $this->respondWithUser($user);
    }

    /**
     * 刷新token
     *
     * @Get("/refresh")
     * @Request(headers={"Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"})
     * @Response(200)
     * @return mixed
     */
    public function refresh()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    /**
     * 退出登录
     *
     * @Get("/logout")
     * @Request(headers={"Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"})
     * @Response(200)
     * @return \Dingo\Api\Http\Response
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return $this->response->noContent();
    }

    /**
     * 通过用户获取token
     * 
     * @return mixed
     */
    protected function respondWithUser($user)
    {
        return $this->response->item(
            $user,
            new UserTransformer(),
            function ($resource, $fractal) {
                return $fractal->setSerializer(new DataArraySerializer);
            }
        )
        ->setMeta([
            'access_token' => Auth::guard('api')->fromUser($user),
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ])
        ->setStatusCode(201);
    }

    /**
     * 返回统一格式
     *
     * @param $token
     * @return mixed
     */
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
