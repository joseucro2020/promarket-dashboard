<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
//use Nicolaslopezj\Searchable\SearchableTrait;

class Product extends Model
{
    // use SearchableTrait;

    const STR_LIMIT = 60;

    const TYPE_SIMPLE = 0;
    const TYPE_VARIABLE = 1;

    const STATUS_ACTIVE = '1';
    const STATUS_INACTIVE = '0';
    const STATUS_DELETED = '2';

    protected $table = "products";

    protected $fillable = [
        'name',
        'name_english',
        'description',
        'description_english',
        'coin',
        'variable',
        'price_1',
        'price_2',
        'category_id',
        'subcategory_id',
        'collection_id',
        'design_id',
        'retail',
        'wholesale',
        'status',
        'subsubcategory_id',
        'slug',
        'company_id',
        'taxe_id'
    ];

    protected $with = [
        'secondary_subcategories',
        'secondary_categories',
        'tags',
        'supplier'
    ];

    protected $appends = [
        'image_url',
        'es_name',
        'en_name',
        'es_date',
        'es_update',
        'type_variable',
        'offer',
        'discount',
        'taxe',
        'offers_active',      // <-- Agregado
        'discounts_active',   // <-- Agregado
    ];

    const UNITS = [
        1 => 'Gr',
        2 => 'Kg',
        3 => 'Ml',
        4 => 'L',
        5 => 'Cm'
    ];

    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'products.name' => 10,
            'products.description' => 7,
        ],
    ];

    public function srtLenght($name)
    {
        return strlen($name);
    }

    public function getEsNameAttribute()
    {
        return $this->srtLenght($this->name) >= static::STR_LIMIT ? mb_substr($this->name, 0, static::STR_LIMIT) . '...' : $this->name;
    }

    public function getEnNameAttribute()
    {
        return $this->srtLenght($this->name_english) >= static::STR_LIMIT ? mb_substr($this->name_english, 0, static::STR_LIMIT) . '...' : $this->name_english;
    }

    public function getEsDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getEsUpdateAttribute()
    {
        return Carbon::parse($this->updated_at)->format('d-m-Y');
    }

    public function getTypevariableAttribute()
    {
        switch ($this->variable) {
            case static::TYPE_SIMPLE:
                return 'Simple';
            case static::TYPE_VARIABLE:
                return 'Variable';
        }
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategories()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function subsubcategories()
    {
        return $this->belongsTo(Subsubcategories::class, 'subsubcategory_id');
    }

    public function designs()
    {
        return $this->belongsTo(Design::class, 'design_id');
    }

    public function collections()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->images()->count() > 0) {
            $file = $this->images[0]->file;

            if (substr($file, 0, 4) === "http") {
                return $file;
            }

            return asset('img/products/' . $file);
        } else {
            return null;
        }
    }

    public function cateSpecial()
    {
        return $this->belongsToMany(SpecialCategory::class);
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class);
    }

    public function taxe()
    {
        return $this->belongsTo(Taxe::class)->where('status', Taxe::STATUS_ACTIVE);
    }

    public function offersActive()
    {
        return $this->offers()->where('status', Offer::ACTIVE);
    }

    public function getOfferAttribute()
    {
        if ($this->offers->count() > 0) {
            return $this->offersActive()->whereDate('start', '<=', date('Y-m-d'))->first();
        }
        return null;
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }

    public function discountsActive()
    {
        return $this->discounts()->where('status', Discount::ACTIVE);
    }

    public function getDiscountAttribute()
    {
        if ($this->discounts->count() > 0) {
            return $this->discountsActive()->whereDate('start', '<=', date('Y-m-d'))->first();
        }
        return null;
    }

    public function getTaxeAttribute()
    {
        return $this->taxe()->first();
    }

    public function secondary_subcategories()
    {
        return $this->belongsToMany('App\Models\Subcategory', 'product_subcategories', 'product_id', 'subcategory_id')
            ->whereNull('product_subcategories.deleted_at');
    }

    public function secondary_categories()
    {
        return $this->belongsToMany('App\Models\Category', 'product_categories', 'product_id', 'category_id')
            ->whereNull('product_categories.deleted_at');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag', 'product_tags', 'product_id', 'tag_id')
            ->whereNull('product_tags.deleted_at');
    }

    public function supplier()
    {
        return $this->belongsToMany('App\Models\Supplier', 'product_proveedor', 'products_id', 'proveedor_id')
            ->whereNull('product_proveedor.deleted_at');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function buildSearchText()
    {
        $parts = [
            $this->name,
            $this->name_english,
            $this->description,
            $this->description_english,
            optional($this->categories)->name,
            optional($this->subcategories)->name,
            optional($this->subsubcategories)->name,
            optional($this->collections)->name,
            optional($this->designs)->name,
            $this->tags->pluck('name')->implode(' '),
            $this->supplier->pluck('name')->implode(' '),
            $this->coin,
            $this->price_1,
            $this->price_2,
            $this->retail,
            $this->wholesale,
            $this->slug,
            $this->status,
        ];

        // Ofertas activas
        $offer = $this->offersActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->first();
        if ($offer && $offer->percentage) {
            $parts[] = 'oferta ' . $offer->percentage . '%';
            $parts[] = 'rebaja promocion especial';
        }

        // Descuentos activos
        $discount = $this->discountsActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->first();
        if ($discount && $discount->percentage) {
            $parts[] = 'descuento ' . $discount->percentage . '%';
            $parts[] = 'rebaja promocion especial';
        }

        return implode(' ', array_filter($parts));
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            $product->search_text = $product->buildSearchText();
        });
    }

    public function getOffersActiveAttribute()
    {
        return $this->offersActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->get();
    }

    public function getDiscountsActiveAttribute()
    {
        return $this->discountsActive()
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))
            ->get();
    }
}
