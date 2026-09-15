<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OwaspCategorySeeder extends Seeder
{
    public function run(): void
    {
        // On insère la version 2021
        // Si tu veux ajouter 2025 plus tard, tu ajoutes juste un nouveau bloc ici
        DB::table('owasp_categories')->insert([
            ['version' => '2021', 'code' => 'A02', 'name' => 'Cryptographic Failures', 'status' => 'High', 'color' => 'var(--sage-green)', 'guideline' => 'https://owasp.org/www-project-application-security-verification-standard/', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['version' => '2021', 'code' => 'A05', 'name' => 'Security Misconfiguration', 'status' => 'Medium', 'color' => 'var(--orange)', 'guideline' => 'https://owasp.org/www-project-secure-configuration/', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['version' => '2021', 'code' => 'A06', 'name' => 'Vulnerable Components', 'status' => 'Low', 'color' => 'var(--red)', 'guideline' => 'https://owasp.org/www-project-dependency-check/', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['version' => '2021', 'code' => 'A09', 'name' => 'Logging Failures', 'status' => 'High', 'color' => 'var(--sage-green)', 'guideline' => 'https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}