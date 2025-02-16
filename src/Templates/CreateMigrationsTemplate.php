<?php

namespace PC\Databases;

use PC\Databases\DB;
use PC\Abstracts\AMigration;
use PC\Abstracts\ATableBuilder;

return new class extends AMigration {

    public function up(DB $db, array $params=[]):string {

        $db->schema($params["schema"])->createTable('migrations', function (ATableBuilder $table) {

            $table->addBigIncrements('id')->primary();
            $table->addString("path", 255);
            $table->addUnsignedBigInteger("batch");
            $table->addTimestamp('last_executed_at')->nullable();
            $table->addTimestamp('created_at')->nullable();
            
        });

        return "Core: Table migrations was created successfully";

    }

    public function down(DB $db, array $params=[]):string {

        $db->schema($params["schema"])->dropTable('migrations');

        return "Core: Table migrations was deleted successfully";

    }

};

?>