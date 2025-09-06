<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('sms_api')->collation('utf8mb4_unicode_ci')->nullable();
            $table->text('google_auth_api')->collation('utf8mb4_unicode_ci')->nullable();
        });

        // Insert default values
        DB::table('settings')->insert([
            'sms_api' => 'sms.duetbd.org/send_sms.php',
            'google_auth_api' => 'apiKey: "AIzaSyA3E80LldZKJJXE00O9-6DWAUtxeKadUM0",
                authDomain: "test-notification-2cc00.firebaseapp.com",
                databaseURL: "https://test-notification-2cc00.firebaseio.com",
                projectId: "test-notification-2cc00",
                storageBucket: "test-notification-2cc00.appspot.com",
                messagingSenderId: "909713642086",
                appId: "1:909713642086:web:5129fc58137f302d1353a7"',
        ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['sms_api', 'google_auth_api']);
        });
    }
};
