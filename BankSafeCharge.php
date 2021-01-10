<?php

/**
 * Bank SafeCharge API module
 * Test card: 4000020951595032 as the card number
 * $this->terminal->bank_login - merchantId
 * $this->terminal->bank_password - merchantSecretKey
 * Type: server to server
 * Author: km@continentpay.com
 */
class BankSafeCharge extends BankModule implements IBank
{
    const MESSAGES_FILENAME = 'SafeChargeErrorCodes.txt';
    const NOTIFY_URL = 'https://services.continentpay.com/3d2Fetcher/';
    const TEST_NOTIFY_URL = 'http://test.local/3d2Fetcher/';
    const THREED2_REDIRECTOR_URL = 'https://services.continentpay.com/3d2Redirector/';
    const TEST_THREED2_REDIRECTOR_URL = 'http://test.local/3d2Redirector/';
    const MERCHANT_URL = 'http://test.local';
    const DEFAULT_TERM_URL = 'https://services.continentpay.com/defaultTermUrl/';

    public $data;
    public $terminal;
    public $terminal_data = [];
    public $mask_values = []; // values to mask for logs
    private $request_settings = [
        'url' => '',
        'headers' => [],
        'method' => 'POST',
        'body' => '',
    ];
    private $response_results = [
        'body' => '',
        'error' => '',
        'httpcode' => '',
    ];
    private $sub_types = [ // allowed subrequest in this class
        //'charge' => 'AUTH',
        //'refund' => 'REFUND',
    ];
    public $order_id;
    public $amount;
    private $bank_currencies = [
        'EUR', 'USD'
    ];
    private $allow_no3d = false;
    private $token;
    private $relatedTransactionId;
    private $threeD;
    private $threeDv2;
    private $methodCompletionInd = 'U';
    private $screens = [
        ['1080', '1920'], ['1080', '1920'], ['1080', '1920'], ['750', '1334'], ['2960', '1440'], ['480', '800'], ['640', '1136'], ['2732', '2048'], ['768', '1024'], ['1440', '2560'], ['720', '1280'], ['720', '1280'], ['640', '960'], ['1080', '1920'], ['1080', '1920'], ['1920', '1080'], ['2048', '1536'], ['2048', '1536'], ['2048', '1536'], ['1920', '1080'], ['1440', '2560'], ['1080', '1920'], ['1440', '2560'], ['800', '1280'], ['2048', '1536'], ['2736', '1824'], ['2304', '1440'], ['1440', '2560'], ['1440', '2560'], ['1440', '2560'], ['1440', '2560'], ['1080', '1920'], ['1080', '1920'], ['1280', '720'], ['1440', '1440'], ['480', '800'], ['1440', '2560'], ['1920', '1080'], ['1366', '768'], ['2560', '1700'], ['1366', '768'], ['2560', '1600'], ['2560', '1440'], ['1921', '1080'], ['2560', '1440'], ['1366', '768'], ['1440', '900'], ['1280', '800'], ['2560', '1600'], ['1440', '900'], ['1680', '1050'], ['2880', '1800'], ['1920', '1200'], ['1080', '1920'], ['768', '1280'], ['2160', '4096'], ['768', '1366'], ['1366', '768'], ['3840', '2160'], ['1600', '900'], ['1920', '1080'], ['2560', '1440'], ['1920', '1200'], ['2560', '1440'], ['2560', '1600'], ['1920', '1080'], ['1366', '768'], ['2560', '1440'], ['1366', '768'], ['3000', '2000'], ['2160', '3840'], ['768', '1280'], ['1366', '768'], ['1440', '900'], ['2560', '1600'], ['2880', '1800'], ['4096', '2304'], ['5120', '2880'], ['3840', '2160'], ['1920', '1080'], ['1280', '800'], ['1920', '1080'], ['1366', '768'], ['1920', '1080'], ['720', '1280'], ['480', '800'], ['1280', '720'], ['2560', '1440'], ['480', '800'], ['480', '800'], ['480', '800'], ['1080', '1920'], ['1080', '1920'], ['1080', '1920'], ['768', '1280'], ['1080', '1920'], ['768', '1280'], ['720', '1280'], ['1080', '1920'], ['480', '854'], ['540', '960'], ['540', '960'], ['540', '960'], ['1440', '2560'], ['1440', '2560'], ['1440', '2560'], ['720', '1280'], ['540', '960'], ['1080', '1920'], ['1080', '1920'], ['1080', '1920'], ['720', '1280'], ['720', '1280'], ['480', '800'], ['480', '800'], ['720', '1280'], ['1080', '1920'], ['480', '800'], ['720', '1280'], ['1080', '1920'], ['1280', '720'], ['1920', '1080'], ['720', '1280'], ['1080', '1920'], ['1080', '1920'], ['1080', '1920'], ['540', '960'], ['1280', '720'], ['1920', '1080'], ['1920', '1080'], ['1920', '1080'], ['1280', '720'], ['1280', '720'], ['1280', '720'], ['854', '480'], ['1920', '1080'], ['1920', '1080'], ['800', '480'], ['1920', '1080'], ['1920', '1080'], ['1920', '1080'], ['2560', '1440'], ['1920', '1080'], ['1920', '1080'], ['960', '540'], ['1920', '1080'], ['1920', '1080'], ['720', '720'], ['768', '1280'], ['960', '540'], ['1280', '768'], ['1280', '720'], ['1280', '720'], ['480', '360'], ['320', '480'], ['750', '1334'], ['750', '1334'], ['768', '1280'], ['480', '800'], ['480', '800'], ['480', '800'], ['480', '800'], ['768', '1280'], ['768', '1280'], ['1440', '2560'], ['1280', '720'], ['1440', '2560'], ['1080', '1920'], ['1440', '2560'], ['1600', '1200'], ['2048', '1536'], ['1280', '800'], ['768', '1280'], ['1280', '800'], ['1024', '600'], ['2048', '1536'], ['600', '1024'], ['800', '1280'], ['1200', '1920'], ['1280', '720'], ['800', '1280'], ['1200', '1920'], ['1200', '1920'], ['600', '800'], ['1024', '600'], ['1024', '600'], ['1280', '800'], ['1920', '1080'], ['800', '1280'], ['600', '1024'], ['800', '1280'], ['2048', '1536'], ['1280', '800'], ['800', '1280'], ['1280', '800'], ['1280', '800'], ['1024', '600'], ['1024', '600'], ['1280', '800'], ['1024', '600'], ['768', '1024'], ['1536', '2048'], ['900', '1600'], ['1080', '1920'], ['1080', '1920'], ['1280', '800'], ['1280', '800'], ['1024', '600'], ['1280', '800'], ['1280', '800'], ['1280', '800'], ['1280', '800'], ['2048', '1536'], ['1920', '1200'], ['2560', '1600'], ['2560', '1600'], ['1280', '800'], ['2160', '1440'], ['2736', '1824'], ['2960', '1440'], ['750', '1334'], ['1125', '2436'], ['828', '1792'], ['1125', '2436'], ['1242', '2688'],
    ];

