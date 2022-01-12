<?php
namespace App\Repositories\Message;


use App\Models\Message\AliMessageSendConfig;

class MessageRepository
{
    protected $size = 20;
    /**
     * @var AliMessageSendConfig $aliMessageSendConfig
     */
    protected $aliMessageSendConfig;

    public function __construct(AliMessageSendConfig $aliMessageSendConfig)
    {
        $this->aliMessageSendConfig = $aliMessageSendConfig;
    }

    public function getList($title= '',$id = '',$sendType='')
    {

        $query =$this->aliMessageSendConfig->selectFullFields();

        if ($title) {
            $query->where('title',$title);
        }
        if ($id) {
            $query->where('id',$id);
        }
        if ($sendType) {
            $query->where('send_type',$sendType);
        }

        $query->whereIn('status',['SUCCESS','APPLY','FAIL'])->orderBy("id","desc");

        return $query->paginate($this->size);
    }
    /**
     * 创建数据
     *
     * @param array $data 新增字段
     *
     * @return AliMessageSendConfig
     */
    public function create(array $data)
    {
        return $this->aliMessageSendConfig->create($data);
    }
    public function update(array $ids,$data)
    {
        return  $this->aliMessageSendConfig->whereIn('id', $ids)->update($data);
    }
}

?>