<?php

namespace PC\Test\QueryBuilder;

use PC\Databases\Builders\HavingBuilder;
use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class HavingTest extends TestCase {

    public UserModel $user;

    public function setUp():void {
        $this->user = new UserModel();
        $this->user->setDebugMode(true);
    }

    public function testHaving() {

        $expectedSQL = [$this->user->cleanSQL("
            SELECT
                public.users.user_id, 
                public.users.user_name, 
                public.users.user_email, 
                public.users.user_password, 
                public.users.user_age, 
                public.users.user_min_credit, 
                public.users.user_max_credit, 
                public.users.created_at, 
                public.users.updated_at
            FROM public.users 
            HAVING (
                SUM(public.users.user_age) = 16 
                AND MAX(public.users.user_age) >= 18 
                AND COUNT(public.users.user_age) > 90
            ) OR COUNT(public.users.user_age) > 18
        ")];

        $this->user->select()->havingGroup(function(HavingBuilder $having) {
            $having->sumEqual("user_age", 16)
            ->maxGreaterThanOrEqual("user_age", 18)
            ->countGreaterThan("user_age", 90);
        })
        ->orHavingCountGreaterThan("user_age", 18)
        ->get();
        
        $generatedSQL = $this->user->getSQL();
        $this->assertSame($expectedSQL, $generatedSQL);

    }

}

?>