    /**
     * BankSafeCharge constructor.
     * @param Terminals $terminal
     */
    public function __construct(Terminals $terminal)
    {
        $this->data = [];
        $this->terminal = $terminal;
        $this->processor_url = $this->terminal->is_test
            ? $this->terminal->processor->test_url
            : $this->terminal->processor->url;
        $this->terminal_data = json_decode($terminal->data, 1);
        parent::__construct($terminal);
    }

    /**
     * @param Creditcards $card
     * @param Cardholders $ch
     * @param Orders $order
     * @return bool
     */
    public function Authorize(Creditcards $card, Cardholders $ch, Orders $order)
    {
        return $this->initRequest($card, $ch, $order, false);
    }

    /**
     * @param Creditcards $card
     * @param Cardholders $ch
     * @param Orders $order
     * @return bool
     */
    public function AuthCapture(Creditcards $card, Cardholders $ch, Orders $order)
    {
        return $this->initRequest($card, $ch, $order, true);
    }

    /**
     * @param Orders $order
     * @param Transactions $trData
     * @return mixed
     */
    public function Capture(Orders $order, Transactions $trData)
    {
        return $this->subRequest(
            $order,
            $trData->bank_unique_id,
            $trData->bank_reference_number,
            'charge'
        );
    }

    /**
     * @param Orders $order
     * @param Transactions $trData
     * @return mixed
     */
    public function Refund(Orders $order, Transactions $trData)
    {
        return $this->subRequest(
            $order,
            $trData->bank_unique_id,
            $trData->bank_reference_number,
            'refund'
        );
    }

