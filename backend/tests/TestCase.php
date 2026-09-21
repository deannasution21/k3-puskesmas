<?php

namespace Tests;

use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsPuskesmas(?Puskesmas $puskesmas = null): User
    {
        $puskesmas ??= Puskesmas::factory()->create();

        $user = User::factory()->create([
            'role' => 'puskesmas',
            'puskesmas_id' => $puskesmas->id,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function actingAsDinas(): User
    {
        $user = User::factory()->create([
            'role' => 'dinas',
            'puskesmas_id' => null,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }
}
