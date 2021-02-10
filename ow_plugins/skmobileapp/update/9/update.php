<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$pluginKey = 'skmobileapp';

$langService = Updater::getLanguageService();

$langService->deleteLangKey($pluginKey, 'change');
$langService->deleteLangKey($pluginKey, 'delete');
$langService->deleteLangKey($pluginKey, 'upload');
$langService->deleteLangKey($pluginKey, 'abort');
$langService->deleteLangKey($pluginKey, 'simple_upload_file_limit');
$langService->deleteLangKey($pluginKey, 'hidden');
$langService->deleteLangKey($pluginKey, 'remove');
$langService->deleteLangKey($pluginKey, 'view_all_photos_page_header');
$langService->deleteLangKey($pluginKey, 'miles');
$langService->deleteLangKey($pluginKey, 'km');
$langService->deleteLangKey($pluginKey, 'to');
$langService->deleteLangKey($pluginKey, 'from');
$langService->deleteLangKey($pluginKey, 'no');
$langService->deleteLangKey($pluginKey, 'take_avatar');
$langService->deleteLangKey($pluginKey, 'verify_email_page_header');
$langService->deleteLangKey($pluginKey, 'privacy_policy_section');
$langService->deleteLangKey($pluginKey, 'couldnt_complete_request');
$langService->deleteLangKey($pluginKey, 'cancel');
$langService->deleteLangKey($pluginKey, 'choose_photo_from_library');
$langService->deleteLangKey($pluginKey, 'upload_avatar');
$langService->deleteLangKey($pluginKey, 'facebook_connect');
$langService->deleteLangKey($pluginKey, 'per');
$langService->deleteLangKey($pluginKey, 'inapps_product_prefix');
$langService->deleteLangKey($pluginKey, 'wrong_pem_file');
$langService->deleteLangKey($pluginKey, 'app_recurring_information_description');
$langService->deleteLangKey($pluginKey, 'card_details');
$langService->deleteLangKey($pluginKey, 'card_number');
$langService->deleteLangKey($pluginKey, 'card_number_placeholder');
$langService->deleteLangKey($pluginKey, 'cvc');
$langService->deleteLangKey($pluginKey, 'cvc_placeholder');
$langService->deleteLangKey($pluginKey, 'expiration_date');
$langService->deleteLangKey($pluginKey, 'expiration_date_placeholder');
$langService->deleteLangKey($pluginKey, 'card_name');
$langService->deleteLangKey($pluginKey, 'card_name_placeholder');
$langService->deleteLangKey($pluginKey, 'country');
$langService->deleteLangKey($pluginKey, 'country_placeholder');
$langService->deleteLangKey($pluginKey, 'state');
$langService->deleteLangKey($pluginKey, 'state_placeholder');
$langService->deleteLangKey($pluginKey, 'address_line');
$langService->deleteLangKey($pluginKey, 'address_line_placeholder');
$langService->deleteLangKey($pluginKey, 'zip_code');
$langService->deleteLangKey($pluginKey, 'zip_code_placeholder');
$langService->deleteLangKey($pluginKey, 'payment_page_header');
$langService->deleteLangKey($pluginKey, 'payment_fail_message');
$langService->deleteLangKey($pluginKey, 'ads_enabled_desc');
$langService->deleteLangKey($pluginKey, 'inapps_ios_test_mode_desc');
$langService->deleteLangKey($pluginKey, 'pn_enabled_desc');

// import languages
$langService->importPrefixFromDir(__DIR__ . DS . 'langs', true);
