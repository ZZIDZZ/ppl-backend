<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Type::hasType('double')) {
            Type::addType('double', FloatType::class);
        }
        if (!Type::hasType('float')) {
            Type::addType('float', FloatType::class);
        }
        Schema::table('pkl', function (Blueprint $table) {
            // change nilai column to nullable
            // from
            // $table->float('nilai', 16, 2)->default(0);
            $table->float('nilai', 16, 2)->nullable()->default(0)->change();
        });
        Schema::table('skripsi', function (Blueprint $table) {
            // change nilai column to nullable
            // from
            // $table->float('nilai', 16, 2)->default(0);
            $table->float('nilai', 16, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skripsi', function (Blueprint $table) {
            // change nilai column to not nullable
            // from
            // $table->float('nilai', 16, 2)->nullable()->default(0)->change();
            $table->float('nilai', 16, 2)->default(0)->change();
        });
        Schema::table('pkl', function (Blueprint $table) {
            // change nilai column to not nullable
            // from
            // $table->float('nilai', 16, 2)->nullable()->default(0)->change();
            $table->float('nilai', 16, 2)->default(0)->change();
        });
    }
};