    /**
     * @param Orders $order
     * @param Transactions $trData
     * @param $amount
     * @return mixed
     */
    public function PartialRefund(Orders $order, Transactions $trData, $amount)
    {
        return $this->subRequest(
            $order,
            $trData->bank_unique_id,
            $trData->bank_reference_number,
            'refund',
            $amount
        );
    }

    /**
     * @param Orders $order
     * @param Transactions $trData
     * @return mixed
     */
    public function Cancel(Orders $order, Transactions $trData)
    {
        return $this->subRequest(
            $order,
            $trData->bank_unique_id,
            $trData->bank_reference_number,
            'cancel'
        );
    }

    /**
     * @param Creditcards $card
     * @param Cardholders $ch
     * @param Orders $order
     * @param false $auto_charge
     * @return bool
     */
    public function initRequest(Creditcards $card, Cardholders $ch, Orders &$order, $auto_charge = false)
    {
        $this->setResponceGeneral($order->amount, $order->currency_id, $order->reference_number);
        if ($error_arr = $this->checkRequestInitialErrors($order, $auto_charge)) {
            return $this->exitError($error_arr['message'], $error_arr['number']);
        }

        // prepare params
        $this->order_id = str_pad($order->order_id, 11, '0', STR_PAD_LEFT);
        $this->amount = number_format($order->amount, 2, '.', '');
        $this->mask_values = [$card->number, $card->cvv_code, $this->terminal->bank_password];

        if (empty($this->token = $this->getBankToken())) {
            return $this->exitError('Bad token', '005');
        }
        if (!$this->initPayment($card, $ch, $order)) {
            return $this->exitError('Not init', '092');
        }

        // capture
        $query = $this->makeRequestBodyArray($card, $ch, $order, $auto_charge);
        $this->sendRequest([
            'url' => $this->processor_url . 'payment.do',
            'method' => 'POST',
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode($query, JSON_PRETTY_PRINT),
            'nofollow' => true,
        ]);
        $responce = $this->response_results['body'];

        if (!$this->isBodyLikeJson($responce)) {
            return $this->exitError('Bad answer', '005');
        }
        $responce_json = json_decode($responce, true);

        if ($this->assertArrayValue($responce_json, 'transactionId')) {
            $this->bank_unique_id = $responce_json['transactionId'];
        }

        if (    !$this->assertArrayValue($responce_json, 'status', 'SUCCESS')
            &&  !$this->assertArrayValue($responce_json, 'transactionStatus')
        ) {
            return $this->exitError('Processing error', '001');
        }

        if (!$this->assertArrayValue($responce_json, 'paymentOption.card.threeD.eci', '7')) {
            return $this->exitError('Payment authentication was not performed', '005');
        }

        // 3d 2.0 approved
        if (    $this->threeDv2
            &&  $this->assertArrayValue($responce_json, 'transactionStatus', 'APPROVED')
            &&  (!$this->assertArrayValue($responce_json, 'paymentOption.card.threeD.result')
                ||  !$this->assertArrayValue($responce_json, 'paymentOption.card.threeD.result', 'N'))
        ) {
            $this->data['status'] = 'approved';
            return true;
        }

        // 3d 2.0 fail frictionless
        if (    $this->threeDv2
            &&  $this->assertArrayValue($responce_json, 'transactionStatus', 'APPROVED')
            &&  $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.result', 'N')
        ) {
            $this->message = '';
            if ($this->assertArrayValue($responce_json, 'paymentOption.card.threeD.threeDReason')) {
                $this->message = $responce_json['paymentOption']['card']['threeD']['threeDReason'];
            }
            return $this->exitError($this->message, '005');
        }

        // no 3d enrolled not approved
        if (    !$this->threeDv2
            &&  $this->assertArrayValue($responce_json, 'transactionStatus', 'APPROVED')
        ) {
            return $this->exitError('No 3d enrolled', '004');
        }

        // success 3d
        if (    $this->assertArrayValue($responce_json, 'transactionStatus', 'REDIRECT')
            &&  $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.acsUrl')
        ) {
            $this->apply3d($responce_json, $order);
            return true;
        }

        return $this->exitError($responce_json['gwErrorReason'] ?? 'Unknown', '005');
    }

