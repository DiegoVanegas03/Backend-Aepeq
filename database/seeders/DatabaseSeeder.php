<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Taller;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Taller::create([
            'aula' => 'AULA 01',
            'nombre_taller' => 'NEUROANATOMIA',
            'ponente' => 'ELOISA SERVIN',
            'descripcion' => ' ',
            'capacidad_maxima' => 30,
            'dia' => 14,
        ]);
        Taller::create([
            'aula' => 'AULA 01',
            'nombre_taller' => 'NEUROANATOMIA',
            'ponente' => 'ELOISA SERVIN',
            'descripcion' => ' ',
            'capacidad_maxima' => 30,
            'dia' => 14,
        ]);
        Taller::create([
            'aula' => 'AULA 01',
            'nombre_taller' => 'NEUROANATOMIA',
            'ponente' => 'ELOISA SERVIN',
            'descripcion' => ' ',
            'capacidad_maxima' => 30,
            'dia' => 14,
        ]);
        Taller::create([
            'aula' => 'AULA 01',
            'nombre_taller' => 'NEUROANATOMIA',
            'ponente' => 'ELOISA SERVIN',
            'descripcion' => ' ',
            'capacidad_maxima' => 30,
            'dia' => 14,
        ]);
        
    }
}
