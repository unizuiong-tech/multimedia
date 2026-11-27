<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('基础订单表');
            }

            $table->bigIncrements('id')->comment('订单ID');
            $table->string('order_no', 64)->comment('业务订单号');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('order_type', 20)->comment('订单类型: recharge充值 / unlock_chapter单章解锁 / vip会员');
            $table->unsignedBigInteger('target_id')->default(0)->comment('目标ID(如 chapter_id 或套餐ID)');
            $table->unsignedInteger('amount')->default(0)->comment('金额/书币数量');
            $table->tinyInteger('pay_status')->default(0)->comment('支付状态: 0待支付 1已支付 2已取消 3已退款');
            $table->dateTime('paid_at')->nullable()->comment('支付时间');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间');
            $table->unique('order_no', 'uk_order_no');
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            $table->index('deleted_at', 'orders_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
