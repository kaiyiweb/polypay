<?php
declare(strict_types=1);

namespace polypay\ecc\random;
// +----------------------------------------------------------------------
// | Title: 
// +----------------------------------------------------------------------
// | Author: 劳谦君子 <laoqianjunzi@qq.com>
// +----------------------------------------------------------------------
// | Date: 2021年09月22日
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------

interface RandomNumberGeneratorInterface
{
    public function generate(\GMP $max): \GMP;
}
