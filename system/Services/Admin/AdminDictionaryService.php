<?php
namespace System\Services\Admin;

use Exception;
use AiLeZai\Lumen\Framework\Exceptions\DataNotFoundException;
use System\Models\Admin\AdminDictionary;
use System\Repositories\Admin\AdminDictionaryRepository;

class AdminDictionaryService
{
    /**
     * @var AdminDictionaryRepository
     */
    protected $adminDictionaryRepository;

    /**
     * AdminDictionaryService constructor.
     *
     * @param AdminDictionaryRepository $adminDictionaryRepository
     */
    public function __construct(AdminDictionaryRepository $adminDictionaryRepository)
    {
        $this->adminDictionaryRepository = $adminDictionaryRepository;
    }

    /**
     * 分页展示字典
     *
     * @param array $condition
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listPaginateByCondition(array $condition = [])
    {
        $filter = $this->setFilter($condition);
        $size = config('webConfig.paginate.large');
        return $this->adminDictionaryRepository->listPaginateByFilter($filter, $size);
    }

    /**
     * 设置过滤条件
     *
     * @param array $condition  筛选条件
     * @param array $filter     过滤数组
     *
     * @return array
     */
    private function setFilter(array $condition, array $filter = [])
    {
        // trace_id 筛选
        if (!empty($condition['name'])) {
            $filter = sql_where()->field($filter, 'name', $condition['name']);
        }

        return $filter;
    }

    /**
     * 获取字典详情
     *
     * @param $id
     *
     * @return null|AdminDictionary
     */
    public function getById($id)
    {
        return $this->adminDictionaryRepository->getById($id);
    }

    /**
     * 新建字典
     *
     * @param string $name
     * @param string $desc
     * @param array $key
     * @param array $value
     *
     * @throws DataNotFoundException
     */
    public function addSubmit(string $name, string $desc, array $key, array $value)
    {
        if (count($key) != count(array_unique($key))) {
            throw new DataNotFoundException("存在重复的键");
        }
        if (count($key) != count($value)) {
            throw new DataNotFoundException("键和值的数量不符");
        }

        $dictionary = [];
        for ($i = 0; $i < count($key); $i++) {
            if ($key[$i] === '' || $value[$i] === '') {
                throw new DataNotFoundException('键和值不能为空');
            }
            $dictionary[$key[$i]] = $value[$i];
        }

        $data = [
            'name' => $name,
            'desc' => $desc,
            'dictionary' => json_encode($dictionary, JSON_UNESCAPED_UNICODE)
        ];
        $this->adminDictionaryRepository->create($data);
    }

    /**
     * 修改字典
     *
     * @param int|null $id
     * @param string $desc
     * @param array $key
     * @param array $value
     *
     * @throws DataNotFoundException
     */
    public function editSubmit($id, string $desc, array $key, array $value)
    {
        if (count($key) != count(array_unique($key))) {
            throw new DataNotFoundException("存在重复的键");
        }
        if (count($key) != count($value)) {
            throw new DataNotFoundException("键和值的数量不符");
        }

        $dictionary = [];
        for ($i = 0; $i < count($key); $i++) {
            if ($key[$i] === '' || $value[$i] === '') {
                throw new DataNotFoundException('键和值不能为空');
            }
            $dictionary[$key[$i]] = $value[$i];
        }

        $data = [
            'desc' => $desc,
            'dictionary' => json_encode($dictionary, JSON_UNESCAPED_UNICODE)
        ];
        $this->adminDictionaryRepository->updateById($id, $data);
    }

    /**
     * 返回字典内容
     *
     * @param string $name
     * @param int $time
     *
     * @return array|mixed|null|AdminDictionary
     *
     * @throws Exception
     */
    public function getDictionaryByName($name, $time = 300)
    {
        $key = "admin_dictionary_{$name}";
        if (redis()->exists($key)) {
            $dictionary = json_decode(redis()->get($key), true);
        } else {
            $dictionary = $this->adminDictionaryRepository->getByName($name);
            if (empty($dictionary)) {
                $dictionary = [];
            } else {
                $dictionary = json_decode($dictionary->dictionary, true);
            }
            redis()->setex($key, $time, json_encode($dictionary, JSON_UNESCAPED_UNICODE));
        }
        return $dictionary;
    }
}