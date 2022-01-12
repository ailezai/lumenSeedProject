<?php
namespace App\Repositories\Ali;


use App\Models\Ali\AliFormId;
use Illuminate\Support\Facades\DB;

class AliFormIdRepository
{
    protected $size = 20;
    /**
    * @var AliFormId $aliFormId
    */
    protected $aliFormId;

    /**
    * AliFormId constructor.
    * @param AliFormId $aliFormId
    */
    public function __construct(AliFormId $aliFormId)
    {
        $this->aliFormId = $aliFormId;
    }

    public function countActiveFormId()
    {
        $seven_time =  date("Y-m-d H:i:s",strtotime("-6 day")) ;

        $objQuery = AliFormId::select("open_id")
            ->where("USED_NUM",'<',3)
            ->where("CREATE_TIME",'>=',$seven_time)
            ->groupBy('OPEN_ID');

        return  \DB::table(\DB::raw("({$objQuery->toSql()}) as questionstock"))
            ->mergeBindings($objQuery->getQuery())        //注意这里需要合并绑定参数
            ->count();

    }
}

?>