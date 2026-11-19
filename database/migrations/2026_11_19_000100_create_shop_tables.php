<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('phone')->nullable()->after('email');
            $table->boolean('notify_upgrades')->default(true);
            $table->boolean('notify_deals')->default(false);
        });

        // Car models customers pick (BMW řady 3 E46 …)
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('name');            // "Řada 3"
            $table->string('code')->nullable(); // "E46"
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('body_types')->nullable(); // comma separated
            $table->text('description')->nullable();  // SEO text of the model landing page
            $table->boolean('is_popular')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Parts of the car shown as hotspots on the blueprint
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('bi-circle');
            $table->unsignedTinyInteger('pos_x')->default(50); // % from left
            $table->unsignedTinyInteger('pos_y')->default(50); // % from top
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('bi-box');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');                 // CZK incl. VAT
            $table->unsignedInteger('old_price')->nullable(); // crossed price
            $table->integer('stock')->default(0);
            $table->unsignedTinyInteger('restock_days')->default(5);
            $table->unsignedTinyInteger('difficulty')->default(1); // 1–3
            $table->unsignedSmallInteger('install_minutes')->default(15);
            $table->unsignedSmallInteger('install_priority')->default(50); // order in plan (lower first)
            $table->boolean('needs_service')->default(false);
            $table->string('goal')->nullable(); // nabijeni | hudba | bezpecnost | pohodli
            $table->string('manufacturer')->nullable();
            $table->boolean('own_production')->default(false);
            $table->boolean('is_universal')->default(false);
            $table->string('location_in_car')->nullable();
            $table->string('install_tip')->nullable();
            $table->unsignedTinyInteger('test_score')->nullable();
            $table->string('test_quote')->nullable();
            $table->text('pros')->nullable();   // one per line
            $table->text('cons')->nullable();   // one per line
            $table->text('specs')->nullable();  // "Key: value" per line
            $table->string('variant_label')->nullable();
            $table->string('variant_options')->nullable(); // comma separated
            $table->string('video_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();
        });

        Schema::create('car_model_product', function (Blueprint $table) {
            $table->foreignId('car_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['car_model_id', 'product_id']);
        });

        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // buyable tool
            $table->timestamps();
        });

        Schema::create('product_tool', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'tool_id']);
        });

        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bundle_product', function (Blueprint $table) {
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['bundle_id', 'product_id']);
        });

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('intro')->nullable();
            $table->json('steps'); // [{title, body}]
            $table->unsignedTinyInteger('video_minutes')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('guide_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step')->default(1);
            $table->boolean('done')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'guide_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->string('car_label')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->unsignedSmallInteger('install_minutes')->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        // User garage
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_model_id')->constrained()->cascadeOnDelete();
            $table->string('nickname')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('body_type')->nullable();
            $table->string('engine')->nullable();
            $table->string('radio')->nullable();
            $table->string('vin', 17)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('car_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->boolean('installed')->default(false);
            $table->timestamps();
            $table->unique(['car_id', 'product_id']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('street');
            $table->string('city');
            $table->string('zip', 10);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->default('bi-box-seam');
            $table->unsignedInteger('price')->default(0);
            $table->boolean('needs_address')->default(false);
            $table->boolean('needs_pickup_point')->default(false);
            $table->boolean('free_over_threshold')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // card | transfer | cod
            $table->string('name');
            $table->string('icon')->default('bi-credit-card');
            $table->unsignedInteger('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('percent'); // percent | fixed
            $table->unsignedInteger('value');
            $table->unsignedInteger('min_total')->default(0);
            $table->date('valid_until')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('token', 40)->unique(); // guest access to the order
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('car_label')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('pickup_point')->nullable();
            $table->string('shipping_name');
            $table->unsignedInteger('shipping_price');
            $table->string('payment_code');
            $table->string('payment_name');
            $table->unsignedInteger('payment_price');
            $table->string('coupon_code')->nullable();
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('total');
            $table->string('status')->default('new'); // new | packing | shipped | delivered | cancelled
            $table->boolean('is_paid')->default(false);
            $table->string('tracking_number')->nullable();
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bundle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('variant')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('saved_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('car_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('token', 40)->unique(); // share link
            $table->json('items');
            $table->timestamps();
        });

        Schema::create('showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('author_name')->nullable();
            $table->string('before_image')->nullable();
            $table->string('after_image')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('dotaz'); // dotaz | podpora | navrh
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('attachment')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('group')->default('nakup'); // nakup | poradna
            $table->longText('body');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'pages', 'messages', 'showcases', 'saved_plans', 'order_items', 'orders', 'coupons',
            'payment_methods', 'shipping_methods', 'addresses', 'car_upgrades', 'cars', 'reviews', 'guide_progress',
            'guides', 'bundle_product', 'bundles', 'product_tool', 'tools', 'car_model_product', 'products',
            'categories', 'zones', 'car_models'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_admin', 'phone', 'notify_upgrades', 'notify_deals']));
    }
};
