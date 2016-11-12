<?php namespace muvo\sms\providers;

use muvo\sms\SMS;
use linslin\yii2\curl\Curl;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class GoldenTelecom extends Component implements SMS
{
    public $user;
    public $pass;
    public $from;
    private $curl;

    public function init(){
        $this->curl = new Curl();
        $this->curl->setOptions([
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
    }

    private function call($params=array()){
        $postFields = [
            'user' => $this->user,
            'pass' => $this->pass,
            'gzip' => 'none',
        ];

        $postFields = http_build_query(array_merge($postFields,$params));
        $this->curl->setOption(CURLOPT_POSTFIELDS,$postFields);
        \Yii::info(VarDumper::dumpAsString($postFields),__METHOD__);

        if(!$response=$this->curl->post('http://web.smsgold.ru/http'))
            throw new InvalidValueException('Пустой ответ от шлюза');

        try{
            \Yii::trace($response,__METHOD__);
            $result = Json::decode($response,false);

            \Yii::info(VarDumper::dumpAsString($result),__METHOD__);
            return $result;
        }catch (InvalidParamException $e){
            \Yii::error($e->getMessage(),__METHOD__);
            throw new InvalidValueException($response);
        }
    }

    public function send($num,$text,$param=array()){
        $request = [
            'action' => 'post_sms',
            'target' => $num,
            'message' => $text,
        ];

        $request['sender'] = isset($param['from'])
            ? $param['from']
            : $this->from;

        return $this->call($request);
    }
}
