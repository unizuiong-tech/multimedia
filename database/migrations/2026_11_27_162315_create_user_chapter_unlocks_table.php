<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chapter_unlocks', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('用户单章/单集解锁记录表');
            }

            $table->bigIncrements('id')->comment('解锁记录ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('work_id')->comment('作品ID');
            $table->unsignedBigInteger('chapter_id')->comment('章节/单视频ID');
            $table->string('content_type', 16)->comment('内容类型: video / comic / novel / tv_series / short_drama');
            $table->string('unlock_source', 20)->default('coin')->comment('解锁来源: coin书币购买 / vip会员 / gift赠送 / promo活动');
            $table->unsignedInteger('paid_amount')->default(0)->comment('实际消耗书币/金额');
            $table->string('order_no', 64)->default('')->comment('关联订单号');
            $table->dateTime('created_at')->useCurrent()->comment('解锁时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间');
            $table->unique(['user_id', 'chapter_id'], 'uk_user_chapter');
            $table->index(['user_id', 'work_id'], 'idx_user_work');
            $table->index('order_no', 'idx_order_no');
            $table->index('deleted_at', 'user_chapter_unlocks_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_chapter_unlocks');
    }
};
