<?php declare(strict_types=1);

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase {

    private UserModel $user;

    private CONST USER_ID = "01HH1QP44SYMMHTFTESFVNW770";
    private CONST USER_NAME = "Fulanito Pérez";
    private CONST USER_EMAIL = "fulanito.perez@domain.example";
    private CONST USER_PASSWORD = '$2y$12$e.qUpIIPnWRxOvw08kOWSOzRoTPXLlPogIo4.RVwB5XspTxfF3YtO';

    public function setUp():void {
        $this->user = new UserModel();
        /* The model will build the SQL queries without executing them */
        $this->user->setDebugMode(true);
    }

    public function testInsertOrUpdate() {
        
        $this->user->updateOrInsert(self::USER_ID, ["user_name"=>self::USER_NAME, "user_email"=>self::USER_EMAIL, "user_password"=>self::USER_PASSWORD]);
        $generatedSQL = $this->user->getSQL();

        $expectedSQL = [
            "SELECT public.users.user_id FROM public.users WHERE public.users.user_id = '".self::USER_ID."' LIMIT 1",
            "INSERT INTO public.users (user_name, user_email, user_password, user_id) VALUES ('".self::USER_NAME."', '".self::USER_EMAIL."', '".self::USER_PASSWORD."', '".self::USER_ID."');"
        ];
        
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testInsertMultiple() {
        $insertData = [
            ["user_id"=>"01J5YFG27SXB3KTE340DX6VXEG", "user_name"=>"Nombre1", "user_email"=>"email.uno@domain.example", "user_password"=>"132456a"],
            ["user_id"=>"01J5YFG27SXB3KTE340DX6VXEH", "user_name"=>"Nombre2", "user_email"=>"email.dos@domain.example", "user_password"=>"132456b"],
            ["user_id"=>"01J5YFG27SXB3KTE340DX6VXEJ", "user_name"=>"Nombre3", "user_email"=>"email.tres@domain.example", "user_password"=>"132456c"],
            ["user_id"=>"01J5YFG27SXB3KTE340DX6VXEK", "user_name"=>"Nombre4", "user_email"=>"email.cuatro@domain.example", "user_password"=>"132456d"],
            ["user_id"=>"01J5YFG27SXB3KTE340DX6VXEM", "user_name"=>"Nombre5", "user_email"=>"email.cinco@domain.example", "user_password"=>"132456e"]
        ];
        $this->user->insertMultiple($insertData);
        $generatedSQL = $this->user->getSQL();

        $expectedSQL = [$this->user->cleanSQL("
            INSERT INTO public.users (user_id, user_name, user_email, user_password) VALUES 
            ('01J5YFG27SXB3KTE340DX6VXEG','Nombre1','email.uno@domain.example','132456a'), 
            ('01J5YFG27SXB3KTE340DX6VXEH','Nombre2','email.dos@domain.example','132456b'), 
            ('01J5YFG27SXB3KTE340DX6VXEJ','Nombre3','email.tres@domain.example','132456c'), 
            ('01J5YFG27SXB3KTE340DX6VXEK','Nombre4','email.cuatro@domain.example','132456d'), 
            ('01J5YFG27SXB3KTE340DX6VXEM','Nombre5','email.cinco@domain.example','132456e');
        ")];

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testInsert() {
        $userData = [
            "user_id"=>"01J5YHRM0N2H5DMFFDD7K9VW9V", 
            "user_name"=>"Nombre1", 
            "user_email"=>"email.uno@domain.example", 
            "user_password"=>"132456a"
        ];
        $this->user->insert($userData);
        $generatedSQL = $this->user->getSQL();
        
        $expectedSQL = ["INSERT INTO public.users (user_id, user_name, user_email, user_password) VALUES ('01J5YHRM0N2H5DMFFDD7K9VW9V', 'Nombre1', 'email.uno@domain.example', '132456a');"];
        $this->assertSame($expectedSQL, $generatedSQL);
    }

}
?>