    protected function subRequest(
        &$order = null,
        $rrn, // parenttransactionreference
        $int_ref,
        $transaction_type = 'charge',
        $amount = false
    )
    {
        return $this->exitError('Transaction type not allowed', '005');
    }

    public function ThreeDSecure(Creditcards $card, Cardholders $ch, Orders $order, $data_in)
    {
        $this->data['status'] = 'error';
        if (!$this->isValidTerminal()) {
            return $this->exitError('Terminal error', '004');
        }

        if (!is_object($order)) {
            return $this->exitError('Order error', '005');
        }

        // get 3d data from last Auth3d or capture3d transaction
        $transaction = Transactions::model()->findByAttributes([
            'order_id' => $order->order_id,
            'status' => 'processing',
        ], [
            'condition' => 'transaction_type IN ('
                . '"' . Transactions::TR_TYPE_Authorizate . '"'
                . ', "' . Transactions::TR_TYPE_AuthCapture . '"'
                . ')'
        ]);
        if (!is_object($transaction)) {
            return $this->exitError('Transaction 3d data not found', '005');
        }

        $transaction_data = @unserialize($transaction->data);
        if (!$this->assertArrayValue($transaction_data, 'query')) {
            return $this->exitError('Order data error ', '005');
        }

        // prepare params
        $this->mask_values = [$this->terminal->bank_password];

        // set request
        $query = $transaction_data['query'];
        if ($transaction_data['query']['paymentOption']['card']['threeD']['v2supported'] == 'true') {
            $query['paymentOption']['card']['threeD'] = null;
            $query['relatedTransactionId'] = $transaction_data['transactionId'];
        } else {
            $query['paymentOption']['card']['threeD'] = ['paResponse' => $data_in->pares];
        }
        $this->sendRequest([
            'url' => $this->processor_url . 'payment.do',
            'method' => 'POST',
            'headers' => [
                'Content-Type: application/json',
            ],
            'body' => json_encode($query, JSON_PRETTY_PRINT),
            'nofollow' => true,
        ]);

        // set main answer fields
        $this->data['currency'] = $order->currency_id;
        $this->data['amount'] = number_format($order->amount, 2, '.', '');
        $this->response['extra_id'] = $order->reference_number;
        $this->response['amount'] = $order->amount * 100;

        $responce = $this->response_results['body'];
        if (!$this->isBodyLikeJson($responce)) {
            return $this->exitError('Bad answer', '005');
        }
        $responce_json = json_decode($responce, true);

        if (    !is_array($responce_json)
            ||  !isset($responce_json['status'], $responce_json['transactionStatus'])
            ||  ($responce_json['status'] != 'SUCCESS')
            ||  ($responce_json['transactionStatus'] != 'APPROVED')
            ||  !isset($responce_json['paymentOption']['card']['threeD']['result'])
            ||  !in_array($responce_json['paymentOption']['card']['threeD']['result'], ['Y', 'A', 'U', 'E', ''])
        ) {
            $this->message = 'Payment declined';
            if (isset($responce_json['gwErrorReason'])) {
                $this->message = $responce_json['gwErrorReason'];
            }
            if (isset($responce_json['paymentOption']['card']['threeD']['threeDReason'])) {
                $this->message = $responce_json['paymentOption']['card']['threeD']['threeDReason'];
            }
            return $this->exitError($this->message, '005');
        }

        $this->data['status'] = 'approved';
        return true;

        $this->errcode = $this->ErrorMapping(
            isset($responce_json['gwErrorReason'])
                ? $responce_json['gwErrorReason']
                : ''
        );
        $this->data['status'] = 'declined';
        return false;
    }

    /**
     * /* Load currency rates by date
     * /* $date - string XXXX-XX-XX
     * /***/
    public function getCurrencyRate($date, $from, $to, $amount, $transaction_id = false)
    {
        return null;
        // no conversions
    }

    // Helpers ----------------------------------------------------------------

    /**
     * /* Check terminal init params
     */
    private function isValidTerminal()
    {
        if (!isset($this->terminal_data['MERCHANT_SITE_ID'])) {
            return $this->exitError('Terminal MERCHANT_SITE_ID data is empty', '082');
        }
        return true;
    }

