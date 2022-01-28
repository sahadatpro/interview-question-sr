<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $products = Product::query();
        $variants = ProductVariant::with(['variantName'])->get()->groupBy('variantName.title');

        if ($request->title) {
            $products->searchByTitle($request->title);
        }

        if ($request->date) {
            $products->where('created_at', '>', Carbon::parse($request->date)->toDateTimeString());
        }

        if ($request->variant) {
            $products->with(['productVariants' => function ($query) use ($request) {
                $query->where('variant', $request->variant);
            }]);
        }

        $products = $products->with(['variantPrices'])->paginate(5);

        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => 'required|string|max:255',
            'sku'   => 'required|string|unique:products',
            'description' => 'nullable|string'
        ]);

        $data = [
            'product' => $attributes
        ];
        /* Product Variant */
        if (count($request->product_variant) > 0) {
            foreach ($request->product_variant as $option) {

                foreach ($option['tags'] as $variant) {
                    $data['productVariant'][] = [
                        'variant_id' => $option['option'],
                        'variant' => $variant
                    ];
                }
            }
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data['product']);
            foreach ($data['productVariant'] as $pVariant) {
                $pVariant['product_id'] = $product->id;
                ProductVariant::create($pVariant);
            }

            /* Product Prices */

            if (count($request->product_variant_prices) > 0) {
                foreach ($request->product_variant_prices as $variantPrices) {
                    $titleToVariant = explode('/', $variantPrices['title']);
                    $titleToVariant =  array_filter($titleToVariant);
                    $numVariant = [];

                    foreach ($titleToVariant as $key => $v) {
                        $v = trim($v);

                        $productVariant = ProductVariant::query()
                            ->where('product_id', $product->id)
                            ->where('variant', $v)
                            ->first();
                        if ($key == 0) {
                            $numVariant['product_variant_one']  = $productVariant->id;
                        }
                        if ($key == 1) {
                            $numVariant['product_variant_two'] = $productVariant->id;
                        }
                        if ($key == 2) {
                            $numVariant['product_variant_three'] = $productVariant->id;
                        }
                    }
                    // $data['productVariantPrices'][]
                    $vPrices = [
                        'product_id' => $product->id,
                        'stock' => $variantPrices['stock'],
                        'price' => $variantPrices['price']
                    ];
                    $prices = array_merge($vPrices, $numVariant);

                    ProductVariantPrice::create($prices);
                }
            }
        });

        return response(['message' => 'Product data has been created']);
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function upload(Request $request)
    {
        if ($request->file('file')) {
            $path = $request->file('file')->store('uploads');
            if ($path) {
                return response()->json(['message' => 'File uploaded.'], 200);
            }
        } else {
            return response()->json(['message' => 'Error Uploading File..'], 503);
        }
    }
}
