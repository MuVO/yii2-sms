<?php namespace muvo\sms;

interface SMS
{
    public function send($toNumber,$text,$options=array());
}