    private function setResponceGeneral($amount, $currency_id, $reference_number)
    {
        // set main answer fields
        $this->data['currency'] = $currency_id;
        $this->data['amount'] = $amount;
        $this->response['extra_id'] = $reference_number;
        $this->response['amount'] = $amount * 100;
    }

    private function getBankToken()
    {
        $query = [
            'merchantId' => $this->terminal->bank_login,
            'merchantSiteId' => $this->terminal_data['MERCHANT_SITE_ID'],
            'clientRequestId' => $this->order_id,
            'timeStamp' => date("YmdHis", strtotime('+1 day')), // order expire time
        ];
        $query['checksum'] = hash(
            "sha256",
            $query['merchantId']
            . $query['merchantSiteId']
            . $query['clientRequestId']
            . $query['timeStamp']
            . $this->terminal->bank_password // merchantSecretKey
        );
        $this->sendRequest([
            'url' => $this->processor_url . 'getSessionToken.do',
            'method' => 'POST',
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode($query, JSON_PRETTY_PRINT),
            'nofollow' => true,
        ]);
        $responce = $this->response_results['body'];

        if (!$this->isBodyLikeJson($responce)) {
            return false;
        }
        $responce_json = json_decode($responce, true);
        if (!is_array($responce_json)
            || !isset($responce_json['sessionToken'], $responce_json['status'])
            || ($responce_json['status'] != 'SUCCESS')
        ) {
            return false;
        }
        return $responce_json['sessionToken'];
    }

    private function initPayment(Creditcards $card, Cardholders $ch, Orders $order)
    {
        // make query
        $query = [
            'merchantId' => $this->terminal->bank_login,
            'merchantSiteId' => $this->terminal_data['MERCHANT_SITE_ID'],
            'clientRequestId' => $this->order_id,
            'amount' => $this->amount,
            'currency' => strtoupper($order->currency_id),
            'timeStamp' => date("YmdHis", strtotime('+1 day')), // order expire time
        ];
        $query['checksum'] = hash("sha256", join('', $query) . $this->terminal->bank_password);
        $query['clientUniqueId'] = $this->order_id;
        $query['orderId'] = $this->order_id;
        $query['sessionToken'] = $this->token;
        $query['deviceDetails'] = [
            'ipAddress' => long2ip($ch->ip_address),
        ];
        $query['paymentOption'] = [
            'card' => [
                'cardNumber' => $card->number,
                'cardHolderName' => ($this->terminal->is_test
                    ? $ch->first_name//'FL-BRW1'
                    : ($ch->first_name . ' ' . $ch->last_name)),
                'expirationMonth' => str_pad($card->expire_month, 2, '0', STR_PAD_LEFT),
                'expirationYear' => (string)$card->expire_year,
                'CVV' => $card->cvv_code,
                'threeD' => [
                    'methodNotificationUrl' => self::NOTIFY_URL,
                    'notificationURL' => self::NOTIFY_URL,
                ],
            ]
        ];

        $this->sendRequest([
            'url' => $this->processor_url . 'initPayment.do',
            'method' => 'POST',
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode($query, JSON_PRETTY_PRINT),
            'nofollow' => true,
        ]);
        $responce = $this->response_results['body'];

        if (!$this->isBodyLikeJson($responce)) {
            return false;
        }
        $responce_json = json_decode($responce, true);
        if (    !$this->assertArrayValue($responce_json, 'status', 'SUCCESS')
            ||  !$this->assertArrayValue($responce_json, 'transactionStatus', 'APPROVED')
        ) {
            return false;
        }

        if (    $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.v2supported', 'true')
            &&  $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.methodUrl')
            &&  $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.methodPayload')
            &&  $this->assertArrayValue($responce_json, 'paymentOption.card.threeD.methodPayload')
        ) {
            $this->threeDv2 = true;
        } else {
            $this->threeDv2 = false;
        }

        if ($this->assertArrayValue($responce_json, 'paymentOption.card.threeD.methodUrl')) {
            if (empty($responce_json['paymentOption']['card']['threeD']['methodUrl'])) {
                $this->methodCompletionInd = 'U';
            } else {
                $this->methodCompletionInd = 'Y';
            }
        } else {
            $this->methodCompletionInd = 'U';
        }

        $this->relatedTransactionId = $responce_json['transactionId'];
        $this->threeD = $responce_json['paymentOption']['card']['threeD'];
        return true; // is 3d enrolled
    }

