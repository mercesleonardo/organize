<?php

namespace Database\Seeders;

use App\Actions\Finance\EnsureGlobalDefaultCategoriesAction;
use Illuminate\Database\Seeder;

class GlobalCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        app(EnsureGlobalDefaultCategoriesAction::class)->execute();
    }
}
