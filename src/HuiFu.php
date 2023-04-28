<?php

namespace Aoeng\Laravel\Huifu;

use Exception;
use Illuminate\Support\Facades\Http;

class HuiFu
{
    private $test = true;
    private $host = 'https://hfpay.cloudpnr.com/api/';
    private $signHost;
    private $version = '10';
    private $merId;
    private $pfxPassword;
    private $pfxFilePath;
    private $trustedCACertFilePath;
    private $bgRetUrl;


    public function __construct($config = null)
    {
        $this->test                  = $config['test'] ?? true;
        $this->host                  = $this->test ? "https://hfpay.testpnr.com/api/" : "https://hfpay.cloudpnr.com/api/";
        $this->signHost              = $config['sign_host'] ?? 'http://localhost:8080/hfpcfca/cfca/';
        $this->merId                 = $config['mer_cust_id'] ?? null;
        $this->pfxPassword           = $config['pfx_password'] ?? null;
        $this->pfxFilePath           = $config['pfx_file_path'] ?? null;
        $this->trustedCACertFilePath = $config['cert_file_path'] ?? null;
        $this->bgRetUrl              = $config['bg_ret_url'] ?? null;

    }

    /**
     *  钱包开户
     *
     * @param $order_date
     * @param $order_id
     * @param $user_name
     * @param $id_card
     * @param $user_id
     * @param $ret_url
     * @param int $market_type
     * @param string $acct_usage_type
     * @param int $id_card_type
     * @return array
     * @throws Exception
     */
    public function walletOpen($order_date, $order_id, $user_name, $id_card, $user_id, $ret_url, int $market_type = 2, string $acct_usage_type = 'wallet', int $id_card_type = 10): array
    {
        return $this->requestData('hfpwallet/w00003', array_merge(compact(
            'order_date',
            'order_id',
            'user_name',
            'id_card',
            'user_id',
            'market_type',
            'acct_usage_type',
            'id_card_type',
            'ret_url'
        ),
            ['bg_ret_url' => $this->bgRetUrl]));
    }

    /**
     *
     * @throws Exception
     */
    public function walletManage($order_date, $order_id, $user_id, $ret_url, $extension = ''): array
    {
        return $this->requestData('hfpwallet/w00004', compact(
            'order_date',
            'order_id',
            'user_id',
            'ret_url',
            'extension'
        ));
    }

    /**
     * @throws Exception
     */
    public function walletSearch($order_date, $order_id, $trans_type = 8): array
    {
        return $this->requestData('alse/qry009', compact(
            'order_date',
            'order_id',
            'trans_type'
        ));
    }

    /**
     * @throws Exception
     */
    public function walletStatus($user_id): array
    {
        return $this->requestData('alse/qry016', compact('user_id'));
    }

    /**
     * @throws Exception
     */
    public function walletBalance($user_cust_id, $acct_id, $is_query_guarantee = 0): array
    {
        return $this->requestData('alse/qry001', array_filter(compact('user_cust_id', 'acct_id', 'is_query_guarantee')));
    }

    /**
     * @throws Exception
     */
    public function orderCreate($order_date, $order_id, $user_cust_id, $trans_amt, $goods_desc, $object_info, $ret_url,
                                $dev_info_json, $div_details = null, $div_type = 0, $order_expire_time = null, $extension = ''): array
    {
        is_array($object_info) && $object_info = json_encode($object_info);
        is_array($dev_info_json) && $dev_info_json = json_encode($dev_info_json);
        is_array($div_details) && $div_details = json_encode($div_details);

        return $this->requestData('hfpwallet/pay033', array_filter(array_merge(compact(
            'order_date', 'order_id', 'user_cust_id', 'trans_amt', 'goods_desc', 'object_info', 'ret_url',
            'dev_info_json', 'div_details', 'div_type', 'order_expire_time', 'extension'
        ), ['bg_ret_url' => $this->bgRetUrl])));
    }

    /**
     * @throws Exception
     */
    public function orderClose($order_date, $order_id): array
    {
        return $this->requestData('hfpwallet/pay034', array_filter(compact('order_date', 'order_id')));
    }