    private function makeRequestBodyArray($card, $ch, $order, $auto_charge)
    {
        $query = [
            'merchantId' => $this->terminal->bank_login,
            'merchantSiteId' => $this->terminal_data['MERCHANT_SITE_ID'],
            'clientRequestId' => $this->order_id,
            'amount' => $this->amount,
            'currency' => strtoupper($order->currency_id),
            'timeStamp' => date("YmdHis", strtotime('+1 day')), // order expire time
        ];
        $query['checksum'] = hash("sha256", join('', $query) . $this->terminal->bank_password);
        $query['clientUniqueId'] = $this->order_id;
        $query['sessionToken'] = $this->token;
        //$query['transactionType'] = 'Auth';//'Sale';
        $query['authenticationOnlyType'] = '05'; // 04 = Maintain card information
        // 05 = Account verification only
        $query['relatedTransactionId'] = $this->relatedTransactionId;
        $query['paymentOption'] = [
            'card' => [
                'cardNumber' => $card->number,
                'cardHolderName' => ($this->terminal->is_test
                    ? $ch->first_name//'FL-BRW1'
                    : ($ch->first_name . ' ' . $ch->last_name)),
                'expirationMonth' => str_pad($card->expire_month, 2, '0', STR_PAD_LEFT),
                'expirationYear' => (string)$card->expire_year,
                'CVV' => $card->cvv_code,
                'threeD' => $this->threeD,
            ]
        ];
        $uagent = Yii::createComponent('application.extensions.RandomUagent.UserAgent');
        $uagent = $uagent::random(['os_type' => 'Windows',]);
        $timezone_offset = 0;
        if (function_exists('geoip_time_zone_by_country_and_region')) {
            $timezone = geoip_time_zone_by_country_and_region($ch->country_id, '01');
            if (!empty($timezone)) {
                $timezone_offset = timezone_offset_get(
                        timezone_open($timezone),
                        new DateTime()
                    ) / 60;
            }
        }
        $screen_arr = $this->screens[rand(0, count($this->screens) - 1)];
        $screen_arr[2] = '24';
        if (!empty(Yii::app()->request->getQuery('screen'))) {
            $screen_arr = explode('x', Yii::app()->request->getQuery('screen'));
        } //from Request
        if (!empty(Yii::app()->request->getQuery('uagent'))) {
            $uagent = Yii::app()->request->getQuery('uagent');
        }
        $query['paymentOption']['card']['threeD'] += [
            'notificationURL' => (empty(YII_DEBUG)
                ? self::NOTIFY_URL . '?ref=' . $order->reference_number
                : self::TEST_NOTIFY_URL . '?ref=' . $order->reference_number),
            'merchantURL' => self::MERCHANT_URL,
            'version' => $this->threeD['version'],
            'methodCompletionInd' => $this->methodCompletionInd,
            'platformType' => '02',
            'browserDetails' => [
                "acceptHeader" => "text/html,application/xhtml+xml",
                "ip" => long2ip($ch->ip_address),
                "javaEnabled" => "FALSE",
                "javaScriptEnabled" => "TRUE",
                "language" => "en-US",
                "colorDepth" => $screen_arr[2],//"24",
                "screenHeight" => $screen_arr[1],//'864',//$screen[0],
                "screenWidth" => $screen_arr[0],//'1536',//$screen[1],
                "timeZone" => $timezone_offset,
                "userAgent" => $uagent,//"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36",//$uagent,
            ],
            "challengePreference" => "03",
            "challengeWindowSize" => "05",
        ];
        $query['items'] = [["quantity" => "1", "price" => $this->amount, "name" => "Deposit"]];
        $query['billingAddress'] = [
            "email" => $ch->email,
            "firstName" => $ch->first_name,
            "lastName" => $ch->last_name,
            "phone" => $ch->phone,
            "address" => $ch->address,
            "city" => $ch->city,
            "country" => strtoupper($ch->country_id),
            "state" => $ch->state,
            "zip" => $ch->zip,
        ];
        $query['deviceDetails'] = [
            'ipAddress' => long2ip($ch->ip_address),
        ];
        return $query;
    }

