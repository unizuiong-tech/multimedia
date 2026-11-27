<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('后台操作日志表');
            }

            $table->bigIncrements('id')->comment('日志ID');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('操作管理员ID');
            $table->string('client_ip', 45)->default('')->comment('操作来源IP');
            $table->string('detail_summary', 2000)->default('')->comment('操作摘要或字段变更说明(纯文本)');
            $table->dateTime('created_at')->useCurrent()->comment('操作时间');
            $table->index(['admin_id', 'created_at'], 'idx_admin_created');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
