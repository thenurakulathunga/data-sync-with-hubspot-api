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
        Schema::create('company_contact', function (Blueprint $table) {
            $table->string('company_id');
            $table->string('contact_id');

            $table->foreign('company_id')->references('hs_object_id')->on('companies')->onDelete('cascade');
            $table->foreign('contact_id')->references('hs_object_id')->on('contacts')->onDelete('cascade');

            $table->primary(['company_id', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_contact');
    }
};
