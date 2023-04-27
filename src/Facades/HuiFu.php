<?php

namespace Aoeng\Laravel\Huifu\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static walletOpen($order_date, $order_id, $user_name, $id_card, $user_id, $market_type = 2, $acct_usage_type = 'wallet', $id_card_type = 10)
 */
class HuiFu extends LaravelFacade
{
    protected static function getFacadeAccessor(): string
    {
        return 'huifu';
    }
}