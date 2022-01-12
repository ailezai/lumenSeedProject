<?php
/**
 * Created by PhpStorm.
 *
 * @author: Steven (冯瑞铭)
 * @date: 2018/1/7
 */

namespace App\Http\Controllers;

use Illuminate\View\View;
use AiLeZai\Lumen\Framework\Http\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * 后台首页
     *
     * @return View
     */
    public function index()
    {
        return view('index');
    }
}