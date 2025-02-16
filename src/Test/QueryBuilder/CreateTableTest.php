<?php

namespace PC\Databases;

use PC\Databases\DB;
use PC\Abstracts\AMigration;
use PC\Abstracts\ATableBuilder;

return new class extends AMigration {

    public function up(DB $db, array $params=[]): string {

        // We are going to work on public schema
        $dbSchema = DB::connection("master")->schema("public");

        // It's useful to know first the queries we're going to execute for debugging purposes
        $dbSchema->setDebugMode(true);

        // Build the create table SQL command
        $dbSchema->createTable("my_table", function (ATableBuilder $table) {
            
            $table->addIncrements("table_id")->primary()->comment("Llave primaria");
            $table->addSmallIncrements("table_smallid")->index()->comment("Llave secundaria");
            $table->addBigIncrements("table_bigid")->index();
            $table->addUlid("table_ulid")->index();
            $table->addUuid("table_uuid")->index();
            /* ------------------------------------------- */
            $table->addInteger("table_temperature");
            $table->addSmallInteger("table_balance");
            $table->addBigInteger("table_distance");
            $table->addUnsignedInteger("table_age");
            $table->addUnsignedSmallInteger("table_votes");
            $table->addUnsignedBigInteger("table_atoms");
            $table->addDecimal("table_price", 10, 2);
            /* ------------------------------------------- */
            $table->addBoolean("table_active");
            /* ------------------------------------------- */
            $table->addChar("table_flag", 1)->default("Y");
            $table->addString("table_name", 200);
            $table->addText("table_description");
            /* ------------------------------------------- */
            $table->addDate("table_createdat")->default("CURRENT_TIMESTAMP");
            $table->addTime("table_minimumtime");
            $table->addDatetime("table_delivery");
            $table->addTimestamp("table_deletedat");
            /* ------------------------------------------- */
            $table->addTimeTz("table_recordedat");
            $table->addDateTimeTz("table_editedat");
            $table->addTimestampTz("table_movedat")->comment("Fecha con zona horaria");
        });

        return "Table my_table was created successfuly: " . $dbSchema->getDebugSQL();

    }

    public function down(DB $db, array $params=[]): string {

        // We are going to work on public schema
        $dbSchema = DB::connection("master")->schema("public");

        // It's useful to know first the queries we're going to execute for debugging purposes
        $dbSchema->setDebugMode(true);

        // Execute the delete statement
        $dbSchema->dropTable('users');

        return "Table my_table was created successfuly: " . $dbSchema->getDebugSQL();

    }

};

?>