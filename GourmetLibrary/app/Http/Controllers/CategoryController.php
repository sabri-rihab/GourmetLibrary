<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * [US-A1a] Admin: Create a new category.
     *
     * POST /api/admin/categories
     * Requires: role = admin
     *
     * Body (JSON):
     *   name*        : unique name, ex: "Pâtisserie Française"
     *   slug         : auto-generated from name if not supplied
     *   description  : optional text
     *   color        : optional hex color (#rrggbb), default #6366f1
     */
    public function create(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent créer une catégorie.',
            ], 403);
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès.',
            'data'    => $category,
        ], 201);
    }

    /**
     * [US-G1 / public] Show all categories.
     *
     * GET /api/auth/categories   (public, no token required)
     *
     * Returns all categories with the count of books in each.
     */
    public function show(Request $request): JsonResponse
    {
        $categories = Category::withCount('books')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ], 200);
    }

    /**
     * [US-A1b] Admin: Update an existing category.
     *
     * PUT /api/admin/categories/{id}
     * Requires: role = admin
     *
     * Body (JSON): any combination of name, slug, description, color
     * The slug is automatically re-generated if name changes and slug is not provided.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent modifier une catégorie.',
            ], 403);
        }

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => ["sometimes", 'string', 'max:255', "unique:categories,name,{$id}"],
            'slug'        => ['nullable', 'string', 'max:255', "unique:categories,slug,{$id}"],
            'description' => ['nullable', 'string', 'max:500'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // If name changed but no new slug provided, regenerate slug
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès.',
            'data'    => $category->fresh(),
        ], 200);
    }

    /**
     * [US-A1c] Admin: Delete a category.
     *
     * DELETE /api/admin/categories/{id}
     * Requires: role = admin
     *
     * ⚠ Warning: deleting a category cascades and removes all its books (and their copies / borrows).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé : seuls les Chefs Bibliothécaires peuvent supprimer une catégorie.',
            ], 403);
        }

        $category = Category::findOrFail($id);
        $name     = $category->name;
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "La catégorie \"{$name}\" a été supprimée avec succès.",
        ], 200);
    }
}