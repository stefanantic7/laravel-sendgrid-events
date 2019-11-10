<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendgridEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('sendgridevents.events_table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('timestamp')->nullable();
            $table->string('email')->index();
            $table->string('event')->index();
            $table->string('sg_event_id')->unique();
            $table->string('sg_message_id')->index();
            $table->jsonb('payload');
            $table->timestamps();
        });

        $connection = config('sendgridevents.database_connection_for_events');
        if(is_null($connection)) {
            $connection = config('database.default');
        }
        $driver = config("database.connections.{$connection}.driver");

        switch ($driver) {
            case 'mysql': {
                Schema::table(config('sendgridevents.events_table_name'), function (Blueprint $table) {
                    $table->jsonb('categories')
                        ->after('sg_message_id');
                });

                break;
            }

            default: {
                Schema::table(config('sendgridevents.events_table_name'), function (Blueprint $table) {
                    $table->jsonb('categories')
                        ->after('sg_message_id')
                        ->default(json_encode([]))
                        ->index();
                });

                break;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('sendgridevents.events_table_name'));
    }
}
