<?php declare(strict_types=1);

namespace PC\Test\QueryBuilder;

use PC\Test\Models\UserModel;
use PHPUnit\Framework\TestCase;

class FindTest extends TestCase {

    private UserModel $user;

    private CONST USER_ID = "01HH1QP44SYMMHTFTESFVNW770";

    public function setUp():void {
        $this->user = new UserModel();
        /* The model will build the SQL queries without executing them */
        $this->user->setDebugMode(true);
    }

    /* /////////////////////////////////////////////////////////////////////////// //
    // FIND TESTING
    // /////////////////////////////////////////////////////////////////////////// */
    public function testFindWithRelationsSelectingSpecificColumns(): void {

        $this->user->with(["tokens", "sessions"])->find(self::USER_ID, ["*", "tokens.token_id", "tokens.token_ip", "sessions.session_id", "sessions.session_ipaddress"]);
        $generatedSQL = $this->user->getSQL();
        
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
                public.users.updated_at, 
                public.tokens.token_id, 
                public.tokens.token_ip, 
                public.sessions.session_id, 
                public.sessions.session_ipaddress 
            FROM public.users 
            INNER JOIN public.tokens ON tokens.token_tokenable_id = users.user_id 
            INNER JOIN public.sessions ON sessions.session_iduser = users.user_id 
            WHERE public.users.user_id = '".self::USER_ID."'
            LIMIT 1
        ")];
        
        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testFindWithRelationsSelectingAll(): void {

        $this->user->with(["tokens", "sessions"])->find(self::USER_ID, ["*.*"]);
        $generatedSQL = $this->user->getSQL();

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
            public.users.updated_at, 
            public.tokens.token_id, 
            public.tokens.token_tokenable_type, 
            public.tokens.token_tokenable_id, 
            public.tokens.token_device, 
            public.tokens.token_ip, 
            public.tokens.token_data, 
            public.tokens.token_abilities, 
            public.tokens.last_used_at, 
            public.tokens.expires_at, 
            public.tokens.created_at, 
            public.tokens.updated_at, 
            public.sessions.session_id, 
            public.sessions.session_iduser, 
            public.sessions.session_ipaddress, 
            public.sessions.session_useragent, 
            public.sessions.session_payload, 
            public.sessions.session_last_activity 
        FROM public.users 
        INNER JOIN public.tokens ON tokens.token_tokenable_id = users.user_id 
        INNER JOIN public.sessions ON sessions.session_iduser = users.user_id 
        WHERE public.users.user_id = '".self::USER_ID."'
        LIMIT 1
        ")];

        $this->assertSame($expectedSQL, $generatedSQL);
    }

    public function testFindWithRelationsCompareSelectingAllvsSelectionIndividual(): void {

        $this->user->with(["tokens", "sessions"])->find(self::USER_ID, ["*", "tokens.*", "sessions.*"]);
        $selectingIndividual = $this->user->getSQL();

        $this->user->with(["tokens", "sessions"])->find(self::USER_ID, ["*.*"]);
        $selectingAll = $this->user->getSQL();

        $this->assertSame($selectingIndividual, $selectingAll);
    }

}
?>