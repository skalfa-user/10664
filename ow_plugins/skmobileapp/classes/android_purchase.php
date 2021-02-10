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

use \Firebase\JWT\JWT;

class SKMOBILEAPP_CLASS_AndroidPurchase extends SKMOBILEAPP_CLASS_AbstractInAppPurchase
{
    const EXPIRATION_TIMEOUT = 600; // timeout in seconds

    const TOKEN_SCOPE   = 'https://www.googleapis.com/auth/androidpublisher';
    const TOKEN_AUD     = 'https://www.googleapis.com/oauth2/v4/token';

    const PURCHASE_SUBSCRIPTION_INFO_URL = 'https://www.googleapis.com/androidpublisher/v2/applications/:packageName/purchases/subscriptions/:subscriptionId/tokens/:purchaseToken?access_token=:accessToken';

    const KIND_SUBSCRIPTION = 'androidpublisher#subscriptionPurchase';

    // Payment State
    const PAYMENT_STATE_PENDING = 0;    //Payment pending
    const PAYMENT_STATE_RECEIVED = 1;   //Payment received
    const PAYMENT_STATE_TRIAL = 2;      //Free trial

    const CONFIG_AUTH_TOKEN                 = 'service_account_auth_token';
    const CONFIG_AUTH_TOKEN_EXPIRATION_TIME = 'service_account_auth_expiration_time';
    const CONFIG_PACKAGE_NAME               = 'inapps_apm_package_name';
    const CONFIG_CLIENT_EMAIL               = 'inapps_apm_android_client_email';
    const CONFIG_PUBLIC_KEY                 = 'inapps_apm_key';
    const CONFIG_PRIVATE_KEY                = 'inapps_apm_android_private_key';

    /**
     * @var OW_Log
     */
    protected $logger;
    protected $config;
    protected $packageName;
    protected $token = null;
    protected $clientEmail = '';
    protected $publicKey = '';
    protected $privateKey = '';

    /**
     * SKMOBILEAPP_CLASS_InAppPurchase constructor.
     */
    public function __construct()
    {
        $this->logger = OW::getLogger('skmobileapp');
        $this->config = OW::getConfig()->getValues('skmobileapp');

        $this->clientEmail  = $this->config[self::CONFIG_CLIENT_EMAIL];
        $this->publicKey    = $this->config[self::CONFIG_PUBLIC_KEY];
        $this->privateKey   = str_replace('\n', "\n", $this->config[self::CONFIG_PRIVATE_KEY]);
        $this->packageName  = $this->config[self::CONFIG_PACKAGE_NAME];
    }

    protected function prepareToken()
    {
        $this->token = $this->config[self::CONFIG_AUTH_TOKEN];
        $expirationTime = $this->config[self::CONFIG_AUTH_TOKEN_EXPIRATION_TIME];

        if ( empty($this->token) || $expirationTime < time() + self::EXPIRATION_TIMEOUT - 10 )
        {
            $time = time();

            $token = [
                'iss'   => $this->clientEmail,
                'scope' => self::TOKEN_SCOPE,
                'aud'   => self::TOKEN_AUD,
                'exp'   => $time + self::EXPIRATION_TIMEOUT,
                'iat'   => $time
            ];
            
            $jwt = JWT::encode($token, $this->privateKey, 'RS256');

            $clientParams = new UTIL_HttpClientParams();
            $clientParams->addParams([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            $result = UTIL_HttpClient::post(self::TOKEN_AUD, $clientParams);

            if ( $result && $result->getBody() )
            {
                $response = json_decode($result->getBody(), true);

                if ( !empty($response['access_token']) )
                {
                    $this->token = $response['access_token'];

                    OW::getConfig()->saveConfig('skmobileapp',
                        self::CONFIG_AUTH_TOKEN, $response['access_token']);

                    if ( (int) $response['expires_in'] > 10 )
                    {
                        OW::getConfig()->saveConfig('skmobileapp', self::CONFIG_AUTH_TOKEN_EXPIRATION_TIME,
                            ($time + self::EXPIRATION_TIMEOUT - 10) );

                        return;
                    }
                }

                $this->logger->addEntry('Can\'t generate access token', 'access_token');
                $this->logger->writeLog();
            }
            else
            {
                $this->logger->addEntry('Unable to get response', 'http_response_exception');
                $this->logger->writeLog();
            }
        }
    }

    public function validateReceipt($signature, $receipt)
    {
        $key = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($this->publicKey, 64, "\n") .
            '-----END PUBLIC KEY-----';
        $key = openssl_get_publickey($key);
        $signature = base64_decode($signature);
        $result = @openssl_verify($receipt, $signature, $key, OPENSSL_ALGO_SHA1);

        if ( 1 === $result )
        {
            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    public function getPurchaseSubscriptionInfo($productId, $purchaseToken)
    {
        $this->prepareToken();

        $url = str_replace([':packageName', ':subscriptionId', ':purchaseToken', ':accessToken'],
            [$this->packageName, $productId, $purchaseToken, $this->token], self::PURCHASE_SUBSCRIPTION_INFO_URL);

        $response = UTIL_HttpClient::get($url);

        if ( !$response || !$response->getBody() )
        {
            $this->logger->addEntry('Unable to get response', 'http_response_exception');
            $this->logger->writeLog();

            return [];
        }

        $purchaseInfo = json_decode($response->getBody(), true);

        if ( !$purchaseInfo || isset($purchaseInfo['error']) )
        {
            $error = 'Purchase subscription error info';

            if ( isset($purchaseInfo['error']['message']) )
            {
                $error = $purchaseInfo['error']['message'];
            }

            $this->logger->addEntry($error, 'android_purchase_subscription_info');
            $this->logger->writeLog();

            return [];
        }

        return $purchaseInfo;
    }

    public function activePurchasesSubscriptions( $productId, $purchaseToken )
    {
        $purchaseInfo = $this->getPurchaseSubscriptionInfo(mb_strtolower($productId), $purchaseToken);

        if ( empty($purchaseInfo) )
        {
            return self::FAILURE;
        }

        if ( isset($purchaseInfo['cancelReason']) && $purchaseInfo['kind'] == self::KIND_SUBSCRIPTION )
        {
            return self::CANCEL;
        }

        if ( isset($purchaseInfo['paymentState'])
            && $purchaseInfo['kind'] == self::KIND_SUBSCRIPTION
            && $purchaseInfo['paymentState'] == self::PAYMENT_STATE_RECEIVED
            && time() < $purchaseInfo['expiryTimeMillis'] / 1000 )
        {
            return $purchaseInfo;
        }

        $this->logger->addEntry(print_r($purchaseInfo, true), 'android_purchase_subscription_info');
        $this->logger->writeLog();

        return self::FAILURE;
    }
}