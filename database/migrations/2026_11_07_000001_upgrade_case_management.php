<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)
                ->default('mahasiswa')
                ->change();
        });

        if (! Schema::hasColumn('documents', 'description')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->string('case_type', 20)
                ->default('Individu');

            $table->string('review_status', 30)
                ->default('approved')
                ->index();

            $table->text('review_note')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedInteger('revision')->default(1);

            $table->year('angkatan_pembuat')
                ->nullable()
                ->change();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('permission');
            $table->boolean('allowed');

            $table->unique([
                'role',
                'permission',
            ]);
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('permission');
            $table->boolean('allowed');

            $table->unique([
                'user_id',
                'permission',
            ]);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('document_id')
                ->nullable()
                ->constrained('documents')
                ->nullOnDelete();

            $table->index([
                'action',
                'created_at',
            ]);
        });

        DB::table('document_requests')
            ->where('request_type', 'download')
            ->delete();

        if (Schema::hasColumn('temporary_permissions', 'can_download')) {
            DB::table('temporary_permissions')
                ->where('can_read', false)
                ->where('can_upload', false)
                ->delete();

            Schema::table('temporary_permissions', function (Blueprint $table) {
                $table->dropColumn('can_download');
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();

                $table->foreignId('user_id')
                    ->nullable()
                    ->index();

                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Migration CMS bersifat forward-only. '
            .'Pulihkan backup database dan kode bersama-sama.'
        );
    }
};