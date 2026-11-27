<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('用户表');
            }

            $table->bigIncrements('id')->comment('用户ID');
            $table->string('nickname', 100)->default('')->comment('用户昵称');
            $table->unsignedInteger('coin_balance')->default(0)->comment('书币/积分余额');
            $table->dateTime('vip_expire_at')->nullable()->comment('VIP到期时间(为空或早于当前时间表示非VIP)');
            $table->tinyInteger('account_status')->default(1)->comment('账号状态: 1正常 2封禁');
            $table->dateTime('created_at')->useCurrent()->comment('注册时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间');
            $table->index('account_status', 'idx_status');
            $table->index('deleted_at', 'users_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
