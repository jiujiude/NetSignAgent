<?php
/**
 * Created by PhpStorm.
 * User: hgq <393210556@qq.com>
 * Date: 2021/09/29
 * Time: 10:40
 */

include_once './php/NetSignAgent.php';

//验签
$signedData = $_POST['signedData'];
$obj = new NetSignAgent($signedData);
$data = $obj->attachedVerify();

//返回数据
$result = [];
$result['msg'] = $data ? '验签成功' : $obj->getMsg();
$result['code'] = $data ? 1 : 0;
$result['data'] = $data;
exit(json_encode($result));