<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            // First drop any existing foreign key if it exists
            if (Schema::hasColumn('order_items', 'variation_id')) {
                $table->dropForeign(['variation_id']);
                $table->dropColumn('variation_id');
            }

            // Add the new column and foreign key
            $table->foreignId('variation_id')
                ->after('product_id')
                ->nullable()
                ->constrained('product_variations')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variation_id']);
            $table->dropColumn('variation_id');
        });
    }
};
