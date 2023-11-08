<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_mappings', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->foreignId('role_id')->nullable()->constrained('roles');
            $table->foreignId('permission_id')->nullable()->constrained('permissions');
            $table->timestampsTz($precision = 0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_mappings');
    }
};
