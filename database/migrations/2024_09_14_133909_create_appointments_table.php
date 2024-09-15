<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('client_name')->nullable();  // Peut être nul jusqu'à ce que le RDV soit réservé
            $table->string('client_email')->nullable(); // Peut être nul aussi
            $table->boolean('is_booked')->default(false); // Pour savoir si le créneau est réservé ou non
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
