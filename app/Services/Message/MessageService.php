<?php

/**
 * Created by CodeGenerate
 * Version: v1.0
 * User: CodeGenerate made by FRM
 * TempleTime: 2018-01-24 20:34:41
 * CreateTime: 2018-02-11 23:14:25
 */

namespace App\Services\Message;

use App\Repositories\Ali\AliFormIdRepository;
use App\Repositories\Message\MessageRepository;
use App\Supports\LifestyleApi\MessageApi;
use AiLeZai\Common\Lib\RPC\HttpServiceClient;
use AiLeZai\Common\Lib\Log\LOG;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    /**
     * @var MessageRepository $messageRepository
     */
    private $messageRepository;

    public function __construct(MessageRepository $messageRepository,AliFormIdRepository $aliFormIdRepository)
    {
        $this->messageRepository = $messageRepository;
        $this->aliFormIdRepository = $aliFormIdRepository;
    }

    public function getList()
    {
        $data = $this->messageRepository->getList(NULL,NULL,'MINIAPP');

        /**
         * @var Collection $collection
         */
        $collection = $data->getCollection();
        $data->setCollection($collection->map(function ($item){
            //获取描述

            $item->desc_content =
                "业务类型:".$item->business_type."<br/>".
                "业务场景:".$item->scence."<br/>".
                "keyword1:".$item->keyword1."<br/>".
                ($item->keyword2?"keyword2:".$item->keyword2."<br/>":"").
                ($item->keyword3?"keyword3:".$item->keyword3."<br/>":"").
                ($item->keyword4?"keyword4:".$item->keyword4."<br/>":"").
                ($item->url?"链接:".$item->url:"");

            return $item;
        }));
        return $data;
    }

    public function addData($params)
    {
        $params['status']='APPLY';
        $params['send_type']='MINIAPP';
        $params['app_id']=env("ALI_LIFESTYLE_APP_ID") ;
        $this->messageRepository->create($params);

    }
    public function delete(array $ids)
    {
        $data = ['status' => 'CLOSE'];
        $this->messageRepository->update($ids,$data);
    }
    public function sendMsg($id)
    {
        $sendData = [];
        $data = $this->messageRepository->getList(NULL,$id,'MINIAPP');
        foreach ($data as $key => $value ){
            $sendData[$key]['appId']=$value->app_id;
            $sendData[$key]['businessType']=$value->business_type;
            $sendData[$key]['scence']=$value->scence;
            $sendData[$key]['microAppPage']=$value->url;
            $sendData[$key]['keyword1']=$value->keyword1;
            $sendData[$key]['keyword2']=$value->keyword2;
            $sendData[$key]['keyword3']=$value->keyword3;
            $sendData[$key]['keyword4']=$value->keyword4;
        }

        $number = $this->aliFormIdRepository->countActiveFormId();
        foreach ($sendData as $val){
            MessageApi::sendMiniappMsg($val);
        }

        //更新数据

        $seven_time =  date("Y-m-d H:i:s",strtotime("-6 day")) ;
        $curr_time = date("Y-m-d H:i:m");
        $data = [
            'status' => 'SUCCESS',
            'send_number' =>$number,
            'send_time' =>$seven_time."~".$curr_time
        ];
        $this->messageRepository->update([$id],$data);
    }
}