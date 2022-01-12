<?php

/**
 * Created by CodeGenerate
 * Version: v1.0
 * User: CodeGenerate made by FRM
 * TempleTime: 2018-01-24 20:34:41
 * CreateTime: 2018-02-11 23:14:25
 */

namespace App\Services\Stats;

use AiLeZai\Common\Lib\RPC\HttpServiceClient;
use AiLeZai\Common\Lib\Log\LOG;

class StatsEggsService
{
    public function __construct(){}


    /**查询数据集市任务结果集
     * @param $businessType 所创建的服务类型
     * @param $keyName 服务key
     * @param bool $pageNum 页数	第一页从1开始,默认从第一页开始
     * @param bool $pageSize 分页size	默认20条
     * @param array $sqlParams  查询参数	为结果集列的查询筛选条件，传入格式为a>1,a!=1,a=1
     * @param bool $all 是否查询所有	若传ture则会查询出所有结果慎用，默认为false
     * @param String $directSqlContent 直接查询的sql语句	传入sql模板为select * from ${tableName},${tableName}为占位符会替换为任务模板生成的结果集表名
     * @param String $sqlSelectResult sq自定义查询列	默认是select * from 表名，若传入该内容则变为select sqlSelectResult的值 from 表名
     * @param String $allResp  false 返回整个结构体
     * @param json $sortIndexs 排序参数 acs desc
     * @return array|ZipAreaModel
     */
    public function eggsStatsResult()
    {
        $req = array(
            'businessType'      => "redpack_stats",
            'keyName'           => "eggs_stats",
            'sortIndexs'        =>\GuzzleHttp\json_encode(array("d desc")),
            'all'               =>true,
            'pageNum'           =>null,
            'pageSize'          =>null,
            'sqlParams'         =>array(),
            'directSqlContent'  =>'',
            'sqlSelectResult'   =>'',
            'allResp'           =>false
        );

        try{
            //$result = HttpServiceClient::callDataMarket('/dataMarketTask/findTaskResult', $req,"POST","form_params");
            //LOG::info('/dataMarketTask/findTaskResult success');

            return null;//$result['paginationResultInfo']['result'];
        }catch (Exception $e){
            LOG::error(sprintf('/dataMarketTask/findTaskResult error-message[ %s]', $e->getMessage()));
        }
        return array();
    }
}
