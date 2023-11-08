<?php

use App\Models\Provinces;
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
        Schema::create('provinces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('code')->nullable(true)->unique();
            $table->longText('description')->nullable(true);
            $table->timestampsTz($precision = 0);
        });

        $provinces_data = [
            ["name"=>"ACEH"],
            ["name"=>"SUMATERA UTARA"],
            ["name"=>"SUMATERA BARAT"],
            ["name"=>"RIAU"],
            ["name"=>"JAMBI"],
            ["name"=>"SUMATERA SELATAN"],
            ["name"=>"BENGKULU"],
            ["name"=>"LAMPUNG"],
            ["name"=>"KEPULAUAN BANGKA BELITUNG"],
            ["name"=>"KEPULAUAN RIAU"],
            ["name"=>"DKI JAKARTA"],
            ["name"=>"JAWA BARAT"],
            ["name"=>"JAWA TENGAH"],
            ["name"=>"DAERAH ISTIMEWA YOGYAKARTA"],
            ["name"=>"JAWA TIMUR"],
            ["name"=>"BANTEN"],
            ["name"=>"BALI"],
            ["name"=>"NUSA TENGGARA BARAT"],
            ["name"=>"NUSA TENGGARA TIMUR"],
            ["name"=>"KALIMANTAN BARAT"],
            ["name"=>"KALIMANTAN TENGAH"],
            ["name"=>"KALIMANTAN SELATAN"],
            ["name"=>"KALIMANTAN TIMUR"],
            ["name"=>"KALIMANTAN UTARA"],
            ["name"=>"SULAWESI UTARA"],
            ["name"=>"SULAWESI TENGAH"],
            ["name"=>"SULAWESI SELATAN"],
            ["name"=>"SULAWESI TENGGARA"],
            ["name"=>"GORONTALO"],
            ["name"=>"SULAWESI BARAT"],
            ["name"=>"MALUKU"],
            ["name"=>"MALUKU UTARA"],
            ["name"=>"PAPUA"],
            ["name"=>"PAPUA BARAT"],
        ];
        Provinces::upsert($provinces_data, ["name"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provinces');
    }
};
