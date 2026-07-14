<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'discount_value')) {
                $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            }
            if (! Schema::hasColumn('products', 'discount_start_at')) {
                $table->timestamp('discount_start_at')->nullable()->after('discount_value');
            }
            if (! Schema::hasColumn('products', 'discount_end_at')) {
                $table->timestamp('discount_end_at')->nullable()->after('discount_start_at');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 18, 2)->default(0)->after('cost_price');
            }
        });

        if (Schema::hasColumn('products', 'discount')) {
            DB::table('products')
                ->where('discount', '>', 0)
                ->update([
                    'discount_type' => 'percent',
                    'discount_value' => DB::raw('discount'),
                ]);
        }

        if (Schema::hasColumn('product_variants', 'sale_price')) {
            DB::statement('
                UPDATE product_variants
                SET price = CASE
                    WHEN EXISTS (SELECT 1 FROM products WHERE products.id = product_variants.product_id AND products.price IS NOT NULL AND products.price > 0)
                        THEN COALESCE((SELECT products.price FROM products WHERE products.id = product_variants.product_id), 0)
                    WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price
                    ELSE price
                END
            ');
        } elseif (Schema::hasColumn('products', 'price')) {
            DB::statement('
                UPDATE product_variants
                SET price = COALESCE((SELECT products.price FROM products WHERE products.id = product_variants.product_id), price)
                WHERE price = 0
            ');
        }

        if (Schema::hasColumn('products', 'cost_price')) {
            DB::statement('
                UPDATE product_variants
                SET cost_price = COALESCE(NULLIF(cost_price, 0), (SELECT products.cost_price FROM products WHERE products.id = product_variants.product_id), 0)
            ');
        }

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            if (Schema::hasColumn('products', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 15, 2)->default(0)->after('description');
            }
            if (! Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('products', 'discount')) {
                $table->unsignedTinyInteger('discount')->default(0)->after('cost_price');
            }
        });

        if (Schema::hasColumn('product_variants', 'price')) {
            DB::statement('
                UPDATE products
                SET price = COALESCE((SELECT MIN(product_variants.price) FROM product_variants WHERE product_variants.product_id = products.id), 0),
                    cost_price = COALESCE((SELECT MIN(product_variants.cost_price) FROM product_variants WHERE product_variants.product_id = products.id), 0),
                    discount = CASE WHEN discount_type = "percent" THEN CAST(discount_value AS UNSIGNED) ELSE 0 END
            ');
        }

        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'sale_price')) {
                $table->decimal('sale_price', 18, 2)->default(0)->after('cost_price');
            }
        });

        if (Schema::hasColumn('product_variants', 'price')) {
            DB::statement('UPDATE product_variants SET sale_price = price');
        }

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
            if (Schema::hasColumn('products', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
            if (Schema::hasColumn('products', 'discount_start_at')) {
                $table->dropColumn('discount_start_at');
            }
            if (Schema::hasColumn('products', 'discount_end_at')) {
                $table->dropColumn('discount_end_at');
            }
        });
    }
};
