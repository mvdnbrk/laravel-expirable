<?php

namespace Mvdnbrk\EloquentExpirable\Tests;

use Illuminate\Support\Carbon;
use Mvdnbrk\EloquentExpirable\Tests\Models\Subscription;

class ExpirableScopeTest extends TestCase
{
    /* @var \Mvdnbrk\EloquentExpirable\Tests\Models\Subscription */
    protected $expired;

    /* @var \Mvdnbrk\EloquentExpirable\Tests\Models\Subscription */
    protected $expiresInFuture;

    /* @var \Mvdnbrk\EloquentExpirable\Tests\Models\Subscription */
    protected $expiresNever;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        Carbon::setTestNow('2019-01-01 12:34:56');

        $this->expired = Subscription::create([
            'expires_at' => Carbon::now()->addDay(),
        ]);

        Carbon::setTestNow();

        $this->expiresNever = Subscription::create([
            'expires_at' => null,
        ]);

        $this->expiresInFuture = Subscription::create([
            'expires_at' => Carbon::now()->addDay(),
        ]);
    }

    /** @test */
    public function it_can_retrieve_only_expired_models()
    {
        tap(Subscription::onlyExpired()->get(), function ($models) {
            $this->assertCount(1, $models);
            $this->assertTrue($models->first()->is($this->expired));
        });
    }

    /** @test */
    public function it_can_retrieve_all_models_without_expired()
    {
        tap(Subscription::withoutExpired()->get(), function ($models) {
            $this->assertCount(2, $models);
            $this->assertTrue($models->contains($this->expiresNever));
            $this->assertTrue($models->contains($this->expiresInFuture));
        });
    }

    /** @test */
    public function it_can_retrieve_all_models_expiring_in_the_future()
    {
        tap(Subscription::expiring()->get(), function ($models) {
            $this->assertCount(1, $models);
            $this->assertTrue($models->contains($this->expiresInFuture));
        });
    }

    /** @test */
    public function it_can_retrieve_all_models_that_will_not_expire_in_the_future()
    {
        tap(Subscription::notExpiring()->get(), function ($models) {
            $this->assertCount(1, $models);
            $this->assertTrue($models->contains($this->expiresNever));
        });
    }
}
