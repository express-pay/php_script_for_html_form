<?php
$settings = array(
    'serviceId'     => '5',
    'token'         => 'a75b74cbcfe446509e8ee874f421bd67',
    'useSignature'  => false,
    'secretWord'    => '',
    'currency'      => '933',
    'returnType'    => 'redirect',
    'info'          => 'Оплата на сайте',
    'site'          => 'http://wordpress:8080'
);

$url_action = 'https://api.express-pay.by/v1/web_cardinvoices';

if($_SERVER['REQUEST_METHOD'] == "POST")
{


    if(isset($_REQUEST['ExpressPayAccountNumber']))
    {
        header('Location: ' . $_REQUEST['returnUrl']);
    }
    else{
        $accountNo = GUID();
        $amount = str_replace('.',',',$_REQUEST['amount']);

        $settings['amount'] = $amount;
        $settings['accountNo'] = $accountNo;
        $settings['returnUrl'] = $settings['site'].'/express-pay.php?status=success&returnUrl='.$_SERVER['HTTP_REFERER'];
        $settings['failUrl'] = $settings['site'].'/express-pay.php?status=fail&returnUrl='.$_SERVER['HTTP_REFERER'];

        $signature = compute_signature_add_invoice($settings);

        ?>
        <form id='form-card-invoice-add' action='<?= $url_action ?>' method='POST'>
            <input type='hidden' name='ServiceId' value="<?= $settings['serviceId'] ?>" />
            <input type='hidden' name='AccountNo' value="<?= $accountNo ?>" />
            <input type='hidden' name='Amount' value="<?= $amount ?>" />
            <input type='hidden' name='Currency' value="<?= $settings['currency'] ?>" />
            <input type='hidden' name='ReturnType' value="<?= $settings['returnType'] ?>" />
            <input type='hidden' name='ReturnUrl' value="<?= $settings['returnUrl'] ?>" />
            <input type='hidden' name='FailUrl' value="<?= $settings['failUrl'] ?>" />
            <input type='hidden' name='Info' value="<?= $settings['info'] ?>" />
            <input type='hidden' name='Signature' value="<?= $signature ?>" />
        </form>

        <script type='text/javascript'>
            var form = document.getElementById('form-card-invoice-add');
            form.submit();
        </script>
        <?php
    }

}
else{
    header('Location: /');
}

function compute_signature_add_invoice($settings) {
    $secret_word = trim($settings['secretWord']);
    $normalized_params = array_change_key_case($settings, CASE_LOWER);
    $api_method = array(
        "serviceid",
        "accountno",
        "expiration",
        "amount",
        "currency",
        "info",
        "returnurl",
        "failurl",
        "language",
        "sessiontimeoutsecs",
        "expirationdate",
        "returntype"
    );

    $result = $settings['token'];

    foreach ($api_method as $item)
        $result .= ( isset($normalized_params[$item]) ) ? $normalized_params[$item] : '';

    $hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

    return $hash;
}

function GUID()
{

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535));
}