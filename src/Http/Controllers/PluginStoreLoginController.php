<?php

namespace Dcat\Admin\PluginStore\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dcat\Admin\Core\Util\CurlUtil;
use Illuminate\Http\Client\Response;


class PluginStoreLoginController extends Controller {


    // 验证登陆
    public function checkLogin(Request $request) {
        $weChatFlag = $request->get('wechat_flag');
        if(empty($weChatFlag)){
            return Response()->json(['code' => -1,'msg'=> '登陆标识符 不能为空']);
        }
        $res = CurlUtil::request('https://jikeadmin.saishiyun.net/api/member/checkWxGzhLoginQrcode',['wechat_flag'=> $weChatFlag],['method'=>'post','returnHeader'=>true]);
        $res_body = json_decode($res['body'],true);

        if(isset($res_body['code']) && $res_body['code'] != 0){
            return Response()->json(['code' => -1,'msg'=> $res_body['msg']]);
        }
        $this->saveLoginInfo($res);

        return Response()->json(['code'=> 0,'data'=>$res_body['data'],'msg'=> 'Login Success' ]);
    }

    public function saveLoginInfo($res){
        $user = Admin::user();
        $res_body = json_decode($res['body'],true);
        $res_header = $res['header'];
        $api_token = '';
        foreach ($res_header as $head) {
            if(isset($head['api-token'])){
                $api_token = $head['api-token'];
            }
        }
        if(!empty($api_token)){
            cache()->put('StoreUser_token_'.$user->id,$api_token,12000000);
        }
        $StoreUser_info = [];
        if(!empty($res_body['data']['userinfo'])){
            $StoreUser_info = $res_body['data']['userinfo'];
            cache()->put('StoreUser_info_'.$user->id,$StoreUser_info,12000000);
            cache()->put('StoreUser_memberUserId_'.$user->id,$StoreUser_info['id'],12000000);//->get('memberUserId');
        }
        return true;
    }



}
