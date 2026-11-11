<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Fiksi' => [
                'Romance', 'Fantasi', 'Misteri dan Thriller', 'Fiksi Ilmiah', 'Horor',
                'Sastra', 'Cerita Anak', 'Kumpulan Cerpen', 'Puisi', 'Naskah Drama',
            ],
            'Non-Fiksi' => [
                'Pengembangan Diri', 'Bisnis dan Keuangan', 'Teknologi dan Komputer',
                'Sains Populer', 'Sejarah dan Politik', 'Biografi dan Memoar',
                'Agama dan Spiritual', 'Kesehatan dan Psikologi',
                'Pendidikan dan Modul Belajar', 'Panduan dan Hobi',
            ],
        ];

        $now = now();

        foreach ($tree as $parentName => $children) {
            $parentSlug = str($parentName)->slug()->toString();

            DB::table('categories')->updateOrInsert(
                ['slug' => $parentSlug],
                ['parent_id' => null, 'name' => $parentName, 'created_at' => $now, 'updated_at' => $now]
            );

            $parentId = DB::table('categories')->where('slug', $parentSlug)->value('id');

            foreach ($children as $childName) {
                DB::table('categories')->updateOrInsert(
                    ['slug' => str($childName)->slug()->toString()],
                    ['parent_id' => $parentId, 'name' => $childName, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}
