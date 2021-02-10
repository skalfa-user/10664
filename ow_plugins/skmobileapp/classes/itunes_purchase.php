<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class SKMOBILEAPP_CLASS_ItunesPurchase extends SKMOBILEAPP_CLASS_AbstractInAppPurchase
{
    // The App Store could not read the JSON object you provided.
    const COULD_NOT_READ_JSON_OBJECT               = 21000;

    // The data in the receipt-data property was malformed.
    const RECEIPT_DATA_PROPERTY_MALFORMED          = 21002;

    // The receipt could not be authenticated.
    const RECEIPT_COULD_NOT_BE_AUTHENTICATED       = 21003;

    // The shared secret you provided does not match the shared secret on file for your account.
    const SHARED_SECRET_MISMATCH                   = 21004;

    // The receipt server is not currently available.
    const RECEIPT_SERVER_UNAVAILABLE               = 21005;

    // This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response.
    const VALID_RECEIPT_BUT_SUBSCRIPTION_EXPIRED   = 21006;

    // This receipt is a sandbox receipt, but it was sent to the production service for verification.
    const SANDBOX_RECEIPT_SENT_TO_PRODUCTION_ERROR = 21007;

    // This receipt is a production receipt, but it was sent to the sandbox service for verification.
    const PRODUCTION_RECIEPT_SENT_TO_SANDBOX_ERROR = 21008;

    // This receipt valid.
    const RECEIPT_VALID                            = 0;

    const CURL_ERROR                               = 60001;

    const ITUNES_PRODUCTION_VERIFY_URL = 'https://buy.itunes.apple.com/verifyReceipt';
    const ITUNES_SANDBOX_VERIFY_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    const CONFIG_IOS_TEST_MODE  = 'inapps_ios_test_mode';
    const CONFIG_SHARED_SECRET  = 'inapps_itunes_shared_secret';


    /**
     * @var OW_Log
     */
    protected $logger;
    protected $config;

    private $mode = 'live';
    private $endpoint = null;
    private $sharedSecret = null;
    private $retrySandbox = true;
    private $retryProduction = false;

    /**
     * SKMOBILEAPP_CLASS_iosPurchase constructor.
     */
    public function __construct()
    {
        $this->logger = OW::getLogger('skmobileapp');
        $this->config = OW::getConfig()->getValues('skmobileapp');

        $this->mode  = $this->config[self::CONFIG_IOS_TEST_MODE] ? 'test' : 'live';
        $this->sharedSecret = $this->config[self::CONFIG_SHARED_SECRET];

        switch ( $this->mode )
        {
            case 'test':
                $this->endpoint = self::ITUNES_SANDBOX_VERIFY_URL;
                break;

            case 'live':
                $this->endpoint = self::ITUNES_PRODUCTION_VERIFY_URL;
                break;
        }
    }

    public function getPurchaseInfo( $receipt )
    {
         $receiptData = (object) array(
            'receipt-data' => $receipt,
        );

        if ( $this->sharedSecret != null )
        {
            $receiptData->password = $this->sharedSecret;
        }

        $clientParams = new UTIL_HttpClientParams();
        $clientParams->setBody(json_encode($receiptData));

        $response = UTIL_HttpClient::post($this->endpoint, $clientParams);

        // Ensure the http status code was 200
        if ( !$response || !$response->getBody() )
        {
            $this->logger->addEntry('Unable to get response', 'http_response_exception');
            $this->logger->writeLog();

            return self::FAILURE;
        }

        // Parse the response data
        $data = json_decode($response->getBody(), true);

        // Ensure response data was a valid JSON string
        if ( !is_array($data) )
        {
            return self::FAILURE;
        }

        if ( $this->mode == 'test' && $data['status'] === self::SANDBOX_RECEIPT_SENT_TO_PRODUCTION_ERROR && $this->retrySandbox )
        {
            return $this->getPurchaseInfo($receipt);
        }

        if ( $this->mode == 'live' && $data['status'] === self::PRODUCTION_RECIEPT_SENT_TO_SANDBOX_ERROR && $this->retryProduction )
        {
            return $this->getPurchaseInfo($receipt);
        }

        return $data;
    }

    public function getPurchaseSubscriptionInfo( $receipt )
    {
        $data = [];

        $purchaseInfo = $this->getPurchaseInfo($receipt);

        if ( !$purchaseInfo )
        {
            return $data;
        }

        $error = 'Error: Status codes - ' . $purchaseInfo['status'];

        switch ( $purchaseInfo['status'] )
        {
            case self::RECEIPT_VALID:
            case self::VALID_RECEIPT_BUT_SUBSCRIPTION_EXPIRED:

                if ( isset($purchaseInfo['latest_receipt_info']) ) {

                    $data = $purchaseInfo;

                } else {

                    $error = 'The order is not an auto-renewable subscription!';

                }

                break;
        }

        if ( empty($data) )
        {
            $this->logger->addEntry($error, 'ituns_purchase_subscription_info');
            $this->logger->writeLog();
        }

        return $data;
    }

    public function validateReceipt( $receipt, $transactionId )
    {
        $result = $this->getPurchaseInfo( $receipt );

        if ( isset($result['status']) && $result['status'] == self::RECEIPT_VALID )
        {
            foreach ($result['receipt']['in_app'] as $value)
            {
                if ($value['transaction_id'] == $transactionId)
                {
                    return $value;
                }
            }
        }

        return self::FAILURE;
    }

    public function activePurchasesSubscriptions( $receipt, $transactionId, $productId )
    {
        $purchaseInfo = $this->getPurchaseSubscriptionInfo($receipt);

        if ( empty($purchaseInfo) )
        {
            return self::FAILURE;
        }

        if ( $purchaseInfo['status'] && $purchaseInfo['status'] == self::VALID_RECEIPT_BUT_SUBSCRIPTION_EXPIRED )
        {
            return self::CANCEL;
        }

        $latestExpirationIntervalSince = 0;
        $dataPurchase = [];

        foreach ( $purchaseInfo['latest_receipt_info'] as $latestReceiptInfo )
        {
            if ( ($latestReceiptInfo['transaction_id'] != $transactionId &&
                    $latestReceiptInfo['original_transaction_id'] != $transactionId) &&
                $latestReceiptInfo['productId'] != $productId )
            {
                continue;
            }

            $expirationIntervalSince = intval($latestReceiptInfo['expires_date_ms'] / 1000);

            if ( $expirationIntervalSince > $latestExpirationIntervalSince )
            {
                $latestExpirationIntervalSince = $expirationIntervalSince;
                $dataPurchase = $latestReceiptInfo;
            }
        }

        if ( ($latestExpirationIntervalSince > time()) )
        {
            return $dataPurchase;
        }

        $this->logger->addEntry(print_r($purchaseInfo, true), 'ituns_purchase_subscription_info');
        $this->logger->writeLog();

        return self::FAILURE;
    }
}