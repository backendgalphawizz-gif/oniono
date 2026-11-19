<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('bank_name');
            $table->boolean('gst_enabled')->default(false)->after('gst_number');
            $table->decimal('default_gst_percentage', 5, 2)->nullable()->after('gst_enabled');
        });

        Schema::table('food_items', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false)->after('price');
            $table->decimal('gst_percentage', 5, 2)->nullable()->after('is_gst_applicable');
        });

        Schema::create('delivery_partner_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_partner_commission_percentage_applied', 5, 2)->nullable()->after('restaurant_earning_amount');
            $table->decimal('delivery_partner_commission_amount', 12, 2)->nullable()->after('delivery_partner_commission_percentage_applied');
            $table->decimal('delivery_partner_payout_amount', 12, 2)->nullable()->after('delivery_partner_commission_amount');
            $table->decimal('food_gst_amount', 12, 2)->default(0)->after('tax_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false)->after('total_price');
            $table->decimal('gst_percentage', 5, 2)->nullable()->after('is_gst_applicable');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_gst_applicable', 'gst_percentage', 'gst_amount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_partner_commission_percentage_applied',
                'delivery_partner_commission_amount',
                'delivery_partner_payout_amount',
                'food_gst_amount',
            ]);
        });

        Schema::dropIfExists('delivery_partner_commissions');

        Schema::table('food_items', function (Blueprint $table) {
            $table->dropColumn(['is_gst_applicable', 'gst_percentage']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['gst_number', 'gst_enabled', 'default_gst_percentage']);
        });
    }
};
