<?php declare(strict_types=1);

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase {

    private UserModel $user;

    private CONST USER_ID = "01HH1QP44SYMMHTFTESFVNW770";
    private CONST USER_NAME = "Fulanito Pérez";
    private CONST USER_EMAIL = "fulanito.perez@domain.example";

    public CONST UPDATE_DATA = [
        "user_name"=>self::USER_NAME,
        "user_email"=>self::USER_EMAIL
    ];

    public function setUp():void {
        $this->user = new UserModel();
        /* The model will build the SQL queries without executing them */
        $this->user->setDebugMode(true);
    }

    /* /////////////////////////////////////////////////////////////////////////// //
    // UPDATE TESTING
    // /////////////////////////////////////////////////////////////////////////// */
    public function testUpdateWithoutWhere() {
        $this->expectExceptionMessage("Trying to execute an update statement without where");
        $this->user->update(self::UPDATE_DATA)->execute();
        
    }

    public function testUpdateWithoutWhereButForcing() {
        $this->user->forceUpdate(self::UPDATE_DATA)->execute();
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = ["UPDATE public.users SET user_name = '".self::USER_NAME."', user_email = '".self::USER_EMAIL."'"];
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testUpdateWithWhere() {
        $this->user->update(self::UPDATE_DATA)->whereContains("user_name", "james")->execute();
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = ["UPDATE public.users SET user_name = '".self::USER_NAME."', user_email = '".self::USER_EMAIL."' WHERE LOWER(public.users.user_name) LIKE LOWER('%james%')"];
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testUpdateByID() {
        $this->user->updateByID(self::USER_ID, self::UPDATE_DATA);
        $generatedSQL = $this->user->getSQL();
        $expectedSQL = [
            "SELECT public.users.user_id FROM public.users WHERE public.users.user_id = '01HH1QP44SYMMHTFTESFVNW770' LIMIT 1",
            "UPDATE public.users SET user_name = '".self::USER_NAME."', user_email = '".self::USER_EMAIL."' WHERE public.users.user_id = '".self::USER_ID."'"
        ];
        $this->assertSame($expectedSQL, $generatedSQL);
    }

}
?>