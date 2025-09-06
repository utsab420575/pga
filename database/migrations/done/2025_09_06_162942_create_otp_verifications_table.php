<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto-increment
            $table->string('mobile_number'); // defaults to varchar(255)
            $table->string('otp', 10);       // varchar(10)
            $table->timestamps();            // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
