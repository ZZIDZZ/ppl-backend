<?php

use App\Models\Cities;
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
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('code')->nullable(true);
            $table->longText('description')->nullable(true);
            $table->foreignId('province_id');
            $table->timestampsTz($precision = 0);
        });

        $cities_data = [
            ["province_id" => "1", "name" => "KAB. ACEH SELATAN"],
            ["province_id" => "1", "name" => "KAB. ACEH TENGGARA"],
            ["province_id" => "1", "name" => "KAB. ACEH TIMUR"],
            ["province_id" => "1", "name" => "KAB. ACEH TENGAH"],
            ["province_id" => "1", "name" => "KAB. ACEH BARAT"],
            ["province_id" => "1", "name" => "KAB. ACEH BESAR"],
            ["province_id" => "1", "name" => "KAB. PIDIE"],
            ["province_id" => "1", "name" => "KAB. ACEH UTARA"],
            ["province_id" => "1", "name" => "KAB. SIMEULUE"],
            ["province_id" => "1", "name" => "KAB. ACEH SINGKIL"],
            ["province_id" => "1", "name" => "KAB. BIREUEN"],
            ["province_id" => "1", "name" => "KAB. ACEH BARAT DAYA"],
            ["province_id" => "1", "name" => "KAB. GAYO LUES"],
            ["province_id" => "1", "name" => "KAB. ACEH JAYA"],
            ["province_id" => "1", "name" => "KAB. NAGAN RAYA"],
            ["province_id" => "1", "name" => "KAB. ACEH TAMIANG"],
            ["province_id" => "1", "name" => "KAB. BENER MERIAH"],
            ["province_id" => "1", "name" => "KAB. PIDIE JAYA"],
            ["province_id" => "1", "name" => "KOTA BANDA ACEH"],
            ["province_id" => "1", "name" => "KOTA SABANG"],
            ["province_id" => "1", "name" => "KOTA LHOKSEUMAWE"],
            ["province_id" => "1", "name" => "KOTA LANGSA"],
            ["province_id" => "1", "name" => "KOTA SUBULUSSALAM"],
            ["province_id" => "2", "name" => "KAB. TAPANULI TENGAH"],
            ["province_id" => "2", "name" => "KAB. TAPANULI UTARA"],
            ["province_id" => "2", "name" => "KAB. TAPANULI SELATAN"],
            ["province_id" => "2", "name" => "KAB. NIAS"],
            ["province_id" => "2", "name" => "KAB. LANGKAT"],
            ["province_id" => "2", "name" => "KAB. KARO"],
            ["province_id" => "2", "name" => "KAB. DELI SERDANG"],
            ["province_id" => "2", "name" => "KAB. SIMALUNGUN"],
            ["province_id" => "2", "name" => "KAB. ASAHAN"],
            ["province_id" => "2", "name" => "KAB. LABUHANBATU"],
            ["province_id" => "2", "name" => "KAB. DAIRI"],
            ["province_id" => "2", "name" => "KAB. TOBA"],
            ["province_id" => "2", "name" => "KAB. MANDAILING NATAL"],
            ["province_id" => "2", "name" => "KAB. NIAS SELATAN"],
            ["province_id" => "2", "name" => "KAB. PAKPAK BHARAT"],
            ["province_id" => "2", "name" => "KAB. HUMBANG HASUNDUTAN"],
            ["province_id" => "2", "name" => "KAB. SAMOSIR"],
            ["province_id" => "2", "name" => "KAB. SERDANG BEDAGAI"],
            ["province_id" => "2", "name" => "KAB. BATU BARA"],
            ["province_id" => "2", "name" => "KAB. PADANG LAWAS UTARA"],
            ["province_id" => "2", "name" => "KAB. PADANG LAWAS"],
            ["province_id" => "2", "name" => "KAB. LABUHANBATU SELATAN"],
            ["province_id" => "2", "name" => "KAB. LABUHANBATU UTARA"],
            ["province_id" => "2", "name" => "KAB. NIAS UTARA"],
            ["province_id" => "2", "name" => "KAB. NIAS BARAT"],
            ["province_id" => "2", "name" => "KOTA MEDAN"],
            ["province_id" => "2", "name" => "KOTA PEMATANGSIANTAR"],
            ["province_id" => "2", "name" => "KOTA SIBOLGA"],
            ["province_id" => "2", "name" => "KOTA TANJUNG BALAI"],
            ["province_id" => "2", "name" => "KOTA BINJAI"],
            ["province_id" => "2", "name" => "KOTA TEBING TINGGI"],
            ["province_id" => "2", "name" => "KOTA PADANGSIDIMPUAN"],
            ["province_id" => "2", "name" => "KOTA GUNUNGSITOLI"],
            ["province_id" => "3", "name" => "KAB. PESISIR SELATAN"],
            ["province_id" => "3", "name" => "KAB. SOLOK"],
            ["province_id" => "3", "name" => "KAB. SIJUNJUNG"],
            ["province_id" => "3", "name" => "KAB. TANAH DATAR"],
            ["province_id" => "3", "name" => "KAB. PADANG PARIAMAN"],
            ["province_id" => "3", "name" => "KAB. AGAM"],
            ["province_id" => "3", "name" => "KAB. LIMA PULUH KOTA"],
            ["province_id" => "3", "name" => "KAB. PASAMAN"],
            ["province_id" => "3", "name" => "KAB. KEPULAUAN MENTAWAI"],
            ["province_id" => "3", "name" => "KAB. DHARMASRAYA"],
            ["province_id" => "3", "name" => "KAB. SOLOK SELATAN"],
            ["province_id" => "3", "name" => "KAB. PASAMAN BARAT"],
            ["province_id" => "3", "name" => "KOTA PADANG"],
            ["province_id" => "3", "name" => "KOTA SOLOK"],
            ["province_id" => "3", "name" => "KOTA SAWAHLUNTO"],
            ["province_id" => "3", "name" => "KOTA PADANG PANJANG"],
            ["province_id" => "3", "name" => "KOTA BUKITTINGGI"],
            ["province_id" => "3", "name" => "KOTA PAYAKUMBUH"],
            ["province_id" => "3", "name" => "KOTA PARIAMAN"],
            ["province_id" => "4", "name" => "KAB. KAMPAR"],
            ["province_id" => "4", "name" => "KAB. INDRAGIRI HULU"],
            ["province_id" => "4", "name" => "KAB. BENGKALIS"],
            ["province_id" => "4", "name" => "KAB. INDRAGIRI HILIR"],
            ["province_id" => "4", "name" => "KAB. PELALAWAN"],
            ["province_id" => "4", "name" => "KAB. ROKAN HULU"],
            ["province_id" => "4", "name" => "KAB. ROKAN HILIR"],
            ["province_id" => "4", "name" => "KAB. SIAK"],
            ["province_id" => "4", "name" => "KAB. KUANTAN SINGINGI"],
            ["province_id" => "4", "name" => "KAB. KEPULAUAN MERANTI"],
            ["province_id" => "4", "name" => "KOTA PEKANBARU"],
            ["province_id" => "4", "name" => "KOTA DUMAI"],
            ["province_id" => "5", "name" => "KAB. KERINCI"],
            ["province_id" => "5", "name" => "KAB. MERANGIN"],
            ["province_id" => "5", "name" => "KAB. SAROLANGUN"],
            ["province_id" => "5", "name" => "KAB. BATANGHARI"],
            ["province_id" => "5", "name" => "KAB. MUARO JAMBI"],
            ["province_id" => "5", "name" => "KAB. TANJUNG JABUNG BARAT"],
            ["province_id" => "5", "name" => "KAB. TANJUNG JABUNG TIMUR"],
            ["province_id" => "5", "name" => "KAB. BUNGO"],
            ["province_id" => "5", "name" => "KAB. TEBO"],
            ["province_id" => "5", "name" => "KOTA JAMBI"],
            ["province_id" => "5", "name" => "KOTA SUNGAI PENUH"],
            ["province_id" => "6", "name" => "KAB. OGAN KOMERING ULU"],
            ["province_id" => "6", "name" => "KAB. OGAN KOMERING ILIR"],
            ["province_id" => "6", "name" => "KAB. MUARA ENIM"],
            ["province_id" => "6", "name" => "KAB. LAHAT"],
            ["province_id" => "6", "name" => "KAB. MUSI RAWAS"],
            ["province_id" => "6", "name" => "KAB. MUSI BANYUASIN"],
            ["province_id" => "6", "name" => "KAB. BANYUASIN"],
            ["province_id" => "6", "name" => "KAB. OGAN KOMERING ULU TIMUR"],
            ["province_id" => "6", "name" => "KAB. OGAN KOMERING ULU SELATAN"],
            ["province_id" => "6", "name" => "KAB. OGAN ILIR"],
            ["province_id" => "6", "name" => "KAB. EMPAT LAWANG"],
            ["province_id" => "6", "name" => "KAB. PENUKAL ABAB LEMATANG ILIR"],
            ["province_id" => "6", "name" => "KAB. MUSI RAWAS UTARA"],
            ["province_id" => "6", "name" => "KOTA PALEMBANG"],
            ["province_id" => "6", "name" => "KOTA PAGAR ALAM"],
            ["province_id" => "6", "name" => "KOTA LUBUK LINGGAU"],
            ["province_id" => "6", "name" => "KOTA PRABUMULIH"],
            ["province_id" => "7", "name" => "KAB. BENGKULU SELATAN"],
            ["province_id" => "7", "name" => "KAB. REJANG LEBONG"],
            ["province_id" => "7", "name" => "KAB. BENGKULU UTARA"],
            ["province_id" => "7", "name" => "KAB. KAUR"],
            ["province_id" => "7", "name" => "KAB. SELUMA"],
            ["province_id" => "7", "name" => "KAB. MUKO MUKO"],
            ["province_id" => "7", "name" => "KAB. LEBONG"],
            ["province_id" => "7", "name" => "KAB. KEPAHIANG"],
            ["province_id" => "7", "name" => "KAB. BENGKULU TENGAH"],
            ["province_id" => "7", "name" => "KOTA BENGKULU"],
            ["province_id" => "8", "name" => "KAB. LAMPUNG SELATAN"],
            ["province_id" => "8", "name" => "KAB. LAMPUNG TENGAH"],
            ["province_id" => "8", "name" => "KAB. LAMPUNG UTARA"],
            ["province_id" => "8", "name" => "KAB. LAMPUNG BARAT"],
            ["province_id" => "8", "name" => "KAB. TULANG BAWANG"],
            ["province_id" => "8", "name" => "KAB. TANGGAMUS"],
            ["province_id" => "8", "name" => "KAB. LAMPUNG TIMUR"],
            ["province_id" => "8", "name" => "KAB. WAY KANAN"],
            ["province_id" => "8", "name" => "KAB. PESAWARAN"],
            ["province_id" => "8", "name" => "KAB. PRINGSEWU"],
            ["province_id" => "8", "name" => "KAB. MESUJI"],
            ["province_id" => "8", "name" => "KAB. TULANG BAWANG BARAT"],
            ["province_id" => "8", "name" => "KAB. PESISIR BARAT"],
            ["province_id" => "8", "name" => "KOTA BANDAR LAMPUNG"],
            ["province_id" => "8", "name" => "KOTA METRO"],
            ["province_id" => "9", "name" => "KAB. BANGKA"],
            ["province_id" => "9", "name" => "KAB. BELITUNG"],
            ["province_id" => "9", "name" => "KAB. BANGKA SELATAN"],
            ["province_id" => "9", "name" => "KAB. BANGKA TENGAH"],
            ["province_id" => "9", "name" => "KAB. BANGKA BARAT"],
            ["province_id" => "9", "name" => "KAB. BELITUNG TIMUR"],
            ["province_id" => "9", "name" => "KOTA PANGKAL PINANG"],
            ["province_id" => "10", "name" => "KAB. BINTAN"],
            ["province_id" => "10", "name" => "KAB. KARIMUN"],
            ["province_id" => "10", "name" => "KAB. NATUNA"],
            ["province_id" => "10", "name" => "KAB. LINGGA"],
            ["province_id" => "10", "name" => "KAB. KEPULAUAN ANAMBAS"],
            ["province_id" => "10", "name" => "KOTA BATAM"],
            ["province_id" => "10", "name" => "KOTA TANJUNG PINANG"],
            ["province_id" => "11", "name" => "KAB. ADM. KEP. SERIBU"],
            ["province_id" => "11", "name" => "KOTA ADM. JAKARTA PUSAT"],
            ["province_id" => "11", "name" => "KOTA ADM. JAKARTA UTARA"],
            ["province_id" => "11", "name" => "KOTA ADM. JAKARTA BARAT"],
            ["province_id" => "11", "name" => "KOTA ADM. JAKARTA SELATAN"],
            ["province_id" => "11", "name" => "KOTA ADM. JAKARTA TIMUR"],
            ["province_id" => "12", "name" => "KAB. BOGOR"],
            ["province_id" => "12", "name" => "KAB. SUKABUMI"],
            ["province_id" => "12", "name" => "KAB. CIANJUR"],
            ["province_id" => "12", "name" => "KAB. BANDUNG"],
            ["province_id" => "12", "name" => "KAB. GARUT"],
            ["province_id" => "12", "name" => "KAB. TASIKMALAYA"],
            ["province_id" => "12", "name" => "KAB. CIAMIS"],
            ["province_id" => "12", "name" => "KAB. KUNINGAN"],
            ["province_id" => "12", "name" => "KAB. CIREBON"],
            ["province_id" => "12", "name" => "KAB. MAJALENGKA"],
            ["province_id" => "12", "name" => "KAB. SUMEDANG"],
            ["province_id" => "12", "name" => "KAB. INDRAMAYU"],
            ["province_id" => "12", "name" => "KAB. SUBANG"],
            ["province_id" => "12", "name" => "KAB. PURWAKARTA"],
            ["province_id" => "12", "name" => "KAB. KARAWANG"],
            ["province_id" => "12", "name" => "KAB. BEKASI"],
            ["province_id" => "12", "name" => "KAB. BANDUNG BARAT"],
            ["province_id" => "12", "name" => "KAB. PANGANDARAN"],
            ["province_id" => "12", "name" => "KOTA BOGOR"],
            ["province_id" => "12", "name" => "KOTA SUKABUMI"],
            ["province_id" => "12", "name" => "KOTA BANDUNG"],
            ["province_id" => "12", "name" => "KOTA CIREBON"],
            ["province_id" => "12", "name" => "KOTA BEKASI"],
            ["province_id" => "12", "name" => "KOTA DEPOK"],
            ["province_id" => "12", "name" => "KOTA CIMAHI"],
            ["province_id" => "12", "name" => "KOTA TASIKMALAYA"],
            ["province_id" => "12", "name" => "KOTA BANJAR"],
            ["province_id" => "13", "name" => "KAB. CILACAP"],
            ["province_id" => "13", "name" => "KAB. BANYUMAS"],
            ["province_id" => "13", "name" => "KAB. PURBALINGGA"],
            ["province_id" => "13", "name" => "KAB. BANJARNEGARA"],
            ["province_id" => "13", "name" => "KAB. KEBUMEN"],
            ["province_id" => "13", "name" => "KAB. PURWOREJO"],
            ["province_id" => "13", "name" => "KAB. WONOSOBO"],
            ["province_id" => "13", "name" => "KAB. MAGELANG"],
            ["province_id" => "13", "name" => "KAB. BOYOLALI"],
            ["province_id" => "13", "name" => "KAB. KLATEN"],
            ["province_id" => "13", "name" => "KAB. SUKOHARJO"],
            ["province_id" => "13", "name" => "KAB. WONOGIRI"],
            ["province_id" => "13", "name" => "KAB. KARANGANYAR"],
            ["province_id" => "13", "name" => "KAB. SRAGEN"],
            ["province_id" => "13", "name" => "KAB. GROBOGAN"],
            ["province_id" => "13", "name" => "KAB. BLORA"],
            ["province_id" => "13", "name" => "KAB. REMBANG"],
            ["province_id" => "13", "name" => "KAB. PATI"],
            ["province_id" => "13", "name" => "KAB. KUDUS"],
            ["province_id" => "13", "name" => "KAB. JEPARA"],
            ["province_id" => "13", "name" => "KAB. DEMAK"],
            ["province_id" => "13", "name" => "KAB. SEMARANG"],
            ["province_id" => "13", "name" => "KAB. TEMANGGUNG"],
            ["province_id" => "13", "name" => "KAB. KENDAL"],
            ["province_id" => "13", "name" => "KAB. BATANG"],
            ["province_id" => "13", "name" => "KAB. PEKALONGAN"],
            ["province_id" => "13", "name" => "KAB. PEMALANG"],
            ["province_id" => "13", "name" => "KAB. TEGAL"],
            ["province_id" => "13", "name" => "KAB. BREBES"],
            ["province_id" => "13", "name" => "KOTA MAGELANG"],
            ["province_id" => "13", "name" => "KOTA SURAKARTA"],
            ["province_id" => "13", "name" => "KOTA SALATIGA"],
            ["province_id" => "13", "name" => "KOTA SEMARANG"],
            ["province_id" => "13", "name" => "KOTA PEKALONGAN"],
            ["province_id" => "13", "name" => "KOTA TEGAL"],
            ["province_id" => "14", "name" => "KAB. KULON PROGO"],
            ["province_id" => "14", "name" => "KAB. BANTUL"],
            ["province_id" => "14", "name" => "KAB. GUNUNGKIDUL"],
            ["province_id" => "14", "name" => "KAB. SLEMAN"],
            ["province_id" => "14", "name" => "KOTA YOGYAKARTA"],
            ["province_id" => "15", "name" => "KAB. PACITAN"],
            ["province_id" => "15", "name" => "KAB. PONOROGO"],
            ["province_id" => "15", "name" => "KAB. TRENGGALEK"],
            ["province_id" => "15", "name" => "KAB. TULUNGAGUNG"],
            ["province_id" => "15", "name" => "KAB. BLITAR"],
            ["province_id" => "15", "name" => "KAB. KEDIRI"],
            ["province_id" => "15", "name" => "KAB. MALANG"],
            ["province_id" => "15", "name" => "KAB. LUMAJANG"],
            ["province_id" => "15", "name" => "KAB. JEMBER"],
            ["province_id" => "15", "name" => "KAB. BANYUWANGI"],
            ["province_id" => "15", "name" => "KAB. BONDOWOSO"],
            ["province_id" => "15", "name" => "KAB. SITUBONDO"],
            ["province_id" => "15", "name" => "KAB. PROBOLINGGO"],
            ["province_id" => "15", "name" => "KAB. PASURUAN"],
            ["province_id" => "15", "name" => "KAB. SIDOARJO"],
            ["province_id" => "15", "name" => "KAB. MOJOKERTO"],
            ["province_id" => "15", "name" => "KAB. JOMBANG"],
            ["province_id" => "15", "name" => "KAB. NGANJUK"],
            ["province_id" => "15", "name" => "KAB. MADIUN"],
            ["province_id" => "15", "name" => "KAB. MAGETAN"],
            ["province_id" => "15", "name" => "KAB. NGAWI"],
            ["province_id" => "15", "name" => "KAB. BOJONEGORO"],
            ["province_id" => "15", "name" => "KAB. TUBAN"],
            ["province_id" => "15", "name" => "KAB. LAMONGAN"],
            ["province_id" => "15", "name" => "KAB. GRESIK"],
            ["province_id" => "15", "name" => "KAB. BANGKALAN"],
            ["province_id" => "15", "name" => "KAB. SAMPANG"],
            ["province_id" => "15", "name" => "KAB. PAMEKASAN"],
            ["province_id" => "15", "name" => "KAB. SUMENEP"],
            ["province_id" => "15", "name" => "KOTA KEDIRI"],
            ["province_id" => "15", "name" => "KOTA BLITAR"],
            ["province_id" => "15", "name" => "KOTA MALANG"],
            ["province_id" => "15", "name" => "KOTA PROBOLINGGO"],
            ["province_id" => "15", "name" => "KOTA PASURUAN"],
            ["province_id" => "15", "name" => "KOTA MOJOKERTO"],
            ["province_id" => "15", "name" => "KOTA MADIUN"],
            ["province_id" => "15", "name" => "KOTA SURABAYA"],
            ["province_id" => "15", "name" => "KOTA BATU"],
            ["province_id" => "16", "name" => "KAB. PANDEGLANG"],
            ["province_id" => "16", "name" => "KAB. LEBAK"],
            ["province_id" => "16", "name" => "KAB. TANGERANG"],
            ["province_id" => "16", "name" => "KAB. SERANG"],
            ["province_id" => "16", "name" => "KOTA TANGERANG"],
            ["province_id" => "16", "name" => "KOTA CILEGON"],
            ["province_id" => "16", "name" => "KOTA SERANG"],
            ["province_id" => "16", "name" => "KOTA TANGERANG SELATAN"],
            ["province_id" => "17", "name" => "KAB. JEMBRANA"],
            ["province_id" => "17", "name" => "KAB. TABANAN"],
            ["province_id" => "17", "name" => "KAB. BADUNG"],
            ["province_id" => "17", "name" => "KAB. GIANYAR"],
            ["province_id" => "17", "name" => "KAB. KLUNGKUNG"],
            ["province_id" => "17", "name" => "KAB. BANGLI"],
            ["province_id" => "17", "name" => "KAB. KARANGASEM"],
            ["province_id" => "17", "name" => "KAB. BULELENG"],
            ["province_id" => "17", "name" => "KOTA DENPASAR"],
            ["province_id" => "18", "name" => "KAB. LOMBOK BARAT"],
            ["province_id" => "18", "name" => "KAB. LOMBOK TENGAH"],
            ["province_id" => "18", "name" => "KAB. LOMBOK TIMUR"],
            ["province_id" => "18", "name" => "KAB. SUMBAWA"],
            ["province_id" => "18", "name" => "KAB. DOMPU"],
            ["province_id" => "18", "name" => "KAB. BIMA"],
            ["province_id" => "18", "name" => "KAB. SUMBAWA BARAT"],
            ["province_id" => "18", "name" => "KAB. LOMBOK UTARA"],
            ["province_id" => "18", "name" => "KOTA MATARAM"],
            ["province_id" => "18", "name" => "KOTA BIMA"],
            ["province_id" => "19", "name" => "KAB. KUPANG"],
            ["province_id" => "19", "name" => "KAB TIMOR TENGAH SELATAN"],
            ["province_id" => "19", "name" => "KAB. TIMOR TENGAH UTARA"],
            ["province_id" => "19", "name" => "KAB. BELU"],
            ["province_id" => "19", "name" => "KAB. ALOR"],
            ["province_id" => "19", "name" => "KAB. FLORES TIMUR"],
            ["province_id" => "19", "name" => "KAB. SIKKA"],
            ["province_id" => "19", "name" => "KAB. ENDE"],
            ["province_id" => "19", "name" => "KAB. NGADA"],
            ["province_id" => "19", "name" => "KAB. MANGGARAI"],
            ["province_id" => "19", "name" => "KAB. SUMBA TIMUR"],
            ["province_id" => "19", "name" => "KAB. SUMBA BARAT"],
            ["province_id" => "19", "name" => "KAB. LEMBATA"],
            ["province_id" => "19", "name" => "KAB. ROTE NDAO"],
            ["province_id" => "19", "name" => "KAB. MANGGARAI BARAT"],
            ["province_id" => "19", "name" => "KAB. NAGEKEO"],
            ["province_id" => "19", "name" => "KAB. SUMBA TENGAH"],
            ["province_id" => "19", "name" => "KAB. SUMBA BARAT DAYA"],
            ["province_id" => "19", "name" => "KAB. MANGGARAI TIMUR"],
            ["province_id" => "19", "name" => "KAB. SABU RAIJUA"],
            ["province_id" => "19", "name" => "KAB. MALAKA"],
            ["province_id" => "19", "name" => "KOTA KUPANG"],
            ["province_id" => "20", "name" => "KAB. SAMBAS"],
            ["province_id" => "20", "name" => "KAB. MEMPAWAH"],
            ["province_id" => "20", "name" => "KAB. SANGGAU"],
            ["province_id" => "20", "name" => "KAB. KETAPANG"],
            ["province_id" => "20", "name" => "KAB. SINTANG"],
            ["province_id" => "20", "name" => "KAB. KAPUAS HULU"],
            ["province_id" => "20", "name" => "KAB. BENGKAYANG"],
            ["province_id" => "20", "name" => "KAB. LANDAK"],
            ["province_id" => "20", "name" => "KAB. SEKADAU"],
            ["province_id" => "20", "name" => "KAB. MELAWI"],
            ["province_id" => "20", "name" => "KAB. KAYONG UTARA"],
            ["province_id" => "20", "name" => "KAB. KUBU RAYA"],
            ["province_id" => "20", "name" => "KOTA PONTIANAK"],
            ["province_id" => "20", "name" => "KOTA SINGKAWANG"],
            ["province_id" => "21", "name" => "KAB. KOTAWARINGIN BARAT"],
            ["province_id" => "21", "name" => "KAB. KOTAWARINGIN TIMUR"],
            ["province_id" => "21", "name" => "KAB. KAPUAS"],
            ["province_id" => "21", "name" => "KAB. BARITO SELATAN"],
            ["province_id" => "21", "name" => "KAB. BARITO UTARA"],
            ["province_id" => "21", "name" => "KAB. KATINGAN"],
            ["province_id" => "21", "name" => "KAB. SERUYAN"],
            ["province_id" => "21", "name" => "KAB. SUKAMARA"],
            ["province_id" => "21", "name" => "KAB. LAMANDAU"],
            ["province_id" => "21", "name" => "KAB. GUNUNG MAS"],
            ["province_id" => "21", "name" => "KAB. PULANG PISAU"],
            ["province_id" => "21", "name" => "KAB. MURUNG RAYA"],
            ["province_id" => "21", "name" => "KAB. BARITO TIMUR"],
            ["province_id" => "21", "name" => "KOTA PALANGKARAYA"],
            ["province_id" => "22", "name" => "KAB. TANAH LAUT"],
            ["province_id" => "22", "name" => "KAB. KOTABARU"],
            ["province_id" => "22", "name" => "KAB. BANJAR"],
            ["province_id" => "22", "name" => "KAB. BARITO KUALA"],
            ["province_id" => "22", "name" => "KAB. TAPIN"],
            ["province_id" => "22", "name" => "KAB. HULU SUNGAI SELATAN"],
            ["province_id" => "22", "name" => "KAB. HULU SUNGAI TENGAH"],
            ["province_id" => "22", "name" => "KAB. HULU SUNGAI UTARA"],
            ["province_id" => "22", "name" => "KAB. TABALONG"],
            ["province_id" => "22", "name" => "KAB. TANAH BUMBU"],
            ["province_id" => "22", "name" => "KAB. BALANGAN"],
            ["province_id" => "22", "name" => "KOTA BANJARMASIN"],
            ["province_id" => "22", "name" => "KOTA BANJARBARU"],
            ["province_id" => "23", "name" => "KAB. PASER"],
            ["province_id" => "23", "name" => "KAB. KUTAI KARTANEGARA"],
            ["province_id" => "23", "name" => "KAB. BERAU"],
            ["province_id" => "23", "name" => "KAB. KUTAI BARAT"],
            ["province_id" => "23", "name" => "KAB. KUTAI TIMUR"],
            ["province_id" => "23", "name" => "KAB. PENAJAM PASER UTARA"],
            ["province_id" => "23", "name" => "KAB. MAHAKAM ULU"],
            ["province_id" => "23", "name" => "KOTA BALIKPAPAN"],
            ["province_id" => "23", "name" => "KOTA SAMARINDA"],
            ["province_id" => "23", "name" => "KOTA BONTANG"],
            ["province_id" => "24", "name" => "KAB. BULUNGAN"],
            ["province_id" => "24", "name" => "KAB. MALINAU"],
            ["province_id" => "24", "name" => "KAB. NUNUKAN"],
            ["province_id" => "24", "name" => "KAB. TANA TIDUNG"],
            ["province_id" => "24", "name" => "KOTA TARAKAN"],
            ["province_id" => "25", "name" => "KAB. BOLAANG MONGONDOW"],
            ["province_id" => "25", "name" => "KAB. MINAHASA"],
            ["province_id" => "25", "name" => "KAB. KEPULAUAN SANGIHE"],
            ["province_id" => "25", "name" => "KAB. KEPULAUAN TALAUD"],
            ["province_id" => "25", "name" => "KAB. MINAHASA SELATAN"],
            ["province_id" => "25", "name" => "KAB. MINAHASA UTARA"],
            ["province_id" => "25", "name" => "KAB. MINAHASA TENGGARA"],
            ["province_id" => "25", "name" => "KAB. BOLAANG MONGONDOW UTARA"],
            ["province_id" => "25", "name" => "KAB. KEP. SIAU TAGULANDANG BIARO"],
            ["province_id" => "25", "name" => "KAB. BOLAANG MONGONDOW TIMUR"],
            ["province_id" => "25", "name" => "KAB. BOLAANG MONGONDOW SELATAN"],
            ["province_id" => "25", "name" => "KOTA MANADO"],
            ["province_id" => "25", "name" => "KOTA BITUNG"],
            ["province_id" => "25", "name" => "KOTA TOMOHON"],
            ["province_id" => "25", "name" => "KOTA KOTAMOBAGU"],
            ["province_id" => "26", "name" => "KAB. BANGGAI"],
            ["province_id" => "26", "name" => "KAB. POSO"],
            ["province_id" => "26", "name" => "KAB. DONGGALA"],
            ["province_id" => "26", "name" => "KAB. TOLI TOLI"],
            ["province_id" => "26", "name" => "KAB. BUOL"],
            ["province_id" => "26", "name" => "KAB. MOROWALI"],
            ["province_id" => "26", "name" => "KAB. BANGGAI KEPULAUAN"],
            ["province_id" => "26", "name" => "KAB. PARIGI MOUTONG"],
            ["province_id" => "26", "name" => "KAB. TOJO UNA UNA"],
            ["province_id" => "26", "name" => "KAB. SIGI"],
            ["province_id" => "26", "name" => "KAB. BANGGAI LAUT"],
            ["province_id" => "26", "name" => "KAB. MOROWALI UTARA"],
            ["province_id" => "26", "name" => "KOTA PALU"],
            ["province_id" => "27", "name" => "KAB. KEPULAUAN SELAYAR"],
            ["province_id" => "27", "name" => "KAB. BULUKUMBA"],
            ["province_id" => "27", "name" => "KAB. BANTAENG"],
            ["province_id" => "27", "name" => "KAB. JENEPONTO"],
            ["province_id" => "27", "name" => "KAB. TAKALAR"],
            ["province_id" => "27", "name" => "KAB. GOWA"],
            ["province_id" => "27", "name" => "KAB. SINJAI"],
            ["province_id" => "27", "name" => "KAB. BONE"],
            ["province_id" => "27", "name" => "KAB. MAROS"],
            ["province_id" => "27", "name" => "KAB. PANGKAJENE KEPULAUAN"],
            ["province_id" => "27", "name" => "KAB. BARRU"],
            ["province_id" => "27", "name" => "KAB. SOPPENG"],
            ["province_id" => "27", "name" => "KAB. WAJO"],
            ["province_id" => "27", "name" => "KAB. SIDENRENG RAPPANG"],
            ["province_id" => "27", "name" => "KAB. PINRANG"],
            ["province_id" => "27", "name" => "KAB. ENREKANG"],
            ["province_id" => "27", "name" => "KAB. LUWU"],
            ["province_id" => "27", "name" => "KAB. TANA TORAJA"],
            ["province_id" => "27", "name" => "KAB. LUWU UTARA"],
            ["province_id" => "27", "name" => "KAB. LUWU TIMUR"],
            ["province_id" => "27", "name" => "KAB. TORAJA UTARA"],
            ["province_id" => "27", "name" => "KOTA MAKASSAR"],
            ["province_id" => "27", "name" => "KOTA PARE PARE"],
            ["province_id" => "27", "name" => "KOTA PALOPO"],
            ["province_id" => "28", "name" => "KAB. KOLAKA"],
            ["province_id" => "28", "name" => "KAB. KONAWE"],
            ["province_id" => "28", "name" => "KAB. MUNA"],
            ["province_id" => "28", "name" => "KAB. BUTON"],
            ["province_id" => "28", "name" => "KAB. KONAWE SELATAN"],
            ["province_id" => "28", "name" => "KAB. BOMBANA"],
            ["province_id" => "28", "name" => "KAB. WAKATOBI"],
            ["province_id" => "28", "name" => "KAB. KOLAKA UTARA"],
            ["province_id" => "28", "name" => "KAB. KONAWE UTARA"],
            ["province_id" => "28", "name" => "KAB. BUTON UTARA"],
            ["province_id" => "28", "name" => "KAB. KOLAKA TIMUR"],
            ["province_id" => "28", "name" => "KAB. KONAWE KEPULAUAN"],
            ["province_id" => "28", "name" => "KAB. MUNA BARAT"],
            ["province_id" => "28", "name" => "KAB. BUTON TENGAH"],
            ["province_id" => "28", "name" => "KAB. BUTON SELATAN"],
            ["province_id" => "28", "name" => "KOTA KENDARI"],
            ["province_id" => "28", "name" => "KOTA BAU BAU"],
            ["province_id" => "29", "name" => "KAB. GORONTALO"],
            ["province_id" => "29", "name" => "KAB. BOALEMO"],
            ["province_id" => "29", "name" => "KAB. BONE BOLANGO"],
            ["province_id" => "29", "name" => "KAB. PAHUWATO"],
            ["province_id" => "29", "name" => "KAB. GORONTALO UTARA"],
            ["province_id" => "29", "name" => "KOTA GORONTALO"],
            ["province_id" => "30", "name" => "KAB. PASANGKAYU"],
            ["province_id" => "30", "name" => "KAB. MAMUJU"],
            ["province_id" => "30", "name" => "KAB. MAMASA"],
            ["province_id" => "30", "name" => "KAB. POLEWALI MANDAR"],
            ["province_id" => "30", "name" => "KAB. MAJENE"],
            ["province_id" => "30", "name" => "KAB. MAMUJU TENGAH"],
            ["province_id" => "31", "name" => "KAB. MALUKU TENGAH"],
            ["province_id" => "31", "name" => "KAB. MALUKU TENGGARA"],
            ["province_id" => "31", "name" => "KAB. KEPULAUAN TANIMBAR"],
            ["province_id" => "31", "name" => "KAB. BURU"],
            ["province_id" => "31", "name" => "KAB. SERAM BAGIAN TIMUR"],
            ["province_id" => "31", "name" => "KAB. SERAM BAGIAN BARAT"],
            ["province_id" => "31", "name" => "KAB. KEPULAUAN ARU"],
            ["province_id" => "31", "name" => "KAB. MALUKU BARAT DAYA"],
            ["province_id" => "31", "name" => "KAB. BURU SELATAN"],
            ["province_id" => "31", "name" => "KOTA AMBON"],
            ["province_id" => "31", "name" => "KOTA TUAL"],
            ["province_id" => "32", "name" => "KAB. HALMAHERA BARAT"],
            ["province_id" => "32", "name" => "KAB. HALMAHERA TENGAH"],
            ["province_id" => "32", "name" => "KAB. HALMAHERA UTARA"],
            ["province_id" => "32", "name" => "KAB. HALMAHERA SELATAN"],
            ["province_id" => "32", "name" => "KAB. KEPULAUAN SULA"],
            ["province_id" => "32", "name" => "KAB. HALMAHERA TIMUR"],
            ["province_id" => "32", "name" => "KAB. PULAU MOROTAI"],
            ["province_id" => "32", "name" => "KAB. PULAU TALIABU"],
            ["province_id" => "32", "name" => "KOTA TERNATE"],
            ["province_id" => "32", "name" => "KOTA TIDORE KEPULAUAN"],
            ["province_id" => "33", "name" => "KAB. MERAUKE"],
            ["province_id" => "33", "name" => "KAB. JAYAWIJAYA"],
            ["province_id" => "33", "name" => "KAB. JAYAPURA"],
            ["province_id" => "33", "name" => "KAB. NABIRE"],
            ["province_id" => "33", "name" => "KAB. KEPULAUAN YAPEN"],
            ["province_id" => "33", "name" => "KAB. BIAK NUMFOR"],
            ["province_id" => "33", "name" => "KAB. PUNCAK JAYA"],
            ["province_id" => "33", "name" => "KAB. PANIAI"],
            ["province_id" => "33", "name" => "KAB. MIMIKA"],
            ["province_id" => "33", "name" => "KAB. SARMI"],
            ["province_id" => "33", "name" => "KAB. KEEROM"],
            ["province_id" => "33", "name" => "KAB. PEGUNUNGAN BINTANG"],
            ["province_id" => "33", "name" => "KAB. YAHUKIMO"],
            ["province_id" => "33", "name" => "KAB. TOLIKARA"],
            ["province_id" => "33", "name" => "KAB. WAROPEN"],
            ["province_id" => "33", "name" => "KAB. BOVEN DIGOEL"],
            ["province_id" => "33", "name" => "KAB. MAPPI"],
            ["province_id" => "33", "name" => "KAB. ASMAT"],
            ["province_id" => "33", "name" => "KAB. SUPIORI"],
            ["province_id" => "33", "name" => "KAB. MAMBERAMO RAYA"],
            ["province_id" => "33", "name" => "KAB. MAMBERAMO TENGAH"],
            ["province_id" => "33", "name" => "KAB. YALIMO"],
            ["province_id" => "33", "name" => "KAB. LANNY JAYA"],
            ["province_id" => "33", "name" => "KAB. NDUGA"],
            ["province_id" => "33", "name" => "KAB. PUNCAK"],
            ["province_id" => "33", "name" => "KAB. DOGIYAI"],
            ["province_id" => "33", "name" => "KAB. INTAN JAYA"],
            ["province_id" => "33", "name" => "KAB. DEIYAI"],
            ["province_id" => "33", "name" => "KOTA JAYAPURA"],
            ["province_id" => "34", "name" => "KAB. SORONG"],
            ["province_id" => "34", "name" => "KAB. MANOKWARI"],
            ["province_id" => "34", "name" => "KAB. FAK FAK"],
            ["province_id" => "34", "name" => "KAB. SORONG SELATAN"],
            ["province_id" => "34", "name" => "KAB. RAJA AMPAT"],
            ["province_id" => "34", "name" => "KAB. TELUK BINTUNI"],
            ["province_id" => "34", "name" => "KAB. TELUK WONDAMA"],
            ["province_id" => "34", "name" => "KAB. KAIMANA"],
            ["province_id" => "34", "name" => "KAB. TAMBRAUW"],
            ["province_id" => "34", "name" => "KAB. MAYBRAT"],
            ["province_id" => "34", "name" => "KAB. MANOKWARI SELATAN"],
            ["province_id" => "34", "name" => "KAB. PEGUNUNGAN ARFAK"],
            ["province_id" => "34", "name" => "KOTA SORONG"],
        ];
        Cities::upsert($cities_data, ["name"], ["province_id"]);
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
