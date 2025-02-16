<?php declare(strict_types=1);

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase {

    private UserModel $user;

    private CONST USER_ID = "01HH1QP44SYMMHTFTESFVNW770";

    public function setUp():void {
        $this->user = new UserModel();
        /* The model will build the SQL queries without executing them */
        $this->user->setDebugMode(true);
    }

    public function testDeleteWithoutWhere() {
        
        $this->expectExceptionMessage("Trying to execute a delete statement without where");
        $this->user->delete()->execute();
        
    }

    public function testDeleteWithoutWhereButForcing() {
        
        $this->user->forceDelete()->execute();
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = ["DELETE FROM public.users"];
        $this->assertSame($expectedSQL, $generatedSQL);
        
    }

    public function testDeleteWithWhere() {
        $this->user->delete()->whereContains("user_name", "james")->execute();
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = ["DELETE FROM public.users WHERE LOWER(public.users.user_name) LIKE LOWER('%james%')"];
        
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testDeleteByID() {
        $this->user->deleteByID(self::USER_ID);
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = ["DELETE FROM public.users WHERE public.users.user_id = '".self::USER_ID."'"];
        
        $this->assertSame($expectedSQL, $generatedSQL);
    }

}
?>