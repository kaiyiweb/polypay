<?php
namespace polypay\ecc\sm34\libs;
// +----------------------------------------------------------------------
// | Title: 字的位运算类
// +----------------------------------------------------------------------
// | Author: 劳谦君子 <laoqianjunzi@qq.com>
// +----------------------------------------------------------------------
// | Date: 2021年09月22日
// +----------------------------------------------------------------------
// | Description：
// +----------------------------------------------------------------------

use  polypay\ecc\sm34\types\Word;

class WordConversion
{
    public static function xorConversion($params)
    {
        return self::conversion($params, 3);
    }

    public static function  conversion_func($prevent, $current, $type)
    {
        if (is_null($prevent)) {
            return $current;
        }

        $prevent = strval($prevent);
        $current = strval($current);

        if (strlen($current) >= strlen($prevent)) {
            $longest = strlen($current);
            $longest_value = $current;
            $shortest = strlen($prevent);
        } else {
            $longest = strlen($prevent);
            $longest_value = $prevent;
            $shortest = strlen($current);
        }

        if ($prevent === '0' || $current === '0') {
            switch ($type) {
                // and
                case 1:
                    return 0;
                // or
                case 2:
                    // xor
                case 3:
                    // add
                case 5:
                    return $prevent == '0' ? $current : $prevent;
                default:
                    break;
            }
        }

        $value = array();
        /**
         * 加运算时需要，用来储存需要进几
         *
         * @var  int 向前一位进的数字
         */
        $carry = 0;
        /**
         * 大端
         *
         * 这里从大端跑完之后，结果数组的序号是从大到小排列的
         * 还需要根据键名排序一次
         *
         * 个人感觉区分不区分大端并没有什么意义
         * 如果换成字符串拼接的话更好用
         * 但是方便你理解，还是按照大端+数组的方式进行的排列
         */
        for ($i = $longest - 1; $i >= 0; $i--) {
            $prevent_number = $prevent[$i];
            switch ($type) {
                // 与
                case 1:
                    $value[$i] = ($i >= $shortest)
                        ? $longest_value[$i]
                        : ($prevent_number & $current[$i]);
                    break;
                // 或
                case 2:
                    $value[$i] = ($i >= $shortest)
                        ? ~$longest_value[$i]
                        : ($prevent_number | $current[$i]);
                    break;
                // 异或
                case 3:
                    $value[$i] = $i > $shortest
                        ? 1
                        : (intval($prevent_number) ^ intval($current[$i]));
                    break;
                // 非（按位取反）
                case 4:
                    $value[$i] = $prevent_number === '1'
                        ? '0'
                        : '1';
                    break;
                // 加
                case 5:
                    $add = $prevent_number + $current[$i] + $carry;
                    $value[$i] = $add % 2;
                    $carry = ($add - $value[$i]) / 2;
                    break;
                // 特殊情况
                default:
                    break;
            }
        }

        ksort($value);
        return new Word(join('', $value));
    }
    
    private static function conversion($params, $type)
    {
        $prevent = null;
        foreach ($params as $param){
            $prevent = self::conversion_func($prevent,$param,$type);
        }
        return $prevent;
    }
    
   
    public static function andConversion($params)
    {
        return self::conversion($params, 1);
    }
    
    public static function orConversion($params)
    {
        return self::conversion($params, 2);
    }
    
   
    public static function notConversion($word)
    {
        return self::conversion(array($word, null), 4);
    }
    
    public static function shiftLeftConversion($word, $times)
    {
        return new Word(
            substr(
                $word,
                ($times % strlen($word))
            )
            . substr(
                $word,
                0,
                ($times % strlen($word))
            )
        );
    }
    
    public static function hex2bin($hex)
    {
        // 格式化为字符串
        $hex = strval($hex);
        
        /** 十六进制转二进制，每1位一组 */
        defined('HEX_TO_BIN_NUM') || define('HEX_TO_BIN_NUM', 1);
        /** @var array $hex_array 把指定的十六进制数按位切片为数组 */
        $hex_array = str_split($hex, HEX_TO_BIN_NUM);
        // 最终的二进制数字（为确保长度不丢失，使用字符串类型）
        $binary = '';
        
        foreach ($hex_array as $number) {
            $bin_number = strval(base_convert($number, 16, 2));
            if (strlen($bin_number) < 4) {
                $bin_number = str_pad($bin_number, 4, '0', STR_PAD_LEFT);
            }
            $binary .= $bin_number;
        }
        
        return $binary;
    }
    
    
    public static function bin2hex($bin)
    {
        // 格式化为字符串
        $bin = strval($bin);
        
        /** 二进制转十六进制，每4位一组 */
        defined('BIN_TO_HEX_NUM') || define('BIN_TO_HEX_NUM', 4);
        /** @var array $bin_array 把指定的二进制数按位切片为数组 */
        $bin_array = str_split($bin, BIN_TO_HEX_NUM);
        // 最终的二进制数字（为确保长度不丢失，使用字符串类型）
        $hex = '';
        
        foreach ($bin_array as $number) {
            $hex .= strval(base_convert($number, 2, 16));
        }

        return $hex;
    }
    
    public static function addConversion($params)
    {
        return self::conversion($params, 5);
    }
}