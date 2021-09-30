<?php
/**
 * Created by PhpStorm.
 * User: Administrator hgq <393210556@qq.com>
 * Date: 2021/09/29
 * Time: 9:14
 */

include_once(dirname(__DIR__) . "/javaBridge/Java.inc");

class NetSignAgent
{
    private $java_obj;
    private $msg;
    private $signedText;

    public function __construct($signedText)
    {
        $this->java_obj = java("cn.com.infosec.netsign.agent.NetSignAgent");
        $this->java_obj->initialize(dirname(__DIR__) . '/javaBridge/netsignagent.properties');
        $this->signedText = $signedText;
    }

    /**
     * attached 验签 返回 签名证书主题 信息
     * @param $signedText
     * @return array|bool
     * @author hgq <393210556@qq.com>.
     * @date: 2021/09/29 9:22
     */
    public function attachedVerify()
    {
        if (empty($this->signedText)) {
            $this->msg = '签名不能为空';
            return false;
        }
        try {
            $tsaText = null; //时间戳，如果该参数的值是null，表示不用验证时间戳
            $needCert = false; //标明是否返回用于验证签名的公钥证书
            //验签
            $result = $this->java_obj->attachedVerify($this->signedText, $tsaText, $needCert);
            //验签结果获取
            $result_str = $result->getStringResult($result->SIGN_SUBJECT);
            $data = [];
            if (!empty($result_str)) {
                $tmp = explode(',', $result_str);
                foreach ($tmp as $index => $item) {
                    $item_tmp = explode('=', $item);
                    if (count($item_tmp) == 2) {
                        $data[trim($item_tmp[0])] = trim($item_tmp[1]);
                    }
                }
            }
            return $data;
        } catch (\JavaException $ex) {
            $ex_text = $ex->getCause();
            $text1 = $this->getBetween($ex_text, ':"', '" at:') . ' msg_end';
            $text2 = $this->getBetween($text1, ':', 'msg_end');
            $this->msg = $text2 ?: $text1;
            return false;
        }

    }

    /**
     * 字符串截取
     * @param $input
     * @param $start
     * @param $end
     * @return false|string
     * @author hgq <393210556@qq.com>.
     * @date: 2021/09/29 9:23
     */
    public function getBetween($input, $start, $end)
    {
        return substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
    }

    /**
     * 获取错误信息
     * @return mixed
     * @author hgq <393210556@qq.com>.
     * @date: 2021/09/29 9:23
     */
    public function getMsg()
    {
        return $this->msg;
    }
}