<?php

if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/zh_cn/payment/paysapi.php';

if (file_exists($payment_lang)) {
    global $_LANG;

    include_once($payment_lang);
}

if (isset($set_modules) && $set_modules == TRUE) {
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code'] = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc'] = 'paysapi_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod'] = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online'] = '1';

    /* 作者 */
    $modules[$i]['author'] = 'paysapi';

    /* 网址 */
    $modules[$i]['website'] = 'https://www.paysapi.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config'] = array(
        array('name' => 'paysapi_appkey', 'type' => 'text', 'value' => ''),
        array('name' => 'paysapi_appsecret', 'type' => 'text', 'value' => ''),
        array('name' => 'paysapi_pay_type', 'type' => 'select', 'value' => '1'),
        array('name' => 'paysapi_pay_url', 'type' => 'text', 'value' => 'https://pay.bearsoftware.net.cn'),
        array('name' => 'paysapi_return', 'type' => 'text', 'value' => $GLOBALS['ecs']->url() . 'respond.php'),

    );
    return;
}

class paysapi
{
    function __construct()
    {
        $this->paysapi();
    }

    function paysapi()
    {

    }

    function get_code($order, $payment)
    {        
        if (!defined('EC_CHARSET')) {
            $charset = 'utf-8';
        }

        $notify_url = return_url(basename(__FILE__, '.php'));
        $return_url = $notify_url;
        $order_id = $order['log_id'];
        $istype = $payment['paysapi_pay_type'];
        $price = (int)($order['order_amount'] * 100);
        $uid= $payment['paysapi_appkey'];
        $token = $payment['paysapi_appsecret'];
        $goodsname = "";
        $orderuid = "";
        $key = md5($goodsname . $istype . $notify_url . $order_id . $orderuid . $price . $return_url . $token . $uid);

        $html = '';
        $html=$html.'<form method="post" action="'. $payment['paysapi_pay_url'] .'">';
        $html=$html.'    <input type="hidden" name="key" value="'.$key.'"/> ';
        $html=$html.'    <input type="hidden" name="notify_url" value="'. $notify_url .'"/> ';
        $html=$html.'    <input type="hidden" name="orderid" value="'. $order_id .'"/> ';
        $html=$html.'    <input type="hidden" name="orderuid" value="'. $orderuid .'"/> ';
        $html=$html.'    <input type="hidden" name="return_url" value="'. $return_url .'"/> ';
        $html=$html.'    <input type="hidden" name="goodsname" value="'. $goodsname .'"/> ';
        $html=$html.'    <input type="hidden" name="istype" value="'. $istype .'"/> ';
        $html=$html.'    <input type="hidden" name="uid" value="'. $uid .'"/> ';
        $html=$html.'    <input type="hidden" name="price" value="'. $price .'"/> ';
        $html=$html.'    <input type="submit" value="' . $GLOBALS['_LANG']['pay_button'] . '"/>';
        $html=$html.'</form>';

        return '<div style="text-align:center">'. $html .'</div>';
    }

    function respond()
    {

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $token = get_payment('paysapi')['paysapi_appsecret'];
            $paysapi_id = $_POST['paysapi_id'];
            $orderid = $_POST['orderid'];
            $price = $_POST['price'];
            $realprice = $_POST['realprice'];
            $orderuid = $_POST['orderuid'];
            $key = $_POST['key'];

            $temps = md5($orderid . $orderuid . $paysapi_id . $price . $realprice . $token);
            // echo $token."|";
            // echo $temps."|";
            // echo $key."|";

            //检查签名
            if ($temps != $key) {
                header('HTTP/1.1 500 Not Found');
                echo "校验失败";
                exit();
            }

            order_paid($orderid);
            echo "支付成功";
            exit();
        }else{
            $orderid = $_GET['orderid'];
            order_paid($orderid);
            return true;
        }
        
    }
}

?>