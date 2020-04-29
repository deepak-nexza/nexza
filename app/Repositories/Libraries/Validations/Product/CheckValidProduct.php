<?php

namespace App\B2c\Repositories\Libraries\Validations\Product;

use Auth;
use App\B2c\Repositories\Models\Master\Product;
use App\B2c\Repositories\Models\Master\FinancePurpose;

class CheckValidProduct
{

    /**
     * Attribute being tested
     *
     * @var mixed
     */
    protected $attribute;

    /**
     * Value of the attribute
     *
     * @var mixed
     */
    protected $value;

    /**
     * Array of parameters passed to the value
     *
     * @var array
     */
    protected $parameters;

    /**
     * Validator instance
     *
     * @var mixed
     */
    protected $validator;

    /**
     * Matching column between two tables
     *
     * @var string
     */
    protected $match_column = 'type';

    /**
     *  Check if product is valid
     */
    public static function isValid($attribute, $value, $parameters, $validator)
    {
        $finance_info_type = false;
        $counter = explode('.', $attribute);
        $counter = $counter[1]; 
        if (isset($validator->getData()['finance_purpose_id'])) {
            $finance_info = FinancePurpose::find($validator->getData()['finance_purpose_id'][$counter]);
            $finance_info_type = $finance_info['type'];
        }
        $product_info_type = false;
        if (isset($value)) {
            $product_info = Product::find($value);
            $product_info_type = $product_info['type'];
        }

        if ($finance_info_type && $product_info_type) {
            return $finance_info_type == $product_info_type;
        }
    }
}
