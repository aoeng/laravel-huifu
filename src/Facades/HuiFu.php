<?php

namespace Aoeng\Laravel\Huifu\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static walletOpen($order_date, $order_id, $user_name, $id_card, $user_id,$ret_url, $market_type = 2, $acct_usage_type = 'wallet', $id_card_type = 10)
 * @method static walletManage($order_date, $order_id, $user_id, $ret_url, $extension = '')
 * @method static walletSearch($order_date, $order_id, $trans_type = 8)
 * @method static walletStatus($user_id)
 * @method static walletBalance($user_cust_id, $acct_id, $is_query_guarantee = 0)
 * @method static orderCreate($order_date, $order_id, $user_cust_id, $trans_amt, $goods_desc, $object_info, $ret_url, $dev_info_json, $div_details = null, $div_type = 0, $order_expire_time = null, $extension = '')
 * @method static orderClose($order_date, $order_id)
 * @method static orderSearch($order_date, $order_id, $trans_type = 36)
 * @method static orderBill($order_date, $order_id, $org_order_id, $org_order_date, $trans_amt, $div_detail, $share_fee_mode = 0, $org_trans_type = 12, $dev_info_json = null)
 * @method static decodeSignature($signature)
 *
 */
class HuiFu extends LaravelFacade
{
    protected static function getFacadeAccessor(): string
    {
        return 'huifu';
    }
}