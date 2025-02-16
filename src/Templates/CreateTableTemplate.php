<?php

namespace PC\Databases;

use Exception;

use PC\Databases\DB;
use PC\Abstracts\AMigration;
use PC\Databases\Base\Builders\TableBuilder;

return new class extends AMigration {

    public function up(DB $db, array $params=[]):string {
        
        $db->schema("public")->createTable("my_table", function (TableBuilder $table) {
                
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

        return "Modification Done";

    }

    public function down(DB $db):string {

        //$db->schema("public")->dropTable('users');

        return "down";

    }

};

?>