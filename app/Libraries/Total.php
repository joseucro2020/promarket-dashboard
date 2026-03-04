<?php

namespace App\Libraries;

class Total
{
    public static function get($pedido)
    {
        $total = 0;

        foreach (data_get($pedido, 'details', []) as $item) {
            $total += (float) data_get($item, 'quantity', 0) * CalcPrice::get(
                data_get($item, 'price', 0),
                data_get($item, 'coin', '1'),
                data_get($pedido, 'exchange.change', 1)
            );
        }

        return $total;
    }

    public static function getByCurrency($pedido, $withoutCoupon = null)
    {
        $total = 0;

        foreach (data_get($pedido, 'details', []) as $item) {
            $price = (float) data_get($item, 'quantity', 0) * CalcPrice::getByCurrency(
                data_get($item, 'price', 0),
                data_get($item, 'coin', '1'),
                data_get($pedido, 'exchange.change', 1),
                data_get($pedido, 'currency', '1')
            );

            if (data_get($item, 'coupon_percentage') && !$withoutCoupon) {
                $price = $price - (($price * (float) data_get($item, 'coupon_percentage', 0)) / 100);
            }

            $total += $price;
        }

        return $total;
    }

    public static function getByCurrencyCompany($pedido, $withoutCoupon = null)
    {
        $total = 0;

        foreach (data_get($pedido, 'details', []) as $item) {
            if (data_get($item, 'product') && (int) data_get($item, 'product.company_id', 0) > 0) {
                $price = (float) data_get($item, 'quantity', 0) * CalcPrice::getByCurrency(
                    data_get($item, 'product_amount.cost', 0),
                    data_get($item, 'coin', '1'),
                    data_get($pedido, 'exchange.change', 1),
                    data_get($pedido, 'currency', '1')
                );

                $total += $price;
            }
        }

        return $total;
    }
}
