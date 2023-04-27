<?php

return [
    'test'      => true,

    //微服务地址
    'sign_host' => env('HUIFU_SIGN_HOST', 'http://localhost:8080/hfpcfca/cfca/'),

    'mer_cust_id' => env('HUIFU_MER_CUST_ID'),

    'pfx_file_path' => storage_path('huifu/' . env('HUIFU_PFX_FILE_PATH', 'HF0547.pfx')),

    'pfx_password' => env('HUIFU_PFX_PASSWORD'),

    'cert_file_path' => storage_path('huifu/' . env('HUIFU_CERT_FILE_PATH', 'CFCA_ACS_TEST_OCA31.cer')),

    'bg_ret_url' => env('APP_URL') . '/callback/huifu',
];