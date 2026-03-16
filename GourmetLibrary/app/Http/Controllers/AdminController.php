<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrow;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * [US-A3] Collection Statistics dashboard for the admin.
     *
     * GET /api/admin/stats
     * Requires: role = admin
     *
     * Returns:
     *  - total_books         : number of unique titles in the library
     *  - total_copies        : total physical book copies
     *  - available_copies    : copies currently not borrowed
     *  - borrowed_copies     : copies currently on loan
     *  - degraded_copies     : copies in 'degraded' or 'damaged' condition
     *  - lost_copies         : copies marked 'lost'
     *  - total_borrows       : all-time borrow records
     *  - active_borrows      : currently active borrows
     *  - most_borrowed_books : top 5 most borrowed books
     *  - top_categories      : top 5 categories by number of books
     */
    public function stats(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent accéder aux statistiques.',
            ], 403);
        }

        // ── Global counts ───────────────────────────────────────────────────
        $totalBooks       = Book::count();
        $totalCopies      = BookCopy::count();
        $availableCopies  = BookCopy::where('is_available', true)->count();
        $borrowedCopies   = BookCopy::where('is_available', false)->count();
        $degradedCopies   = BookCopy::whereIn('condition', ['degraded', 'damaged'])->count();
        $lostCopies       = BookCopy::where('condition', 'lost')->count();
        $totalBorrows     = Borrow::count();
        $activeBorrows    = Borrow::where('status', 'active')->count();
        $overdueBorrows   = Borrow::where('status', 'overdue')->count();
        $totalCategories  = Category::count();

        // ── Top 5 most-borrowed books ────────────────────────────────────────
        $mostBorrowedBooks = Book::with('category:id,name')
            ->withCount(['borrows as borrow_count'])
            ->orderByDesc('borrow_count')
            ->limit(5)
            ->get(['id', 'title', 'author', 'category_id']);

        // ── Top 5 categories by book count ──────────────────────────────────
        $topCategories = Category::withCount('books')
            ->orderByDesc('books_count')
            ->limit(5)
            ->get(['id', 'name', 'slug', 'color']);

        return response()->json([
            'success' => true,
            'data'    => [
                'collection' => [
                    'total_books'      => $totalBooks,
                    'total_categories' => $totalCategories,
                    'total_copies'     => $totalCopies,
                    'available_copies' => $availableCopies,
                    'borrowed_copies'  => $borrowedCopies,
                    'degraded_copies'  => $degradedCopies,
                    'lost_copies'      => $lostCopies,
                ],
                'borrows' => [
                    'total'    => $totalBorrows,
                    'active'   => $activeBorrows,
                    'overdue'  => $overdueBorrows,
                ],
                'most_borrowed_books' => $mostBorrowedBooks,
                'top_categories'      => $topCategories,
            ],
        ], 200);
    }

    /**
     * [US-A4] Degraded copies report — per book.
     *
     * GET /api/admin/degraded-copies
     * Requires: role = admin
     *
     * Returns a list of books that have at least one degraded or damaged copy,
     * with the exact count and details of those copies per book.
     * Useful for planning repair or replacement.
     *
     * Optional query params:
     *   - category_id  : filter by category
     *   - condition    : filter by exact condition ('degraded' or 'damaged')
     */
    public function degradedCopies(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent voir ce rapport.',
            ], 403);
        }

        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'condition'   => ['nullable', 'in:degraded,damaged'],
        ]);

        // Determine which conditions to look for
        $conditions = $request->filled('condition')
            ? [$request->input('condition')]
            : ['degraded', 'damaged'];

        // Books that have at least one copy matching the condition
        $books = Book::with([
                'category:id,name,slug',
                'copies' => function ($q) use ($conditions) {
                    $q->whereIn('condition', $conditions)
                      ->orderBy('condition');
                },
            ])
            ->withCount([
                'copies as degraded_count' => function ($q) use ($conditions) {
                    $q->whereIn('condition', $conditions);
                },
            ])
            ->having('degraded_count', '>', 0)    // only books with at least 1
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            })
            ->orderByDesc('degraded_count')
            ->get(['id', 'category_id', 'title', 'author', 'isbn']);

        return response()->json([
            'success'           => true,
            'conditions_filter' => $conditions,
            'total_books_affected' => $books->count(),
            'data'              => $books,
        ], 200);
    }
}
