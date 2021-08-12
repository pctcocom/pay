<?php
namespace Pctco\Pay\Alipay;
class Returns{
   public function __construct($config){
      $this->config = array_merge([
         'charset'   =>   'utf8',
         // 支付宝公钥，账户中心->密钥管理->开放平台密钥->查看支付宝公钥
         'alipayPublicKey'=>   str_replace([" ","　","\t","\n","\r"], '', $config['Alipay']['alipayPublicKey']),
      ],$config);

      $this->tools = new \Pctco\Pay\Alipay\Tools($this->config);
   }
   /**
   * 返回订单
   * @return array
   */
   public function pay(){
      $data = $this->config['GetData'];
      //验证签名
      $result = $this->tools->rsaCheck($data,$data['sign_type']);

      if($result === true){
         //同步回调一般不处理业务逻辑，显示一个付款成功的页面，或者跳转到用户的财务记录页面即可。
         return true;
      }
      return false;
   }
}
