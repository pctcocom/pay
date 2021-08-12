<?php
namespace Pctco\Pay\Alipay;
class Tools{
   function __construct($config){
      $this->config = $config;
   }
   /**
   * 建立请求，以表单HTML形式构造（默认）
   * @param $para_temp 请求参数数组
   * @return 提交表单HTML文本
   */
   public function form($para_temp) {
      $sHtml = "正在跳转至支付页面...<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=".$this->config['charset']."' method='POST'>";
      foreach($para_temp as $key=>$val){
         if (false === $this->checkEmpty($val)) {
            $val = str_replace("'","&apos;",$val);
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
         }
      }
      //submit按钮控件请不要含有name属性
      $sHtml = $sHtml."<input type='submit' value='ok' style='display:none;''></form>";
      $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
      return $sHtml;
   }

   public function generateSign($params, $signType = "RSA") {
      return $this->sign($this->getSignContent($params), $signType);
   }

   public function sign($data, $signType = "RSA") {
      $priKey = $this->config['rsaPrivateKey'];
      $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
      wordwrap($priKey, 64, "\n", true) .
      "\n-----END RSA PRIVATE KEY-----";
      ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
      if ("RSA2" == $signType) {
         openssl_sign($data, $sign, $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
      } else {
         openssl_sign($data, $sign, $res);
      }
      $sign = base64_encode($sign);
      return $sign;
   }

   public function verify($data, $sign, $signType = 'RSA') {
      $pubKey= $this->config['alipayPublicKey'];
      $res = "-----BEGIN PUBLIC KEY-----\n" .
      wordwrap($pubKey, 64, "\n", true) .
      "\n-----END PUBLIC KEY-----";
      ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

      //调用openssl内置方法验签，返回bool值
      if ("RSA2" == $signType) {
         $result = (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
      } else {
         $result = (bool)openssl_verify($data, base64_decode($sign), $res);
      }
      // if(!$this->checkEmpty($this->config['alipayPublicKey'])) {
      //    //释放资源
      //    openssl_free_key($res);
      // }
      return $result;
   }

   /**
   * 校验$value是否非空
   *  if not set ,return true;
   *    if is null , return true;
   **/
   public function checkEmpty($value) {
      if (!isset($value))return true;
      if ($value === null)return true;
      if (trim($value) === "")return true;
      return false;
   }

   /**
   *  验证签名
   **/
   public function rsaCheck($params) {
      $sign = $params['sign'];
      $signType = $params['sign_type'];
      unset($params['sign_type']);
      unset($params['sign']);
      return $this->verify($this->getSignContent($params), $sign, $signType);
   }

   public function getSignContent($params) {
      ksort($params);
      $stringToBeSigned = "";
      $i = 0;
      foreach ($params as $k => $v) {
         if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
         // 转换成目标字符集
         $v = $this->characet($v, $this->config['charset']);
         if ($i == 0) {
            $stringToBeSigned .= "$k" . "=" . "$v";
         } else {
            $stringToBeSigned .= "&" . "$k" . "=" . "$v";
         }
            $i++;
         }
      }

      unset ($k, $v);
      return $stringToBeSigned;
   }

   /**
   * 转换字符集编码
   * @param $data
   * @param $targetCharset
   * @return string
   */
   function characet($data, $targetCharset) {
      if (!empty($data)) {
         $fileType = $this->config['charset'];
         if (strcasecmp($fileType, $targetCharset) != 0) {
            $data = mb_convert_encoding($data, $targetCharset, $fileType);
            //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
         }
      }
      return $data;
   }
}
