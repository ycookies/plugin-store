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
        $this->saveLoginInfo($res);
        /*$res_header = $res['header'];
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
        }*/
        return $this->response()->success('登陆成功')->refresh();
    }

    public function saveLoginInfo($res){
        $res_header = $res['header'];
        $res_body = json_decode($res['body'],true);
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
        return true;
    }

    public function form()
    {
        $modalid = !empty($this->payload['modalid']) ? $this->payload['modalid']:'';
        $res = CurlUtil::request('https://jikeadmin.saishiyun.net/api/member/getWxGzhLoginQrcode',[],['method'=>'post','returnHeader'=>true]);
        $res_body = json_decode($res['body'],true);
        if(isset($res_body['code']) && $res_body['code'] != 0){
            $this->html($res_body['msg']);
        }else{
            $this->html($res_body['data']['qrcode_html']);
            $formdata1  = json_encode(['wechat_flag'=> $res_body['data']['wechat_flag']]);
            // 执行轮询

            Admin::script(<<<JS
                // 检查 timer 是否已经存在
                
                var timer = null;
                if (timer == null) {
                    timer = setInterval(() => {
                        // 请求参数是二维码中的场景值 url('/admin/auth/qrcode-login-check')
                        $.ajax('/admin/plugin-store/checkLogin', {
                            method: 'POST',
                            data: {$formdata1}
                        }).then(response => {
                            let result = response.data;
                            console.log(response);
                            console.log(response.code);
                            if (response.code === 0) {
                                clearInterval(timer);
                                timer = null; // 清空 timer 变量
                                $('#{$modalid}').modal('hide');
                                window.location.reload();
                            }
                        });
                    }, 2000);
                }
                $('#{$modalid}').on('hide.bs.modal', function (event) {
                    //if(timer != null){
                        clearInterval(timer);
                    //}
});
$('#{$modalid}').on('hidden.bs.modal', function (event) {
                    //if(timer != null){
                        clearInterval(timer);
                    //}
                    
});
JS
            );

            /*$lunxun_res = CurlUtil::request('https://jikeadmin.saishiyun.net/api/member/checkWxGzhLoginQrcode',['wechat_flag'=> $res_body['wechat_flag']],['method'=>'post','returnHeader'=>true]);
            if(isset($lunxun_res['code']) && $lunxun_res['code'] == 0){
                $this->saveLoginInfo($lunxun_res);

            }*/
        }
        $this->disableSubmitButton();
        $this->disableResetButton();
    }

    public function form_old()
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
