/* window.uexOnload = function()
{
	//支付回调
	uexUnionPay.cbStartPay = function(data){
        //alert(data);
    }
}
 */
/*
* 银联支付
*/
function unionpay()
{
	var order_desc 		= document.getElementById("subject").value;
	var fee 			= document.getElementById("fee").value;
	var month_id 			= document.getElementById("month_id").value;
	var out_trade_no 	= document.getElementById("out_trade_no").value;
	var paydata = {};
		paydata.out_trade_no	= out_trade_no;
		paydata.user_id 		= document.getElementById("user_id").value;
		paydata.pay_price 		= fee;
		paydata.order_desc 		= order_desc;
		paydata.month_id 		= month_id;
	$.get('plugin.php?id=tom_love:unionpay',paydata,function(json){
		if(json.status == 200){
			var params = {
				orderInfo:json.result_param.tn,//获取到的交易流水号
				mode:"01"//测试环境，该参数传01
			};
			var data = JSON.stringify(params);
			uexUnionPay.startPay(data);
		}else{
			uexWindow.toast("1", "5", "创建订单失败,"+json.status, "5000");
		}
	},'json');
	

}


