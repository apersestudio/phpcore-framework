<?php declare(strict_types=1);

namespace PC\Test\QueryBuilder;

use PC\Databases\Builders\WhereBuilder;
use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class WhereTest extends TestCase {

    private UserModel $user;

    private CONST USER_ID = "01HH1QP44SYMMHTFTESFVNW770";
    private CONST USER_NAME = "Fulanito Pérez";
    private CONST USER_EMAIL = "fulanito.perez@domain.example";
    
    public function setUp():void {
        $this->user = new UserModel();
        /* The model will build the SQL queries without executing them */
        $this->user->setDebugMode(true);
    }

    public function testWhere():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT public.users.user_id 
            FROM public.users 
            WHERE public.users.user_name = '".self::USER_NAME."' 
            AND public.users.user_email != '".self::USER_EMAIL."' 
            OR public.users.user_id = '".self::USER_ID."'"
        )];
        
        $this->user->select(["user_id"])
        ->where("user_name", "=", self::USER_NAME)
        ->where("user_email", "!=", self::USER_EMAIL)
        ->orWhere("user_id", "=", self::USER_ID)
        ->get();

        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testWhereGroup() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE (
                LOWER(public.users.user_email) LIKE LOWER('%mucho%') 
                OR LOWER(public.users.user_email) LIKE LOWER('%gara%')
            ) OR (
                LOWER(public.users.user_email) LIKE LOWER('%mucha%') 
                OR LOWER(public.users.user_email) LIKE LOWER('%garou%')
            )
        ")];

        $this->user->select([
                "user_id",
                "user_email as email",
                "user_name",
                "user_password"
            ]
        )->whereGroup(function(WhereBuilder $builder) {
            $builder->contains("user_email", "mucho")
            ->orContains("user_email", "gara");
        })->orWhereGroup(function(WhereBuilder $builder) {
            $builder->contains("user_email", "mucha")
            ->orContains("user_email", "garou");
        })->get();

        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testStartsWith() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE 
                LOWER(public.users.user_email) LIKE LOWER('logo%') 
                AND LOWER(public.users.user_name) NOT LIKE LOWER('menso%') 
                OR LOWER(public.users.user_name) LIKE LOWER('cool%') 
                OR LOWER(public.users.user_name) NOT LIKE LOWER('tonto%')
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereStartsWith("user_email", "logo")
        ->whereNotStartsWith("user_name", "menso")
        ->orWhereStartsWith("user_name", "cool")
        ->orWhereNotStartsWith("user_name", "tonto")
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testEndsWith() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE 
                LOWER(public.users.user_email) LIKE LOWER('%logo') 
                AND LOWER(public.users.user_name) NOT LIKE LOWER('%menso') 
                OR LOWER(public.users.user_name) LIKE LOWER('%cool') 
                OR LOWER(public.users.user_name) NOT LIKE LOWER('%tonto')
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereEndsWith("user_email", "logo")
        ->whereNotEndsWith("user_name", "menso")
        ->orWhereEndsWith("user_name", "cool")
        ->orWhereNotEndsWith("user_name", "tonto")
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testContains() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE 
                LOWER(public.users.user_email) LIKE LOWER('%logo%') 
                AND LOWER(public.users.user_name) NOT LIKE LOWER('%menso%') 
                OR LOWER(public.users.user_name) LIKE LOWER('%cool%') 
                OR LOWER(public.users.user_name) NOT LIKE LOWER('%tonto%')
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereContains("user_email", "logo")
        ->whereNotContains("user_name", "menso")
        ->orWhereContains("user_name", "cool")
        ->orWhereNotContains("user_name", "tonto")
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testBetween() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
                FROM public.users 
            WHERE 
                public.users.user_age BETWEEN 18 AND 49 
                AND public.users.user_age NOT BETWEEN 64 
                AND 90 OR public.users.user_age BETWEEN 12 AND 16 
                OR public.users.user_age NOT BETWEEN 50 AND 63
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereBetween("user_age", 18, 49)
        ->whereNotBetween("user_age", 64, 90)
        ->orWhereBetween("user_age", 12, 16)
        ->orWhereNotBetween("user_age", 50, 63)
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testBetweenColumns() {
        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE 
                100 BETWEEN public.users.user_min_credit AND public.users.user_max_credit 
                AND 200 NOT BETWEEN public.users.user_min_credit AND public.users.user_max_credit 
                OR 300 BETWEEN public.users.user_min_credit AND public.users.user_max_credit 
                OR 400 NOT BETWEEN public.users.user_min_credit AND public.users.user_max_credit
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereBetweenColumns("user_min_credit", "user_max_credit", 100)
        ->whereNotBetweenColumns("user_min_credit", "user_max_credit", 200)
        ->orWhereBetweenColumns("user_min_credit", "user_max_credit", 300)
        ->orWhereNotBetweenColumns("user_min_credit", "user_max_credit", 400)
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testComparissons() {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT 
                public.users.user_id, 
                public.users.user_email AS email, 
                public.users.user_name, 
                public.users.user_password 
            FROM public.users 
            WHERE 
                public.users.user_age > 8 
                AND public.users.user_age >= 8 
                OR public.users.user_age > 8 
                OR public.users.user_age >= 8 
                AND public.users.user_age < 8 
                AND public.users.user_age <= 8 
                OR public.users.user_age < 8 
                OR public.users.user_age <= 8 
                AND public.users.user_age = 8 
                AND public.users.user_age <> 8 
                OR public.users.user_age = 8 
                OR public.users.user_age <> 8
        ")];

        $this->user->select([
            "user_id",
            "user_email as email",
            "user_name",
            "user_password"
            ]
        )
        ->whereGreaterThan("user_age", 8)
        ->whereGreaterThanOrEqual("user_age", 8)
        ->orWhereGreaterThan("user_age", 8)
        ->orWhereGreaterThanOrEqual("user_age", 8)

        ->whereLowerThan("user_age", 8)
        ->whereLowerThanOrEqual("user_age", 8)
        ->orWhereLowerThan("user_age", 8)
        ->orWhereLowerThanOrEqual("user_age", 8)

        ->whereEqual("user_age", 8)
        ->whereDifferent("user_age", 8)
        ->orWhereEqual("user_age", 8)
        ->orWhereDifferent("user_age", 8)
        ->get();

        $generatedSQL = $this->user->getSQL();

        $this->assertSame($expectedSQL, $generatedSQL);

    }

}
?>