<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

/** Product XML feed in Heureka.cz format – shows the e-shop can connect to price comparators. */
class FeedController extends Controller
{
    public function heureka()
    {
        $products = Product::active()->with('category.parent')->get();
        $x = new \XMLWriter();
        $x->openMemory(); $x->startDocument('1.0', 'UTF-8'); $x->setIndent(true);
        $x->startElement('SHOP');
        foreach ($products as $p) {
            $x->startElement('SHOPITEM');
            $x->writeElement('ITEM_ID', $p->sku);
            $x->writeElement('PRODUCTNAME', $p->name);
            $x->writeElement('DESCRIPTION', (string) $p->short_description);
            $x->writeElement('URL', route('product.show', $p->slug));
            if ($p->image_url) $x->writeElement('IMGURL', url($p->image_url));
            $x->writeElement('PRICE_VAT', (string) $p->price);
            if ($p->manufacturer) $x->writeElement('MANUFACTURER', $p->manufacturer);
            $x->writeElement('CATEGORYTEXT', 'Auto-moto | Autoelektronika | '.collect([$p->category?->parent?->name, $p->category?->name])->filter()->implode(' | '));
            $x->writeElement('DELIVERY_DATE', $p->in_stock ? '1' : (string) $p->restock_days);
            $x->endElement();
        }
        $x->endElement();
        return response($x->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
