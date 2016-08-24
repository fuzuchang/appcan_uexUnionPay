<?php

//error_reporting(E_ALL);
session_start();
/**
 *  1 待支付 2 已支付
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//加载银联支付类库
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/log.class.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/SDKConfig.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/common.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/acp_service.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/cert_util.php';


//var_dump(SDK_SIGN_CERT_PATH,file_exists(SDK_ENCRYPT_CERT_PATH));die;

$jyConfig = $_G['cache']['plugin']['tom_love'];

$payment_mode = '银联APP支付';

$act = isset($_GET['act'])? addslashes($_GET['act']):"score";
$out_trade_no = isset($_GET['out_trade_no'])? addslashes($_GET['out_trade_no']):"";
$order_desc = isset($_GET['order_desc'])? addslashes($_GET['order_desc']):"";
$outArr = array(
	'status'=> 1,
);
$result_param = array();

//查询订单
if($act == 'query_payment_order'){
	$payment_order = C::t('#tom_love#tom_love_order')->fetch_by_order_no($out_trade_no);
	if($payment_order['order_status'] == '2'){
		$outArr = array(
			'status'=> 200
		);
	}
	echo json_encode($outArr); exit;
}else{
	//创建订单

	if($_SESSION['status'] == "score"){
		$outArr = array(
			'status'=> 1,
		);
		$user_id    = isset($_GET['user_id'])? intval($_GET['user_id']):0;
		$openid    = isset($_GET['openid'])? daddslashes($_GET['openid']):"";
		$pay_price  = intval($_GET['pay_price'])>0? intval($_GET['pay_price']):$_SESSION['pay_price'];
		$userinfo = C::t('#tom_love#tom_love')->fetch_by_id($user_id);
		if(!$userinfo && !$_GET['pay_price']){
			$outArr = array(
				'status'=> $pay_price,
			);
			echo json_encode($outArr); exit;
		}
		
		$yuan_score_listStr = str_replace("\r\n","{n}",$jyConfig['yuan_score_list']); 
		$yuan_score_listStr = str_replace("\n","{n}",$yuan_score_listStr);
		$yuan_score_listTmpArr = explode("{n}", $yuan_score_listStr);

		$yuan_scoreArr = array();
		if(is_array($yuan_score_listTmpArr) && !empty($yuan_score_listTmpArr)){
			foreach ($yuan_score_listTmpArr as $key => $value){
				if(!empty($value)){
					list($yuan, $score) = explode("|", $value);
					$yuan = intval($yuan);
					$score = intval($score);
					if(!empty($yuan) && !empty($score)){
						$yuan_scoreArr[$yuan] = $score;
					}
				}
			}
		}
		$pay_price = $_SESSION['pay_price'];
		if(!isset($yuan_scoreArr[$pay_price])){
			$outArr = array(
				'status'=> 302,
			);
			echo json_encode($outArr); exit;
		}
            //生成商户订单号：

            $params = array(

                //以下信息非特殊情况不需要改动
                'version' => '5.0.0',                 //版本号
                'encoding' => 'utf-8',				  //编码方式
                'txnType' => '01',				      //交易类型
                'txnSubType' => '01',				  //交易子类
                'bizType' => '000201',				  //业务类型
                'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
                'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
                'signMethod' => '01',	              //签名方法
                'channelType' => '08',	              //渠道类型，07-PC，08-手机
                'accessType' => '0',		          //接入类型
                'currencyCode' => '156',	          //交易币种，境内商户固定156

                //TODO 以下信息需要填写
                'merId'     => UNION_PAY_MERID,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
                'orderId'   => $out_trade_no,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
                'txnTime'   => date('YmdHis',time()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
                'txnAmt'    => $pay_price * 100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
// 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
				
				//'orderDesc'=>$order_desc,
            );

            AcpService::sign ( $params ); // 签名
            $url = SDK_App_Request_Url;

            $result_arr = AcpService::post ($params,$url);
        if ($result_arr["respCode"] == "00"){
            //返回参数
            $result_param['tn'] = $result_arr["tn"];
			C::t('#tom_love#tom_love_order')->delete_by_order_no($out_trade_no);
            //写入订单记录
            $insertData = array();
            $insertData['order_no']         = $out_trade_no;
            $insertData['openid']           = $openid;
            $insertData['user_id']          = $user_id;
            $insertData['score_value']      = $yuan_scoreArr[$pay_price];
            $insertData['pay_price']        = $pay_price;
            $insertData['order_status']     = 1;
            $insertData['order_time']       = TIMESTAMP;
            $insertData['payment_mode']       = $payment_mode;
            $insertData['unionpay_tn']       = $result_arr["tn"];
            C::t('#tom_love#tom_love_order')->insert($insertData);

			$outArr = array(
				'status'=> 200,
                'result_param' => $result_param,
			);
			echo json_encode($outArr); exit;
		}else{
			$outArr = array(
				'status'=> 304,
			);
			echo json_encode($outArr); exit;
		}

		
		
	}else if($_SESSION['status'] == "vip"){
	   
		$outArr = array(
			'status'=> 1,
		);

		$user_id    = isset($_GET['user_id'])? intval($_GET['user_id']):0;
		$openid     = isset($_GET['openid'])? daddslashes($_GET['openid']):"";
		$month_id   = intval($_GET['month_id'])>0? intval($_GET['month_id']):$_SESSION['months'];
		$vip_id     = intval($_GET['vip_id'])>0? intval($_GET['vip_id']):1;
		
		$userinfo = C::t('#tom_love#tom_love')->fetch_by_id($user_id);
		if(!$userinfo){
			$outArr = array(
				'status'=> 301,
			);
			echo json_encode($outArr); exit;
		}
		
		$yuan_vip1_listStr = str_replace("\r\n","{n}",$jyConfig['yuan_vip1_list']); 
		$yuan_vip1_listStr = str_replace("\n","{n}",$yuan_vip1_listStr);
		$yuan_vip1_listTmpArr = explode("{n}", $yuan_vip1_listStr);
		
		$yuan_vip1Arr = array();
		if(is_array($yuan_vip1_listTmpArr) && !empty($yuan_vip1_listTmpArr)){
			foreach ($yuan_vip1_listTmpArr as $key => $value){
				if(!empty($value)){
					list($month, $price) = explode("|", $value);
					$month = intval($month);
					$price = intval($price);
					if(!empty($month) && !empty($price)){
						$yuan_vip1Arr[$month] = $price;
					}
				}
			}
		}

		if(!isset($yuan_vip1Arr[$month_id])){
			$outArr = array(
				'status'=> 302,
			);
			echo json_encode($outArr); exit;
		} 
		
		if($vip_id == 1){
			$order_type = 2;
		}else if($vip_id == 2){
			$order_type = 3;
		}

        //生成商户订单号：

        $params = array(

            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
            'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
            'signMethod' => '01',	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId'     => UNION_PAY_MERID,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId'   => $out_trade_no,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime'   => date('YmdHis',time()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt'    => $yuan_vip1Arr[$month_id] * 100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
// 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

            //'orderDesc'=>$order_desc,
        );

        AcpService::sign ( $params ); // 签名
        $url = SDK_App_Request_Url;

        $result_arr = AcpService::post ($params,$url);

        if ($result_arr["respCode"] == "00"){
            //返回参数
            $result_param['tn'] = $result_arr["tn"];
		
			C::t('#tom_love#tom_love_order')->delete_by_order_no($out_trade_no);
			$insertData = array();
			$insertData['order_no']         = $out_trade_no;
			$insertData['order_type']       = $order_type;
			$insertData['openid']           = $openid;
			$insertData['user_id']          = $user_id;
			$insertData['score_value']      = 0;
			$insertData['time_value']       = $month_id;
			$insertData['pay_price']        = $yuan_vip1Arr[$month_id];
			$insertData['order_status']     = 1;
			$insertData['order_time']       = TIMESTAMP;
			$insertData['payment_mode']       = $payment_mode;
			$insertData['unionpay_tn']       = $result_arr["tn"];
            C::t('#tom_love#tom_love_order')->insert($insertData);

			$outArr = array(
				'status'=> 200,
				 'result_param' => $result_param,
			);
			echo json_encode($outArr); exit;
		}else{
			$outArr = array(
				'status'=> 304,//304
			);
			echo json_encode($outArr); exit;
		}
	}else{
		$outArr = array(
			'status'=> 111111,
		);
		echo json_encode($outArr); exit;
	}


}

?>