    /** Apply 3d responce any version
     * @param $responce_json
     * @param Orders $order
     */
    private function apply3d($responce_json, Orders $order)
    {
        $threeD = $responce_json['paymentOption']['card']['threeD'];
        $this->data['rrn'] = $order->reference_number;
        $this->bank_unique_id = $responce_json['transactionId'];
        $this->data['status'] = 'awaiting_3d';
        if (!empty($threeD['cReq'])) { // 3D 2.0
            $data3d = [
                'acs-url' => (empty(YII_DEBUG)
                    ? self::THREED2_REDIRECTOR_URL
                    : self::TEST_THREED2_REDIRECTOR_URL),
                'md' => base64_encode(
                    json_encode([
                        'acsUrl' => $threeD['acsUrl'],
                        'ref' => $order->reference_number,
                    ])
                ),
                'pareq' => $threeD['cReq'],
                'term_url' => isset($threeD['TermUrl']) ? $threeD['TermUrl'] : '',
            ];
        } else { // 3D 1.0
            $data3d = [
                'acs-url' => $threeD['acsUrl'],
                'md' => isset($threeD['MD']) ? $threeD['MD'] : '',
                'pareq' => isset($threeD['paRequest']) ? $threeD['paRequest'] : '',
                'term_url' => isset($threeD['TermUrl']) ? $threeD['TermUrl'] : '',
            ];
        }
        $this->data['data'] = serialize([
            'query' => $query,
            'transactionId' => $responce_json['transactionId'],
            'term_url' => isset($threeD['TermUrl']) ? $threeD['TermUrl'] : '',
        ]);
        $this->response['threedsecure'] = $data3d;
        $this->response['threedsecure']['term_url'] = self::DEFAULT_TERM_URL;
        $this->response['threedsecure']['method'] = 'POST';
        $this->response['threedsecure']['form3d_html'] = htmlspecialchars($this->make3dForm());
    }

    /**
     * Return array($proxy, $type, $pwd)
     */
    private function readProxy()
    {
        $proxy = isset($this->terminal_data['PROXY'])
            ? $this->terminal_data['PROXY']
            : '';
        $proxy_type = CURLPROXY_HTTP;
        if (isset($this->terminal_data['PROXY_TYPE'])) {
            $proxy_type = ($this->terminal_data['PROXY_TYPE'] == 'socks')
                ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP;
        }
        $proxy_usrpwd = null;
        if (isset($this->terminal_data['PROXY_USRPWD'])) {
            $proxy_usrpwd = $this->terminal_data['PROXY_USRPWD'];
        }
        return [$proxy, $proxy_type, $proxy_usrpwd];
    }

    private function logRequest()
    {
        $log_request = $this->request_settings;
        if (count($this->mask_values)) {
            // mask values for log
            foreach ($this->mask_values as $value) {
                if ((strlen($value) >= 12) && is_numeric($value)) {
                    $log_request['body'] = str_replace(
                        $value,
                        substr($value, 0, 6) . str_pad('', strlen($value) - 10, '*') . substr($value, -4),
                        $log_request['body']
                    );
                } else {
                    $log_request['body'] = str_replace(
                        $value,
                        str_pad('', strlen($value), '*'),
                        $log_request['body']
                    );
                }
            }
        }

        $this->actionLog(
            ActionLog::AL_TYPE_BANKREQUEST,
            $this->getLogLines($log_request)
        );
    }

    private function logResponse()
    {
        $this->actionLog(
            ActionLog::AL_TYPE_BANKRESPONSE,
            $this->response_results
        );
    }

    private function getLogLines($arr)
    {
        $text = '';
        foreach ($arr as $name => $value) {
            $text .= ($text === '' ? '' : "\n") . "$name = " . var_export($value, 1);
        }
        return $text;
    }

