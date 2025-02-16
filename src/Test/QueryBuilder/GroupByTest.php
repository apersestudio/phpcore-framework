<?php

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class GroupByTest extends TestCase {

    public UserModel $user;

    public function setUp():void {
        $this->user = new UserModel();
        $this->user->setDebugMode(true);
    }

    public function testGroupBy():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                public.users.user_email, 
                public.users.user_age
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupBy("user_email")
        ->groupBy("user_age")
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByLeft():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                LEFT(public.users.user_email, 1)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByLeft("user_email", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByRight():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                RIGHT(public.users.user_email, 1)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByRight("user_email", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByTrim():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                TRIM(public.users.user_name)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByTrim("user_name", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByLength():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                LENGTH(public.users.user_name)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByLength("user_name", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByLower():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                LOWER(public.users.user_name)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByLower("user_name", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

    public function testGroupByUpper():void {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name
            FROM public.users 
            GROUP BY
                UPPER(public.users.user_name)
        ")];

        $this->user->select(["user_id", "user_name"])
        ->groupByUpper("user_name", 1)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

}

?>