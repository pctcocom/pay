<?php
namespace Pctco\Pay\Alipay;
use think\facade\Config;
/**
* @name 电脑端
**/
class Pc{
   public function __construct($config){
      $this->config = array_merge([
         /**
         * @name 公共请求参数
         **/
         // 支付宝分配给开发者的应用ID
         'app_id'   =>   $config['Alipay']['app_id'],
         // 请求使用的编码格式，如utf-8,gbk,gb2312等
         'charset'   =>   'utf8',

         // [同步回调通知] HTTP/HTTPS开头字符串
         'return_url'   =>   '//my.'.Config::get('initialize.client.domain.top').'/'.Config::get('authority.user.username'),
         // [异步回调通知]支付宝服务器主动通知商户服务器里指定的页面http/https路径。
         'notify_url'   =>   Config::get('initialize.client.domain.scheme').'://www.'.Config::get('initialize.client.domain.top').'/notify',

         /**
         * @name 请求参数
         **/

         // 商户订单号。64 个字符以内的大小，可包含字母、数字、下划线。需保证该参数在商户端不重复
         'out_trade_no'   =>   time(),
         // 订单总金额，单位为元，精确到小数点后两位，取值范围为 [0.01,100000000]。金额不能为0。
         'total_amount'   =>   '',
         // 订单标题
         'subject'   =>   '',

         /**
         * @name 其他
         **/
         'rsaPrivateKey'=>   str_replace([" ","　","\t","\n","\r"], '', $config['Alipay']['rsaPrivateKey']),
      ],$config);

      $this->tools = new \Pctco\Pay\Alipay\Tools($this->config);
   }

   /**
   * 发起订单
   * @return array
   */
   public function pay(){
      //请求参数
      $request = [
         'out_trade_no'=>$this->config['out_trade_no'],
         'product_code'=>'FAST_INSTANT_TRADE_PAY',
         'total_amount'=>$this->config['total_amount'], //单位 元
         'subject'=>$this->config['subject'],  //订单标题
      ];
      $configs = array(
         //公共参数
         'app_id' => $this->config['app_id'],
         'method' => 'alipay.trade.page.pay',             //接口名称
         'format' => 'JSON',
         'return_url' => $this->config['return_url'],
         'charset'=>$this->config['charset'],
         'sign_type'=>'RSA2',
         'timestamp'=>date('Y-m-d H:i:s'),
         'version'=>'1.0',
         'notify_url' => $this->config['notify_url'],
         'biz_content'=>json_encode($request)
      );
      $configs["sign"] = $this->tools->generateSign($configs, $configs['sign_type']);
      return $this->tools->form($configs);
   }
}
