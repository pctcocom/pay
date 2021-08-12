<?php
namespace Pctco\Pay\Alipay;
class Notify{
   public function __construct($config){
      $this->config = array_merge([
         'charset'   =>   'utf8',
         // 支付宝公钥，账户中心->密钥管理->开放平台密钥->查看支付宝公钥
         'alipayPublicKey'=>   str_replace([" ","　","\t","\n","\r"], '', $config['Alipay']['alipayPublicKey']),
      ],$config);

      $this->tools = new \Pctco\Pay\Alipay\Tools($this->config);
   }

   /**
   * 验证订单是否支付成功
   * @return array
   */
   public function pay(){
      $data = $this->config['PostData'];
      //验证签名
      $result = $this->tools->rsaCheck($data,$data['sign_type']);

      if($result === true && $data['trade_status'] == 'TRADE_SUCCESS'){
         /**
            * 处理你的逻辑，例如获取订单号$_POST['out_trade_no']，订单金额$_POST['total_amount']等
            * 程序执行完后必须打印输出“success”（不包含引号）。如果商户反馈给支付宝的字符不是success这7个字符，支付宝服务器会不断重发通知，直到超过24小时22分钟。一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）；
         **/
         return true;
      }
      return false;
   }
}
