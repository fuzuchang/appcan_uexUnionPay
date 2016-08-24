<?php

 
// ######(以下配置为PM环境：入网测试环境用，生产环境配置见文档说明)#######
// 商户号
define("UNION_PAY_MERID",'777290058135601');
// 签名证书路径
define("SDK_SIGN_CERT_PATH",DISCUZ_ROOT.'source/plugin/tom_love/unionpay/certs/700000000000001_acp.pfx');
// 签名证书密码
define("SDK_SIGN_CERT_PWD",'000000');
// 密码加密证书（这条一般用不到的请随便配）
define("SDK_ENCRYPT_CERT_PATH",DISCUZ_ROOT.'source/plugin/tom_love/unionpay/certs/verify_sign_acp.cer');
// 验签证书路径（请配到文件夹，不要配到具体文件）
define("SDK_VERIFY_CERT_DIR",DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/certs/');
//App交易地址
define("SDK_App_Request_Url",'https://101.231.204.80:5000/gateway/api/appTransReq.do');
// 前台通知地址 (商户自行配置通知地址)
define("SDK_FRONT_NOTIFY_URL",'http://aiyue.jxwlkssb.com/plugin.php?id=tom_love&mod=my');
// 后台通知地址 (商户自行配置通知地址，需配置外网能访问的地址)
define("SDK_BACK_NOTIFY_URL",'http://aiyue.jxwlkssb.com/source/plugin/tom_love/unionpay/unionpay_notify.php');
// ######(以上配置APP支付必要配置)#######


// ######(以下配置可以忽略,APP支付可以不用)#######
// 前台请求地址
define("SDK_FRONT_TRANS_URL",'https://101.231.204.80:5000/gateway/api/frontTransReq.do');
// 后台请求地址
define("SDK_BACK_TRANS_URL",'https://101.231.204.80:5000/gateway/api/backTransReq.do');
// 批量交易
define("SDK_BATCH_TRANS_URL",'https://101.231.204.80:5000/gateway/api/batchTrans.do');
//单笔查询请求地址
define("SDK_SINGLE_QUERY_URL",'https://101.231.204.80:5000/gateway/api/queryTrans.do');
//文件传输请求地址
define("SDK_FILE_QUERY_URL",'https://101.231.204.80:9080/');
//有卡交易地址
define("SDK_Card_Request_Url",'https://101.231.204.80:5000/gateway/api/cardTransReq.do');
//文件下载目录 
define("SDK_FILE_DOWN_PATH",DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/file/');
//日志 目录 
define("SDK_LOG_FILE_PATH",DISCUZ_ROOT.'/source/plugin/tom_love/unionpay/logs/');
//日志级别，关掉的话改PhpLog::OFF
define("SDK_LOG_LEVEL",PhpLog::DEBUG);
/** 以下缴费产品使用，其余产品用不到，无视即可 */
// 前台请求地址
define("JF_SDK_FRONT_TRANS_URL",'https://101.231.204.80:5000/jiaofei/api/frontTransReq.do');
// 后台请求地址
define("JF_SDK_BACK_TRANS_URL",'https://101.231.204.80:5000/jiaofei/api/backTransReq.do');
// 单笔查询请求地址
define("JF_SDK_SINGLE_QUERY_URL",'https://101.231.204.80:5000/jiaofei/api/queryTrans.do');
// 有卡交易地址
define("JF_SDK_CARD_TRANS_URL",'https://101.231.204.80:5000/jiaofei/api/cardTransReq.do');
// App交易地址
define("JF_SDK_APP_TRANS_URL",'https://101.231.204.80:5000/jiaofei/api/appTransReq.do');

