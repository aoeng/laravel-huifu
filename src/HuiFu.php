<?php

namespace Aoeng\Laravel\Huifu;

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
        $this->pfxPassword           = $config['pfxPassword'] ?? null;
        $this->pfxFilePath           = $config['pfxFilePath'] ?? null;
        $this->trustedCACertFilePath = $config['trustedCACertFilePath'] ?? null;
        $this->bgRetUrl              = $config['bg_ret_url'] ?? null;

    }


    /**
     *  钱包开户
     *
     * @return array
     * @throws \Exception
     */
    public function walletOpen($order_date, $order_id, $user_name, $id_card, $user_id, $market_type = 2, $acct_usage_type = 'wallet', $id_card_type = 10)
    {
        return $this->requestData('hfpwallet/w00003', array_merge(compact(
            'order_date',
            'order_id',
            'user_name',
            'id_card',
            'user_id',
            'market_type',
            'acct_usage_type',
            'id_card_type'
        ),
            ['bg_ret_url' => $this->bgRetUrl]));
    }

    /**
     * 加签方法
     *
     * @param $strSignSourceData   string 加签原串
     * @return string  base64 encode 加签串
     * @throws \Exception
     */
    private function signature(string $strSignSourceData): string
    {
        try {
            //调用加签方法
            $response = Http::withHeaders(['headers' => ['Content-type' => 'application/x-www-form-urlencoded;charset=UTF-8']])
                ->post($this->signHost . 'makeSign', [
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
                throw new \Exception("加签失败:" . $result['resp_desc'] ?? '');
            }

        } catch (\Exception $e) {
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
     * @throws \Exception
     */
    private function decodeSignature($signature)
    {
        try {
            //调用加签方法
            $response = Http::withHeaders(['headers' => ['Content-type' => 'application/x-www-form-urlencoded;charset=UTF-8']])
                ->post($this->signHost . 'verifySign', [
                    'params' => json_encode([
                        'cert_file'   => $this->trustedCACertFilePath,
                        'check_value' => $signature,
                    ]),
                ]);

            $result = $response->json();

            //加签方法异常判断及记录
            if (!isset($result['resp_code']) || $result['resp_code'] != 'C00000') {
                throw new \Exception("验签失败:" . $result['resp_desc'] ?? '');
            }

        } catch (\Exception $e) {
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
     * @throws \Exception
     */
    private function requestData(string $path, array $param): array
    {
        try {
            $param = array_merge([
                'version'     => $this->version,
                'mer_cust_id' => $this->merId,
            ], $param);

            $signature = $this->signature(json_encode($param));

            $response = Http::withHeaders(['headers' => ['Content-type' => 'application/x-www-form-urlencoded;charset=UTF-8']])->post($this->host . $path, [
                'version'     => $param['version'],
                'mer_cust_id' => $param['mer_cust_id'],
                'check_value' => $signature,
            ]);

            $result = $this->decodeSignature($response->json('check_value'));

            if (!isset($result['resp_code']) || $result['resp_code'] != 'C00000') {
                throw new \Exception("调用失败:" . $result['resp_desc'] ?? '');
            }
            return $result;
        } catch (\Exception $e) {
            info('HUIFU:' . $e->getMessage());
            throw $e;
        }
    }


}