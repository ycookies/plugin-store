<?php

namespace Dcat\Admin\PluginStore\Renderable;

use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Exception\RuntimeException;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Core\Util\CurlUtil;
use Illuminate\Support\Facades\Validator;
use Dcat\Admin\Core\Util\ArrayUtil;
use Illuminate\Support\Arr;

class PluginStoreUserLogin extends Form implements LazyRenderable
{
    use LazyWidget;

    // 处理请求
    public function handle(array $input)
    {
        $validator = Validator::make($input, [
            'username'  => 'required',
            'password'=> 'required',
            'xieyi' => 'required',
        ], [
            'username.required'  => '用户名 不能为空',
            'password.required'  => '登陆密码 不能为空',
            'xieyi' => '请阅读协议并同意',
        ]);
        if ($validator->fails()) {
            return $this->response()->error($validator->errors()->first());
        }
        $data = [
            'username'=> $input['username'],
            'password' => $input['password'],
        ];
        $res = CurlUtil::request('https://jikeadmin.saishiyun.net/api/member/login',$data,['method'=>'post','returnHeader'=>true]);

        $res_body = json_decode($res['body'],true);
        if(isset($res_body['code']) && $res_body['code'] != 0){
            return $this->response()->error($res_body['msg']);
        }
        $res_header = $res['header'];
        $api_token = '';
        foreach ($res_header as $head) {
            if(isset($head['api-token'])){
                $api_token = $head['api-token'];
            }
        }
        if(!empty($api_token)){
            cache()->put('StoreUser_token',$api_token,12000000);
        }
        $StoreUser_info = [];
        if(!empty($res_body['data']['userinfo'])){
            $StoreUser_info = $res_body['data']['userinfo'];
            cache()->put('StoreUser_info',$StoreUser_info,12000000);
            cache()->put('memberUserId',$StoreUser_info['id'],12000000);//->get('memberUserId');
        }
        return $this->response()->success('登陆成功')->refresh();
    }

    public function form()
    {
        $this->html('<div style="font-size: 16px;text-align: center">请登录账号</div>');
        $this->width(8,3);
        $this->text('username','用户名')->prepend('')->required();
        $this->password('password','登陆密码')->prepend('')->required();
        //$this->captcha('phone_codes','验证码')->placeholder('图形验证码');
        $this->checkbox('xieyi','')->options(['1'=> '同意']);//->append('<a target="_blank" href="#">《使用协议》</a> <a target="_blank" href="#">《免责声明》</a>');
        $this->html('还没有账号? <a href="#" target="_blank">立即注册</a>');
        $this->disableResetButton();
        $this->submitButtonCenter();
        $this->setSubmitButtonSize('btn-lg btn-block');
        $this->setSubmitButtonLabel('立即登陆')->setSubmitButtonIcon('<i class="feather icon-globe"></i>');
    }
}
