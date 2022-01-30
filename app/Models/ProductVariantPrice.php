<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantPrice extends Model
{
     protected $guarded = ['id'];
     /**
      * Get the product that owns the ProductVariantPrice
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function product(): BelongsTo
     {
          return $this->belongsTo(Product::class, 'product_id');
     }

     /**
      * Get the variantOne that owns the ProductVariantPrice
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function variantOne(): BelongsTo
     {
          return $this->belongsTo(ProductVariant::class, 'product_variant_one');
     }

     /**
      * Get the variantTwo that owns the ProductVariantPrice
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function variantTwo(): BelongsTo
     {
          return $this->belongsTo(ProductVariant::class, 'product_variant_two');
     }

     /**
      * Get the variantThree that owns the ProductVariantPrice
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function variantThree(): BelongsTo
     {
          return $this->belongsTo(ProductVariant::class, 'product_variant_three');
     }


     public function title()
     {
          $one = $this->variantOne->variant ?? '';
          $two = $this->variantTwo->variant ?? '';
          $three = $this->variantThree->variant ?? '';
          return $one . '/' . $two . '/' . $three;
     }
}
