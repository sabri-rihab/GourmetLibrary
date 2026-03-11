<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Pâtisserie Française',
                'slug'        => 'patisserie-francaise',
                'description' => 'Macarons, éclairs, mille-feuilles et autres chefs-d\'œuvres de la pâtisserie hexagonale.',
                'color'       => '#f472b6',
            ],
            [
                'name'        => 'Cuisine du Monde',
                'slug'        => 'cuisine-du-monde',
                'description' => 'Voyage culinaire à travers les saveurs des cinq continents.',
                'color'       => '#f97316',
            ],
            [
                'name'        => 'Sans Gluten',
                'slug'        => 'sans-gluten',
                'description' => 'Recettes délicieuses adaptées aux intolérants au gluten.',
                'color'       => '#84cc16',
            ],
            [
                'name'        => 'Cuisine Végétarienne',
                'slug'        => 'cuisine-vegetarienne',
                'description' => 'Recettes savoureuses sans viande ni poisson.',
                'color'       => '#22c55e',
            ],
            [
                'name'        => 'Barbecue & Grillades',
                'slug'        => 'barbecue-grillades',
                'description' => 'L\'art du feu, des marinades et des cuissons à la braise.',
                'color'       => '#ef4444',
            ],
            [
                'name'        => 'Pâtes & Risotto',
                'slug'        => 'pates-risotto',
                'description' => 'La cuisine italienne dans tous ses états : pasta, risotto, polenta.',
                'color'       => '#eab308',
            ],
            [
                'name'        => 'Desserts & Chocolat',
                'slug'        => 'desserts-chocolat',
                'description' => 'Gâteaux, fondants, mousses et créations chocolatées.',
                'color'       => '#a16207',
            ],
            [
                'name'        => 'Cuisine Rapide',
                'slug'        => 'cuisine-rapide',
                'description' => 'Des plats délicieux en moins de 30 minutes pour les journées chargées.',
                'color'       => '#06b6d4',
            ],
            [
                'name'        => 'Cuisine Moléculaire',
                'slug'        => 'cuisine-moleculaire',
                'description' => 'La science au service du goût — sphérification, émulsions et gels.',
                'color'       => '#8b5cf6',
            ],
            [
                'name'        => 'Cuisine Méditerranéenne',
                'slug'        => 'cuisine-mediterraneenne',
                'description' => 'Huile d\'olive, herbes fraîches, poissons et légumes du soleil.',
                'color'       => '#0ea5e9',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
