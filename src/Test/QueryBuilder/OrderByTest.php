<?php

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class OrderByTest extends TestCase {

    public UserModel $user;

    public function setUp():void {
        $this->user = new UserModel();
        $this->user->setDebugMode(true);
    }

    public function testOrderByDesc():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT public.users.user_name
            FROM public.users 
            GROUP BY public.users.user_email
            ORDER BY public.users.user_id DESC
        ")];

        $this->user->select(["user_name"])
        ->groupBy("user_email")
        ->orderByDesc("user_id")
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testOrderByAsc():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT public.users.user_name
            FROM public.users 
            GROUP BY public.users.user_email
            ORDER BY public.users.user_age ASC
        ")];

        $this->user->select(["user_name"])
        ->groupBy("user_email")
        ->orderByAsc("user_age")
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

}

?>