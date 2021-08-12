<?php
namespace Pctco\Pay;
use think\facade\Cache;
class Processor{
   function __construct($config = []){
      $this->config = array_merge($config,Cache::store('config')->get(md5('app\admin\controller\Config\pay')));

      switch ($this->config['client']) {
         case 'AlipayPc':
            $this->client = new \Pctco\Pay\Alipay\Pc($this->config);
            break;
         case 'AlipayWap':
            $this->client = new \Pctco\Pay\Alipay\Wap($this->config);
            break;
         case 'AlipayReturn':
            $this->client = new \Pctco\Pay\Alipay\Returns($this->config);
            break;
         case 'AlipayReturn':
            $this->client = new \Pctco\Pay\Alipay\Notify($this->config);
            break;
         default:
            $this->client = false;
            break;
      }
   }
   public function send(){
      return $this->client->pay();
   }
}
