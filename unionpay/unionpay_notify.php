<?php


//支付宝支付回调接口文件
error_reporting(E_ALL);
define('APPTYPEID', 110001);
define('CURSCRIPT', 'plugin');
define('DISABLEXSSCHECK', true); 

$_GET['id'] = 'tom_love';

//加载系统核心类库
require substr(dirname(__FILE__), 0, -31).'/source/class/class_core.php';
//加载银联支付类库
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/log.class.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/SDKConfig.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/common.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/acp_service.php';
require DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/sdk/cert_util.php';

$discuz = C::app();
$cachelist = array('plugin', 'diytemplatename');

$discuz->cachelist = $cachelist;
$discuz->init();
define('CURMODULE', 'tom_love');

function dump_log($data){
	$logDir = DISCUZ_ROOT."./source/plugin/tom_love/logs/";
		if(!is_dir($logDir)){
			mkdir($logDir, 0777,true);
		}else{
			chmod($logDir, 0777); 
		}
    $file = DISCUZ_ROOT."./source/plugin/tom_love/logs/".date("Y-m-d").".unionpay.log";
    file_put_contents($file,date("Y-m-d H:i:s").":【".json_encode($data)."】\n",FILE_APPEND);
}

$_G['siteurl'] = substr($_G['siteurl'], 0, -23);
$_G['siteroot'] = substr( $_G['siteroot'], 0, - 23);

$jyConfig = $_G['cache']['plugin']['tom_love'];
//保存回调参数日志
dump_log($_REQUEST);

//回调参数列表
$cert_id 				= $_POST ['certId']; //签名
$signature 				= $_POST ['signature']; //签名
$order_id 				= $_POST ['orderId']; //其他字段也可用类似方式获取
$response_code 			= $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
//验证签名：
 if($signature){
     $signature_result = AcpService::validate ( $_POST ) ? true : false;
 }

if ( ($response_code == '00' || $response_code == 'A6') &&  $signature_result){
	$orderInfo = C::t('#tom_love#tom_love_order')->fetch_by_order_no($out_trade_no);
	if($orderInfo && $orderInfo['order_status'] == 1){
		$updateData = array();
		$updateData['order_status'] = 2;
		$updateData['pay_time'] = TIMESTAMP;
		C::t('#tom_love#tom_love_order')->update($orderInfo['id'],$updateData);
		if($orderInfo['order_type'] == 1){
			$userinfo = C::t('#tom_love#tom_love')->fetch_by_id($orderInfo['user_id']);
			$updateData = array();
			$updateData['score'] = $userinfo['score'] + $orderInfo['score_value'];
			C::t('#tom_love#tom_love')->update($userinfo['id'],$updateData);
			
			$insertData = array();
			$insertData['user_id'] = $userinfo['id'];
			$insertData['score_value'] = $orderInfo['score_value'];
			$insertData['log_type'] = 16;
			$insertData['log_time'] = TIMESTAMP;
			C::t('#tom_love#tom_love_scorelog')->insert($insertData);
			
		//VIP
		}else if($orderInfo['order_type'] == 2){
			$userinfo = C::t('#tom_love#tom_love')->fetch_by_id($orderInfo['user_id']);
			
			$vip_time = TIMESTAMP;
			if($userinfo['vip_time'] > TIMESTAMP){
				$vip_time = $userinfo['vip_time'] + $orderInfo['time_value']*30*86400;
			}else{
				$vip_time = TIMESTAMP + $orderInfo['time_value']*30*86400;
			}
			$updateData = array();
			$updateData['vip_id'] = 1;
			$updateData['vip_time'] = $vip_time;
			C::t('#tom_love#tom_love')->update($userinfo['id'],$updateData);
		}
		
		dump_log($orderInfo);
	}
	echo "success";die();
}
