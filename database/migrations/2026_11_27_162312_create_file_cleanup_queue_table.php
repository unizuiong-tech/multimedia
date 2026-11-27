<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_cleanup_queue', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('独立待清理文件队列表');
            }

            $table->bigIncrements('id')->comment('清理任务ID');
            $table->string('file_path', 255)->comment('待清理文件路径(或视频版本目录路径)');
            $table->string('file_kind', 20)->default('file')->comment('清理类型: file单文件 / dir目录(如m3u8版本目录)');
            $table->string('cleanup_reason', 20)->comment('入队原因: replaced替换新文件 / soft_deleted资源逻辑删除');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('触发操作管理员ID');
            $table->tinyInteger('cleanup_status')->default(0)->comment('处理状态: 0待清理 1已物理删除 2清理失败 3已跳过/恢复取消');
            $table->dateTime('delete_after_at')->comment('允许执行物理删除的最早时间(保留期截止点)');
            $table->dateTime('cleaned_at')->nullable()->comment('实际物理删除完成时间');
            $table->dateTime('created_at')->useCurrent()->comment('入队时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->index(['cleanup_status', 'delete_after_at', 'id'], 'idx_status_delete_after');
            $table->index(['cleanup_reason', 'cleanup_status'], 'idx_reason_status');
            $table->index('file_path', 'idx_file_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_cleanup_queue');
    }
};
