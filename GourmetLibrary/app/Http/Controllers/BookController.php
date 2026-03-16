<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GOURMAND FEATURES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * [US-G1] List all books in a specific category.
     *
     * GET /api/books/category/{categoryId}
     *   OR
     * GET /api/books/category/{slug}   (slug is also supported)
     *
     * Returns the category name + paginated list of books (15 per page).
     */
    public function byCategory(Request $request, $identifier): JsonResponse
    {
        // Accept either numeric ID or string slug
        $category = is_numeric($identifier)
            ? Category::findOrFail($identifier)
            : Category::where('slug', $identifier)->firstOrFail();

        $books = $category->books()
            ->select(['id', 'title', 'author', 'isbn', 'description',
                       'cover_image', 'published_year', 'publisher',
                       'language', 'total_copies', 'arrival_date'])
            ->orderBy('title')
            ->paginate(15);

        // Append computed helpers to every book
        $books->getCollection()->transform(function (Book $book) {
            $book->append(['total_borrows', 'is_new_arrival']);
            return $book;
        });

        return response()->json([
            'success'  => true,
            'category' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
                'color'       => $category->color,
            ],
            'data' => $books,
        ], 200);
    }

    /**
     * [US-G2] Search books by title, author, or category name.
     *
     * GET /api/books/search?q={query}
     *
     * All three fields are searched with a LIKE %query% pattern.
     * Results are paginated (15 per page).
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query = $request->input('q');

        $books = Book::with('category:id,name,slug,color')
            ->select(['id', 'category_id', 'title', 'author', 'isbn',
                       'description', 'cover_image', 'published_year',
                       'publisher', 'language', 'total_copies', 'arrival_date'])
            ->where(function ($q) use ($query) {
                $q->where('title',  'like', "%{$query}%")
                  ->orWhere('author', 'like', "%{$query}%")
                  ->orWhereHas('category', function ($q) use ($query) {
                      $q->where('name', 'like', "%{$query}%");
                  });
            })
            ->paginate(15);

        $books->getCollection()->transform(function (Book $book) {
            $book->append(['total_borrows', 'is_new_arrival']);
            return $book;
        });

        return response()->json([
            'success'      => true,
            'search_query' => $query,
            'data'         => $books,
        ], 200);
    }

    /**
     * [US-G3a] Get the most popular books (most borrowed) in a category.
     *
     * GET /api/books/popular?category_id={id}&limit={n}
     *
     * - category_id  : optional, filter by category
     * - limit        : optional (default 10, max 50)
     *
     * Popularity is measured by counting borrow records through book_copies.
     */
    public function popular(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'limit'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = $request->input('limit', 10);

        $books = Book::with('category:id,name,slug,color')
            ->select(['books.*'])
            ->withCount(['borrows as borrow_count'])   // uses hasManyThrough
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            })
            ->orderByDesc('borrow_count')
            ->limit($limit)
            ->get();

        $books->transform(function (Book $book) {
            $book->append(['is_new_arrival']);
            return $book;
        });

        return response()->json([
            'success' => true,
            'data'    => $books,
        ], 200);
    }

    /**
     * [US-G3b] Get new arrival books (arrived within the last 30 days) in a category.
     *
     * GET /api/books/new-arrivals?category_id={id}&limit={n}
     *
     * - category_id  : optional, filter by category
     * - limit        : optional (default 10, max 50)
     */
    public function newArrivals(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'limit'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = $request->input('limit', 10);

        $books = Book::with('category:id,name,slug,color')
            ->select(['id', 'category_id', 'title', 'author', 'isbn',
                       'description', 'cover_image', 'published_year',
                       'publisher', 'language', 'total_copies', 'arrival_date'])
            ->where('arrival_date', '>=', now()->subDays(30)->toDateString())
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            })
            ->orderByDesc('arrival_date')
            ->limit($limit)
            ->get();

        $books->transform(function (Book $book) {
            $book->append(['total_borrows', 'is_new_arrival']);
            return $book;
        });

        return response()->json([
            'success' => true,
            'data'    => $books,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN FEATURES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * [US-A2a] Admin: Add a new book to a category.
     *
     * POST /api/admin/books
     * Requires: role = admin
     *
     * Body (JSON):
     *   category_id*, title*, author*, isbn, description,
     *   cover_image, published_year, publisher, language,
     *   total_copies, arrival_date
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent ajouter un livre.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'title'          => ['required', 'string', 'max:255'],
            'author'         => ['required', 'string', 'max:255'],
            'isbn'           => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'description'    => ['nullable', 'string'],
            'cover_image'    => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'publisher'      => ['nullable', 'string', 'max:255'],
            'language'       => ['nullable', 'string', 'max:50'],
            'total_copies'   => ['nullable', 'integer', 'min:1'],
            'arrival_date'   => ['nullable', 'date'],
        ]);

        $book = Book::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Livre ajouté avec succès.',
            'data'    => $book->load('category:id,name,slug'),
        ], 201);
    }

    /**
     * [US-A2b] Admin: Update an existing book.
     *
     * PUT /api/admin/books/{id}
     * Requires: role = admin
     *
     * Body (JSON): any fields from the books table (all optional)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent modifier un livre.',
            ], 403);
        }

        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'category_id'    => ['sometimes', 'integer', 'exists:categories,id'],
            'title'          => ['sometimes', 'string', 'max:255'],
            'author'         => ['sometimes', 'string', 'max:255'],
            'isbn'           => ['nullable', 'string', 'max:20', "unique:books,isbn,{$id}"],
            'description'    => ['nullable', 'string'],
            'cover_image'    => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'publisher'      => ['nullable', 'string', 'max:255'],
            'language'       => ['nullable', 'string', 'max:50'],
            'total_copies'   => ['nullable', 'integer', 'min:1'],
            'arrival_date'   => ['nullable', 'date'],
        ]);

        $book->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Livre mis à jour avec succès.',
            'data'    => $book->fresh()->load('category:id,name,slug'),
        ], 200);
    }

    /**
     * [US-A2c] Admin: Delete a book.
     *
     * DELETE /api/admin/books/{id}
     * Requires: role = admin
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent supprimer un livre.',
            ], 403);
        }

        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json([
            'success' => true,
            'message' => "Le livre \"{$book->title}\" a été supprimé avec succès.",
        ], 200);
    }
}
