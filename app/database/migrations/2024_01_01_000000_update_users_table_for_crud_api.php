<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the old name column and add new required fields
            $table->dropColumn('name');
            $table->dropColumn('email_verified_at');
            $table->dropColumn('remember_token');
            
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->tinyInteger('role')->after('last_name')->comment('1=Admin, 2=Supervisor, 3=Agent');
            $table->decimal('latitude', 10, 8)->after('email');
            $table->decimal('longitude', 11, 8)->after('latitude');
            $table->date('date_of_birth')->after('longitude');
            $table->string('timezone')->after('date_of_birth');
            
            $table->index('role');
            $table->index('email');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->rememberToken();
            
            $table->dropColumn([
                'first_name',
                'last_name', 
                'role',
                'latitude',
                'longitude',
                'date_of_birth',
                'timezone'
            ]);
            
            $table->dropIndex(['users_role_index']);
            $table->dropIndex(['users_email_index']);
            $table->dropIndex(['users_latitude_longitude_index']);
        });
    }
};