    private function sendRequest($settings = null)
    {
        if (!is_null($settings)) {
            $this->request_settings = $settings;
        }

        $this->logRequest();

        $OutsideRequest = Yii::createComponent('OutsideRequest')
            ->init(
                $this->request_settings['url'],
                $this->request_settings['method']
            )
            ->setLog($this->terminal->is_test);
        if (!empty($this->request_settings['headers'])) {
            $OutsideRequest->setHeaders($this->request_settings['headers']);
        }
        if (!empty($this->request_settings['body'])) {
            $OutsideRequest->setPostFields($this->request_settings['body']);
        }
        if (!empty($this->request_settings['nofollow'])) {
            $OutsideRequest->setFollow(false);
        }
        list($proxy, $proxy_type, $proxy_usrpwd) = $this->readProxy();
        $OutsideRequest->setProxy($proxy, $proxy_type, $proxy_usrpwd);

        $OutsideRequest->send();

        $error = $OutsideRequest->getError();
        $this->response_results = [];
        $this->response_results['error'] = empty($error) ? '' : $error;
        $this->response_results['httpcode'] = $OutsideRequest->getHttpCode();
        $this->response_results['body'] = $OutsideRequest->getResponce();
        $info = $OutsideRequest->getInfo();
        if (!empty($info['redirect_url'])) {
            $this->response_results['redirect_url'] = $info['redirect_url'];
        }

        $this->logResponse();

        return $info;
    }

    private function checkRequestInitialErrors($order, $auto_charge)
    {
        if (!$this->allow_no3d && !$this->terminal->is3d) {
            return ['message' => 'Non 3D card not allowed', 'number' => '004'];
        }
        if (!$auto_charge) {
            return ['message' => 'Authorise not allowed', 'number' => '512'];
        }
        if (!$this->isValidTerminal()) {
            return ['message' => 'Terminal attributes error', 'number' => '005'];
        }
        if (!in_array($order->currency_id, $this->bank_currencies)) {
            return ['message' => 'Terminal currency ' . $order->currency_id . ' not allowed', 'number' => '082'];
        }
        return false;
    }

    private function ErrorMapping($number)
    {
        $error_code = '005';
        $file = file((Yii::app()->getBasePath()) . '/messages/' . self::MESSAGES_FILENAME);
        foreach ($file as $string) {
            $data = explode(' ', $string, 3);
            if (count($data) < 3) {
                break;
            }
            list($_error_code, $_error_number, $_error_message) = $data;
            if ($number == $_error_number) {
                $error_code = $_error_code;
                break;
            }
        }
        return $error_code;
    }

    private function make3dForm()
    {
        $threed = $this->response['threedsecure'];
        return '<form id="form3d" method="POST" action="' . $threed['acs-url'] . '">'
            . '<input type="hidden" name="MD" value="' . $threed['md'] . '">'
            . '<input type="hidden" name="PaReq" value="' . $threed['pareq'] . '">'
            . '<input type="hidden" name="extra_id" value="' . $this->response['extra_id'] . '">'
            . '<input type="hidden" name="TermUrl" value="' . $threed['term_url'] . '">'
            . '</form>'
            . '<script>window.onload = function(){document.forms["form3d"].submit();}</script>';
    }

    private function read3dData($form3d)
    {
        return [
            'acs-url' => preg_replace('/^.*action=["\']([^"\']+)["\'].*$/ims', '$1', $form3d),
            'pareq' => preg_replace('/^.*name=["\']PaReq["\'][^>]+value=["\']([^"\']+)["\'].*$/ims', '$1', $form3d),
            'term_url' => preg_replace('/^.*name=["\']TermUrl["\'][^>]+value=["\']?([^"\' \>]+)["\']?.*$/ims', '$1', $form3d),
            'md' => preg_replace('/^.*name=["\']MD["\'][^>]+value=["\']([^"\']+)["\'].*$/ims', '$1', $form3d),
        ];
    }

    /**
     * Check isset and equals value inside array
     * @param $arr
     * @param $path
     * @param null $value
     * @param string $separator
     * @return bool
     */
    private function assertArrayValue($arr, $path, $value = null, $separator = '.')
    {
        if (!is_array($arr)) {
            return false;
        }
        $ids = explode($separator, $path);
        $obj = $arr;
        foreach ($ids as $id) {
            if (!isset($obj[$id])) {
                return false;
            }
            $obj = $obj[$id];
        }
        if (!is_null($value) && ($obj !== $value)) {
            return false;
        }
        return true;
    }

    /**
     * Check is param starts like JSON string
     * @param $body
     * @return bool
     */
    private function isBodyLikeJson($body)
    {
        if (empty($body) || (strpos(trim($body), '{') !== 0)) {
            return false;
        }
        return true;
    }
}