    /**
     * @throws Exception
     */
    public function orderSearch($order_date, $order_id, $trans_type = 36): array
    {
        return $this->requestData('alse/qry008', compact(
            'order_date',
            'order_id',
            'trans_type'
        ));
    }

    /**
     * 延时分账
     * @throws Exception
     */
    public function orderBill($order_date, $order_id, $org_order_id, $org_order_date, $trans_amt, $div_details, $share_fee_mode = 0, $org_trans_type = 12, $dev_info_json = null): array
    {
        is_array($dev_info_json) && $dev_info_json = json_encode($dev_info_json);
        is_array($div_details) && $div_details = json_encode($div_details);

        return $this->requestData('hfpay/pay006', array_filter(compact(
            'order_date', 'order_id', 'org_order_id', 'org_order_date', 'trans_amt',
            'div_details', 'share_fee_mode', 'org_trans_type', 'dev_info_json'
        )));
    }

    /**
     * 退款
     * @throws Exception
     */
    public function orderRefund($order_date, $order_id, $org_order_id, $org_order_date, $trans_amt, $div_details, $dev_info_json, $trans_type = 30): array
    {
        is_array($dev_info_json) && $dev_info_json = json_encode($dev_info_json);
        is_array($div_details) && $div_details = json_encode($div_details);

        return $this->requestData('hfpay/reb001', array_filter(compact(
            'order_date', 'order_id', 'org_order_id', 'org_order_date', 'trans_amt',
            'div_details', 'trans_type', 'dev_info_json'
        )));
    }

    /**
     * 加签方法
     *
     * @param $strSignSourceData   string 加签原串
     * @return string  base64 encode 加签串
     * @throws Exception
     */
    private function signature(string $strSignSourceData): string
    {
        try {
            //调用加签方法
            $response = Http::asForm()->post($this->signHost . 'makeSign', [
                'data'   => json_encode([
                    'pfx_file_name' => $this->pfxFilePath,
                    'pfx_file_pwd'  => $this->pfxPassword,
                ]),
                'params' => $strSignSourceData
            ]);

            $result = $response->json();
            info('签名:', $result);

            //加签方法异常判断及记录
            if (!isset($result['resp_code']) || $result['resp_code'] != 'C00000') {
                throw new Exception("加签失败:" . $result['resp_desc'] ?? '');
            }

        } catch (Exception $e) {
            info("HUIFU:" . $e->getMessage());
            throw $e;
        }

        return $result['check_value'];
    }

    /**
     *  验证签名数据
     *
     * @param $signature
     * @return array
     * @throws Exception
     */
    public function decodeSignature($signature)
    {
        try {
            $response = Http::asForm()->post($this->signHost . 'verifySign', [
                'params' => json_encode([
                    'cert_file'   => $this->trustedCACertFilePath,
                    'check_value' => $signature,
                ]),
            ]);

            $result = $response->json();

            if (!isset($result['resp_code']) || $result['resp_code'] != 'C00000') {
                throw new Exception("验签失败:" . $result['resp_desc'] ?? '');
            }

        } catch (Exception $e) {
            info("HUIFU:" . $e->getMessage());
            throw $e;
        }

        return json_decode($result['params'], true);
    }

    /**
     * 请求接口返回数据
     * @param string $path
     * @param array $param
     * @return array
     * @throws Exception
     */
    private function requestData(string $path, array $param): array
    {
        try {
            $param = array_merge([
                'version'     => $this->version,
                'mer_cust_id' => $this->merId,
            ], $param);

            $signature = $this->signature(json_encode($param));

            $response = Http::asForm()->post($this->host . $path, [
                'version'     => $param['version'],
                'mer_cust_id' => $param['mer_cust_id'],
                'check_value' => $signature,
            ]);

            $result = $this->decodeSignature($response->json('check_value'));

            if (!isset($result['resp_code']) || $result['resp_code'] != 'C00000') {
                throw new Exception("调用失败:" . $result['resp_desc'] ?? '');
            }
            return $result;
        } catch (Exception $e) {
            info('HUIFU:' . $e->getMessage(), $result ?? []);
            throw $e;
        }
    }


}