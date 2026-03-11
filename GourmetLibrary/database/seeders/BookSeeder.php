<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            // ── Pâtisserie Française ────────────────────────────────────────────────
            [
                'category'       => 'patisserie-francaise',
                'title'          => 'L\'Art de la Pâtisserie',
                'author'         => 'Pierre Hermé',
                'isbn'           => '978-2-08-020161-0',
                'description'    => 'Le maître de la pâtisserie partage ses secrets pour réaliser des gâteaux d\'exception.',
                'published_year' => 2020,
                'publisher'      => 'Flammarion',
                'language'       => 'Français',
                'total_copies'   => 4,
                'arrival_date'   => now()->subDays(10),
            ],
            [
                'category'       => 'patisserie-francaise',
                'title'          => 'Encyclopédie du Chocolat',
                'author'         => 'Frédéric Bau',
                'isbn'           => '978-2-08-124500-3',
                'description'    => 'Tout ce qu\'il faut savoir sur le travail du chocolat, de la ganache au tempérage.',
                'published_year' => 2018,
                'publisher'      => 'Flammarion',
                'language'       => 'Français',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(90),
            ],
            [
                'category'       => 'patisserie-francaise',
                'title'          => 'Les Macarons de Ladurée',
                'author'         => 'Marie-Hélène Eveno',
                'isbn'           => '978-2-35392-011-4',
                'description'    => 'L\'histoire et les recettes iconiques de la célèbre maison de pâtisserie parisienne.',
                'published_year' => 2012,
                'publisher'      => 'Éditions du Chêne',
                'language'       => 'Français',
                'total_copies'   => 2,
                'arrival_date'   => now()->subDays(200),
            ],
            [
                'category'       => 'patisserie-francaise',
                'title'          => 'Le Grand Livre de la Brioche',
                'author'         => 'Christophe Felder',
                'isbn'           => '978-2-03-588510-2',
                'description'    => 'Brioches, pains sucrés et viennoiseries dans une bible incontournable.',
                'published_year' => 2017,
                'publisher'      => 'La Martinière',
                'language'       => 'Français',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(5),
            ],
            // ── Cuisine du Monde ───────────────────────────────────────────────────
            [
                'category'       => 'cuisine-du-monde',
                'title'          => 'Japan : The Cookbook',
                'author'         => 'Nancy Singleton Hachisu',
                'isbn'           => '978-0-7148-7350-4',
                'description'    => 'Une plongée profonde dans la cuisine japonaise traditionnelle et contemporaine.',
                'published_year' => 2018,
                'publisher'      => 'Phaidon Press',
                'language'       => 'Anglais',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(60),
            ],
            [
                'category'       => 'cuisine-du-monde',
                'title'          => 'Plenty More',
                'author'         => 'Yotam Ottolenghi',
                'isbn'           => '978-0-09-194714-3',
                'description'    => 'Des recettes végétales inspirées des cuisines du Moyen-Orient et de la Méditerranée.',
                'published_year' => 2014,
                'publisher'      => 'Ebury Press',
                'language'       => 'Anglais',
                'total_copies'   => 4,
                'arrival_date'   => now()->subDays(150),
            ],
            [
                'category'       => 'cuisine-du-monde',
                'title'          => 'Saveurs du Maroc',
                'author'         => 'Fatima Hal',
                'isbn'           => '978-2-01-231548-9',
                'description'    => 'Les recettes emblématiques de la gastronomie marocaine : tajines, couscous, pastilla.',
                'published_year' => 2016,
                'publisher'      => 'Hachette Pratique',
                'language'       => 'Français',
                'total_copies'   => 5,
                'arrival_date'   => now()->subDays(20),
            ],
            // ── Sans Gluten ────────────────────────────────────────────────────────
            [
                'category'       => 'sans-gluten',
                'title'          => 'Pâtisserie Sans Gluten',
                'author'         => 'Estérelle Payany',
                'isbn'           => '978-2-01-331628-3',
                'description'    => 'Tous les classiques de la pâtisserie revisités pour les intolérants au gluten.',
                'published_year' => 2019,
                'publisher'      => 'Hachette Pratique',
                'language'       => 'Français',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(7),
            ],
            [
                'category'       => 'sans-gluten',
                'title'          => 'Cuisine Saine et Sans Gluten',
                'author'         => 'Cléa Cuisine',
                'isbn'           => '978-2-84876-220-6',
                'description'    => 'Des recettes équilibrées et délicieuses pour une alimentation sans gluten au quotidien.',
                'published_year' => 2015,
                'publisher'      => 'La Plage',
                'language'       => 'Français',
                'total_copies'   => 2,
                'arrival_date'   => now()->subDays(300),
            ],
            // ── Cuisine Végétarienne ───────────────────────────────────────────────
            [
                'category'       => 'cuisine-vegetarienne',
                'title'          => 'Ottolenghi Simple',
                'author'         => 'Yotam Ottolenghi',
                'isbn'           => '978-0-09-197310-4',
                'description'    => 'Des recettes végétariennes simples, rapides et spectaculaires.',
                'published_year' => 2018,
                'publisher'      => 'Ebury Press',
                'language'       => 'Anglais',
                'total_copies'   => 4,
                'arrival_date'   => now()->subDays(45),
            ],
            [
                'category'       => 'cuisine-vegetarienne',
                'title'          => 'L\'Art de Cuisiner Végétarien',
                'author'         => 'Joël Thiébault',
                'isbn'           => '978-2-35803-215-7',
                'description'    => 'Le légumier de l\'Élysée partage ses meilleures recettes végétariennes.',
                'published_year' => 2021,
                'publisher'      => 'Alain Ducasse Édition',
                'language'       => 'Français',
                'total_copies'   => 2,
                'arrival_date'   => now()->subDays(15),
            ],
            // ── Barbecue & Grillades ───────────────────────────────────────────────
            [
                'category'       => 'barbecue-grillades',
                'title'          => 'Franklin Barbecue: A Meat-Smoking Manifesto',
                'author'         => 'Aaron Franklin',
                'isbn'           => '978-1-60774-591-5',
                'description'    => 'La bible du barbecue texan par le pitmaster le plus primé d\'Amérique.',
                'published_year' => 2015,
                'publisher'      => 'Ten Speed Press',
                'language'       => 'Anglais',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(180),
            ],
            // ── Pâtes & Risotto ────────────────────────────────────────────────────
            [
                'category'       => 'pates-risotto',
                'title'          => 'Pasta Grannies',
                'author'         => 'Vicky Bennison',
                'isbn'           => '978-1-78472-537-5',
                'description'    => 'Les secrets des nonnas italiennes pour faire les meilleures pâtes maison.',
                'published_year' => 2019,
                'publisher'      => 'Hardie Grant Books',
                'language'       => 'Anglais',
                'total_copies'   => 4,
                'arrival_date'   => now()->subDays(25),
            ],
            // ── Desserts & Chocolat ────────────────────────────────────────────────
            [
                'category'       => 'desserts-chocolat',
                'title'          => 'The Zumbo Files',
                'author'         => 'Adriano Zumbo',
                'isbn'           => '978-1-74196-994-1',
                'description'    => 'Desserts extraordinaires du chef pâtissier australien star.',
                'published_year' => 2011,
                'publisher'      => 'Murdoch Books',
                'language'       => 'Anglais',
                'total_copies'   => 2,
                'arrival_date'   => now()->subDays(400),
            ],
            // ── Cuisine Rapide ─────────────────────────────────────────────────────
            [
                'category'       => 'cuisine-rapide',
                'title'          => '5 Ingrédients - Jamie Oliver',
                'author'         => 'Jamie Oliver',
                'isbn'           => '978-0-718-18441-0',
                'description'    => 'Des recettes délicieuses avec seulement 5 ingrédients et en un temps record.',
                'published_year' => 2017,
                'publisher'      => 'Michael Joseph',
                'language'       => 'Anglais',
                'total_copies'   => 6,
                'arrival_date'   => now()->subDays(3),
            ],
            // ── Cuisine Moléculaire ────────────────────────────────────────────────
            [
                'category'       => 'cuisine-moleculaire',
                'title'          => 'Modernist Cuisine at Home',
                'author'         => 'Nathan Myhrvold',
                'isbn'           => '978-0-982-86991-1',
                'description'    => 'Les techniques de la cuisine moderniste accessibles aux cuisiniers passionnés.',
                'published_year' => 2012,
                'publisher'      => 'The Cooking Lab',
                'language'       => 'Anglais',
                'total_copies'   => 2,
                'arrival_date'   => now()->subDays(500),
            ],
            // ── Cuisine Méditerranéenne ────────────────────────────────────────────
            [
                'category'       => 'cuisine-mediterraneenne',
                'title'          => 'Jerusalem',
                'author'         => 'Yotam Ottolenghi & Sami Tamimi',
                'isbn'           => '978-0-09-194533-0',
                'description'    => 'Un voyage culinaire à travers les saveurs et les cultures de Jérusalem.',
                'published_year' => 2012,
                'publisher'      => 'Ebury Press',
                'language'       => 'Anglais',
                'total_copies'   => 4,
                'arrival_date'   => now()->subDays(120),
            ],
            [
                'category'       => 'cuisine-mediterraneenne',
                'title'          => 'La Cuisine de Joël Robuchon',
                'author'         => 'Joël Robuchon',
                'isbn'           => '978-2-08-020097-2',
                'description'    => 'Les recettes et techniques du chef français le plus étoilé au monde.',
                'published_year' => 2008,
                'publisher'      => 'Flammarion',
                'language'       => 'Français',
                'total_copies'   => 3,
                'arrival_date'   => now()->subDays(700),
            ],
        ];

        foreach ($books as $bookData) {
            $categorySlug = $bookData['category'];
            unset($bookData['category']);

            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                Book::firstOrCreate(
                    ['isbn' => $bookData['isbn']],
                    array_merge($bookData, ['category_id' => $category->id])
                );
            }
        }
    }
}
