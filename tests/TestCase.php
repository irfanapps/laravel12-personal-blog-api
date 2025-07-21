<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    //use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        DB::delete("delete from comment_likes");
        DB::delete("delete from comments");
        DB::delete("delete from post_tag");
        DB::delete("delete from tags");
        DB::delete("delete from posts");
        DB::delete("delete from categories");
        DB::delete("delete from personal_access_tokens");
        DB::delete("delete from users");
    }
}
