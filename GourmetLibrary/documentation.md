# GourmetLibrary — API Documentation

> **Base URL** : `http://localhost:8000/api`
> **Format**   : All requests and responses use `Content-Type: application/json`
> **Auth**     : Protected routes require `Authorization: Bearer {token}` header (Sanctum token obtained via `/api/auth/login`)

---

## Table of Contents

| #   | Role      | Feature                               | Method | Endpoint                          |
|-----|-----------|---------------------------------------|--------|-----------------------------------|
| G1  | Gourmand  | Browse books by category              | GET    | `/books/category/{identifier}`    |
| G2  | Gourmand  | Search books                          | GET    | `/books/search`                   |
| G3a | Gourmand  | Most popular books                    | GET    | `/books/popular`                  |
| G3b | Gourmand  | New arrivals                          | GET    | `/books/new-arrivals`             |
| SLUG| Gourmand  | Book detail via readable URL          | GET    | `/{categorySlug}/livres/{bookSlug}` |
| A1a | Admin     | Create a category                     | POST   | `/admin/categories`               |
| A1b | Admin     | Update a category                     | PUT    | `/admin/categories/{id}`          |
| A1c | Admin     | Delete a category                     | DELETE | `/admin/categories/{id}`          |
| A2a | Admin     | Add a book                            | POST   | `/admin/books`                    |
| A2b | Admin     | Edit a book                           | PUT    | `/admin/books/{id}`               |
| A2c | Admin     | Delete a book                         | DELETE | `/admin/books/{id}`               |
| A3  | Admin     | Collection statistics                 | GET    | `/admin/stats`                    |
| A4  | Admin     | Degraded copies report                | GET    | `/admin/degraded-copies`          |

---

## Prerequisites — Authentication

Before calling any protected route you must log in to get a token.

### Register (public)
```
POST /api/auth/register
```
**Postman body (raw JSON):**
```json
{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
**Response (201):**
```json
{ "success": true, "message": "Compte créé avec succès. Vous pouvez maintenant vous connecter." }
```

### Login (public)
```
POST /api/auth/login
```
**Postman body (raw JSON):**
```json
{
  "email": "jean@example.com",
  "password": "password123"
}
```
**Response (200):**
```json
{
  "user": { "name": "Jean Dupont", "email": "jean@example.com", "role": "gourmand" },
  "token": "3|abc123..."
}
```
> Copy the `token` value and add it as `Authorization: Bearer 3|abc123...` in Postman's **Auth** tab (type = Bearer Token).

---

## Gourmand Features

### [G1] Browse books in a specific category

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/books/category/{identifier}` |
| **Controller** | `app/Http/Controllers/BookController.php` → `byCategory()` |
| **Model used** | `Category`, `Book` |
| **Auth**       | ✅ Bearer token required |

**How it works:**
1. The `{identifier}` can be either a **numeric ID** (e.g. `GET /api/books/category/2`) or a **slug** (e.g. `GET /api/books/category/patisserie-francaise`).
2. The method fetches the corresponding `Category` record.
3. It then loads all `Book` records that belong to that category (`category->books()` relationship).
4. Results are **paginated** (15 books per page). Add `?page=2` for the next page.
5. Each book gets two computed attributes appended:
   - `total_borrows` — how many times this book has been borrowed (from `getTotalBorrowsAttribute()` in `Book.php`)
   - `is_new_arrival` — `true` if `arrival_date` is within the last 30 days (from `getIsNewArrivalAttribute()` in `Book.php`)

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/books/category/1`  (or use the slug like `/books/category/patisserie-francaise`)
- Headers: `Authorization: Bearer {token}`
- No body needed.

**Success response (200):**
```json
{
  "success": true,
  "category": { "id": 1, "name": "Pâtisserie Française", "slug": "patisserie-francaise", "description": "...", "color": "#6366f1" },
  "data": {
    "current_page": 1,
    "data": [
      { "id": 3, "title": "Le Grand Livre de la Pâtisserie", "author": "Pierre Hermé", "total_borrows": 12, "is_new_arrival": false, ... }
    ],
    "total": 8, "per_page": 15, ...
  }
}
```

---

### [G2] Search books by title, author, or category

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/books/search?q={query}` |
| **Controller** | `app/Http/Controllers/BookController.php` → `search()` |
| **Model used** | `Book`, `Category` |
| **Auth**       | ✅ Bearer token required |

**How it works:**
1. Validates that `q` is provided (min 2 characters).
2. Builds a query that searches three fields using SQL `LIKE %query%`:
   - `books.title`
   - `books.author`
   - `categories.name` (via `whereHas('category', ...)` — a sub-query joining the categories table)
3. Eager-loads the `category` relationship so each book result contains its category name, slug, and color.
4. Results are paginated (15 per page).

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/books/search?q=chocolat`
- Headers: `Authorization: Bearer {token}`
- No body needed.

**Success response (200):**
```json
{
  "success": true,
  "search_query": "chocolat",
  "data": {
    "current_page": 1,
    "data": [
      { "id": 7, "title": "L'Art du Chocolat", "author": "Christophe Michalak", "category": { "name": "Pâtisserie Française", ... }, ... }
    ],
    "total": 3, "per_page": 15, ...
  }
}
```

---

### [G3a] Most popular books (most borrowed)

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/books/popular` |
| **Controller** | `app/Http/Controllers/BookController.php` → `popular()` |
| **Model used** | `Book`, `Borrow`, `BookCopy` |
| **Auth**       | ✅ Bearer token required |

**How it works:**
1. Uses `withCount(['borrows as borrow_count'])` — this calls the `borrows()` relationship on `Book`, which is a `hasManyThrough(Borrow, BookCopy)` relationship. Laravel counts all borrow records linked to a book via its copies.
2. Optionally filters by `category_id`.
3. Orders the results `DESC` by `borrow_count`.
4. Returns the top N books (default 10, configurable with `limit` up to 50).

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/books/popular?limit=5&category_id=2`
- Headers: `Authorization: Bearer {token}`
- Query params:
  - `limit` (optional, default 10, max 50)
  - `category_id` (optional, filter to one category)

**Success response (200):**
```json
{
  "success": true,
  "data": [
    { "id": 5, "title": "Ma Cuisine", "author": "Paul Bocuse", "borrow_count": 24, "category": { "name": "Cuisine Française" }, "is_new_arrival": false }
  ]
}
```

---

### [G3b] New arrivals (books added in the last 30 days)

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/books/new-arrivals` |
| **Controller** | `app/Http/Controllers/BookController.php` → `newArrivals()` |
| **Model used** | `Book` |
| **Auth**       | ✅ Bearer token required |

**How it works:**
1. Filters books where `arrival_date >= today - 30 days` using `now()->subDays(30)->toDateString()`.
2. Optionally filters by `category_id`.
3. Orders by `arrival_date DESC` (most recent first).
4. Returns top N books (default 10, max 50).

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/books/new-arrivals?category_id=1`
- Headers: `Authorization: Bearer {token}`
- Query params:
  - `limit` (optional)
  - `category_id` (optional)

**Success response (200):**
```json
{
  "success": true,
  "data": [
    { "id": 18, "title": "Zéro Gluten", "author": "Maëlle Pichon", "arrival_date": "2026-03-10", "is_new_arrival": true, ... }
  ]
}
```

---

### [SLUG] Human-readable book detail URL

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/{categorySlug}/livres/{bookSlug}` |
| **Controller** | `app/Http/Controllers/BookController.php` → `showBySlug()` |
| **Model used** | `Book`, `Category`, `BookCopy` |
| **Migration**  | `database/migrations/2026_03_16_..._add_slug_to_books_table.php` |
| **Auth**       | ✅ Bearer token required |

**What is a slug?**

A slug is a short, URL-safe version of a text string. Special characters, accents and spaces are removed or replaced:

| Original text | Slug generated |
|---|---|
| `Cuisine Italienne` | `cuisine-italienne` |
| `Les Meilleures Recettes de Pâtes` | `les-meilleures-recettes-de-pates` |
| `Pâtisserie Française` | `patisserie-francaise` |

This makes URLs **readable for humans** instead of using raw IDs like `/books/47`.

**Why was this added?**

Instead of saying `GET /api/books/47`, you can now say:
```
GET /api/cuisine-italienne/livres/les-meilleures-recettes-de-pates
```
This URL tells you immediately: category = *Cuisine Italienne*, book = *Les Meilleures Recettes de Pâtes*. It is better for APIs consumed by frontends, and matches the format `/{category-slug}/livres/{book-slug}` requested.

**What was added to make it work:**

| Layer | File | What changed |
|-------|------|--------------|
| **Migration** | `2026_03_16_..._add_slug_to_books_table.php` | Adds a `slug` VARCHAR column (unique) to the `books` table. Also **backfills** existing books: generates slugs from their titles, appending `-2`, `-3`, etc. if two books share the same title. |
| **Model** | `app/Models/Book.php` | Added `slug` to `$fillable`. Added a `booted()` method with two Eloquent hooks: (1) `creating` — auto-generates a unique slug from `title` when a book is created without an explicit slug; (2) `updating` — re-generates the slug automatically if `title` changes and no manual `slug` was provided in the update payload. |
| **Controller** | `app/Http/Controllers/BookController.php` | New `showBySlug(string $categorySlug, string $bookSlug)` method. Finds the category by slug, then finds the book inside it by slug. Returns full book detail + computed fields. |
| **Route** | `routes/api.php` | `Route::get('/{categorySlug}/livres/{bookSlug}', ...)` registered inside the `auth:sanctum` middleware group. |

**How `showBySlug()` works step by step:**
1. Receives two URL parameters: `$categorySlug` (e.g. `cuisine-italienne`) and `$bookSlug` (e.g. `les-meilleures-recettes-de-pates`).
2. Calls `Category::where('slug', $categorySlug)->firstOrFail()` — if the category slug doesn't exist, Laravel automatically returns **404 Not Found**.
3. Calls `Book::where('category_id', $category->id)->where('slug', $bookSlug)->firstOrFail()` — this scopes the book search inside its category, preventing slug collisions across categories.
4. Calls `$book->availableCopies()->count()` to return how many physical copies can be borrowed right now.
5. Returns the full book object with `total_borrows`, `is_new_arrival`, `available_copies`, and the `canonical_url` field (the exact URL used to reach this book).

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/cuisine-italienne/livres/les-meilleures-recettes-de-pates`
- Headers: `Authorization: Bearer {token}`
- No body needed.

> **How to know the slug?** Call `GET /api/books/category/cuisine-italienne` first — each book in the response has a `slug` field. Use that slug here.

**Success response (200):**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "category_id": 3,
    "title": "Les Meilleures Recettes de Pâtes",
    "slug": "les-meilleures-recettes-de-pates",
    "author": "Carlo Marchetti",
    "isbn": "9782012345678",
    "description": "Un voyage culinaire en Italie...",
    "published_year": 2019,
    "publisher": "Marabout",
    "language": "Français",
    "total_copies": 4,
    "arrival_date": "2025-11-01",
    "category": {
      "id": 3,
      "name": "Cuisine Italienne",
      "slug": "cuisine-italienne",
      "color": "#22c55e",
      "description": "La dolce vita dans votre assiette"
    },
    "total_borrows": 18,
    "is_new_arrival": false,
    "available_copies": 2,
    "canonical_url": "/api/cuisine-italienne/livres/les-meilleures-recettes-de-pates"
  }
}
```

**Error response — wrong slug (404):**
```json
{ "message": "No query results for model [App\\Models\\Book]." }
```

---

## Admin Features

> All admin routes require:
> - A valid Bearer token (logged-in user)
> - The user's `role` field to be `"admin"` — if not, you get a **403 Forbidden** response.

### [A1a] Create a category

| What     | Detail |
|----------|--------|
| **Route**      | `POST /api/admin/categories` |
| **Controller** | `app/Http/Controllers/CategoryController.php` → `create()` |
| **Model used** | `Category` |
| **Migration**  | `database/migrations/2024_01_01_100000_create_categories_table.php` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Validates the input. `name` is required and must be unique in the `categories` table.
2. `slug` is optional — if not provided, the `Category` model auto-generates it from `name` using `Str::slug()` in its `booted()` method.
3. `color` must be a valid 6-digit hex code like `#e63946`.
4. Calls `Category::create($validated)` to insert the record.

**Postman setup:**
- Method: `POST`
- URL: `{{base_url}}/admin/categories`
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body (raw JSON):**
```json
{
  "name": "Sans Gluten",
  "description": "Recettes pour les intolérants au gluten",
  "color": "#e63946"
}
```
*(Optional fields: `slug`, `description`, `color`)*

**Success response (201):**
```json
{
  "success": true,
  "message": "Catégorie créée avec succès.",
  "data": { "id": 9, "name": "Sans Gluten", "slug": "sans-gluten", "description": "...", "color": "#e63946", ... }
}
```

---

### [A1b] Update a category

| What     | Detail |
|----------|--------|
| **Route**      | `PUT /api/admin/categories/{id}` |
| **Controller** | `app/Http/Controllers/CategoryController.php` → `update()` |
| **Model used** | `Category` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Finds the category by `{id}` (`findOrFail` — returns 404 if not found).
2. Validates only the fields sent (`sometimes` rule = only validate if present).
3. If `name` is changed but no new `slug` is given, the slug is **automatically regenerated** from the new name.
4. Updates the record with `$category->update($validated)`.

**Postman setup:**
- Method: `PUT`
- URL: `{{base_url}}/admin/categories/9`
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body (raw JSON) — only include fields you want to change:**
```json
{
  "name": "Sans Gluten & Vegan",
  "color": "#2ec4b6"
}
```

**Success response (200):**
```json
{
  "success": true,
  "message": "Catégorie mise à jour avec succès.",
  "data": { "id": 9, "name": "Sans Gluten & Vegan", "slug": "sans-gluten-vegan", "color": "#2ec4b6", ... }
}
```

---

### [A1c] Delete a category

| What     | Detail |
|----------|--------|
| **Route**      | `DELETE /api/admin/categories/{id}` |
| **Controller** | `app/Http/Controllers/CategoryController.php` → `destroy()` |
| **Model used** | `Category` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Checks the user's role.
2. Finds the category (`findOrFail`).
3. Calls `$category->delete()`.
4. Because the `books` migration uses `cascadeOnDelete()` on `category_id`, all books in this category (and their copies and borrows) are **automatically deleted** by the database.

> [!CAUTION]
> Deleting a category deletes all of its books, copies, and borrow records. This cannot be undone.

**Postman setup:**
- Method: `DELETE`
- URL: `{{base_url}}/admin/categories/9`
- Headers: `Authorization: Bearer {token}`
- No body needed.

**Success response (200):**
```json
{
  "success": true,
  "message": "La catégorie \"Sans Gluten & Vegan\" a été supprimée avec succès."
}
```

---

### [A2a] Add a book

| What     | Detail |
|----------|--------|
| **Route**      | `POST /api/admin/books` |
| **Controller** | `app/Http/Controllers/BookController.php` → `store()` |
| **Model used** | `Book` |
| **Migration**  | `database/migrations/2024_01_01_100001_create_books_table.php` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Validates all book fields. `category_id`, `title`, and `author` are required.
2. `isbn` must be unique across the `books` table if provided.
3. Calls `Book::create($validated)` to insert the record.
4. Returns the new book with its category (eager-loaded via `load('category:id,name,slug')`).

**Postman setup:**
- Method: `POST`
- URL: `{{base_url}}/admin/books`
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body (raw JSON):**
```json
{
  "category_id": 1,
  "title": "La Bible de la Pâtisserie",
  "author": "Ferrandi Paris",
  "isbn": "9782081411654",
  "description": "Le manuel de référence des pâtissiers professionnels.",
  "published_year": 2020,
  "publisher": "Flammarion",
  "language": "Français",
  "total_copies": 3,
  "arrival_date": "2026-03-01"
}
```
*(Required: `category_id`, `title`, `author`. All other fields are optional.)*

**Success response (201):**
```json
{
  "success": true,
  "message": "Livre ajouté avec succès.",
  "data": { "id": 25, "title": "La Bible de la Pâtisserie", "category": { "id": 1, "name": "Pâtisserie Française", ... }, ... }
}
```

---

### [A2b] Edit a book

| What     | Detail |
|----------|--------|
| **Route**      | `PUT /api/admin/books/{id}` |
| **Controller** | `app/Http/Controllers/BookController.php` → `update()` |
| **Model used** | `Book` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Finds the book by `{id}` (`findOrFail`).
2. All fields use `sometimes` — only the fields you send are validated and updated.
3. For `isbn` uniqueness, the current book's `id` is excluded from the check (so you can keep the same ISBN without triggering a unique error).
4. Calls `$book->update($validated)`, then returns `$book->fresh()` (the refreshed record from DB) with its category.

**Postman setup:**
- Method: `PUT`
- URL: `{{base_url}}/admin/books/25`
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body (raw JSON) — only include fields you want to change:**
```json
{
  "total_copies": 5,
  "description": "Édition mise à jour avec 50 nouvelles recettes."
}
```

**Success response (200):**
```json
{
  "success": true,
  "message": "Livre mis à jour avec succès.",
  "data": { "id": 25, "total_copies": 5, "description": "Édition mise à jour ...", ... }
}
```

---

### [A2c] Delete a book

| What     | Detail |
|----------|--------|
| **Route**      | `DELETE /api/admin/books/{id}` |
| **Controller** | `app/Http/Controllers/BookController.php` → `destroy()` |
| **Model used** | `Book` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
1. Finds the book (`findOrFail`).
2. Calls `$book->delete()`.
3. Because `book_copies` migration uses `cascadeOnDelete()` on `book_id`, all physical copies (and their borrow records) are automatically deleted.

**Postman setup:**
- Method: `DELETE`
- URL: `{{base_url}}/admin/books/25`
- Headers: `Authorization: Bearer {token}`
- No body needed.

**Success response (200):**
```json
{
  "success": true,
  "message": "Le livre \"La Bible de la Pâtisserie\" a été supprimé avec succès."
}
```

---

### [A3] Collection Statistics

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/admin/stats` |
| **Controller** | `app/Http/Controllers/AdminController.php` → `stats()` |
| **Models used**| `Book`, `BookCopy`, `Borrow`, `Category` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**
The method runs several database count queries:

| Query                            | What it counts |
|----------------------------------|----------------|
| `Book::count()`                  | Total unique book titles |
| `BookCopy::count()`              | Total physical copies in the library |
| `BookCopy::where('is_available', true)->count()` | Copies not currently borrowed |
| `BookCopy::where('is_available', false)->count()` | Copies currently on loan |
| `BookCopy::whereIn('condition', ['degraded','damaged'])->count()` | Copies in bad condition |
| `BookCopy::where('condition', 'lost')->count()` | Lost copies |
| `Borrow::count()`                | All-time borrow records |
| `Borrow::where('status','active')->count()` | Currently active borrows |
| `Borrow::where('status','overdue')->count()` | Overdue borrows |
| `Category::count()`              | Total categories |

It also runs two ranked queries:
- **Top 5 most borrowed books**: Uses `withCount(['borrows as borrow_count'])` — leverages the `hasManyThrough(Borrow, BookCopy)` relationship on `Book`.
- **Top 5 categories by book count**: Uses `withCount('books')` on `Category`.

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/admin/stats`
- Headers: `Authorization: Bearer {token}`
- No body needed.

**Success response (200):**
```json
{
  "success": true,
  "data": {
    "collection": {
      "total_books": 45,
      "total_categories": 8,
      "total_copies": 120,
      "available_copies": 98,
      "borrowed_copies": 22,
      "degraded_copies": 7,
      "lost_copies": 2
    },
    "borrows": {
      "total": 312,
      "active": 22,
      "overdue": 3
    },
    "most_borrowed_books": [
      { "id": 5, "title": "Ma Cuisine", "author": "Paul Bocuse", "borrow_count": 24, "category": { "name": "Cuisine Française" } }
    ],
    "top_categories": [
      { "id": 1, "name": "Pâtisserie Française", "books_count": 14 }
    ]
  }
}
```

---

### [A4] Degraded Copies Report

| What     | Detail |
|----------|--------|
| **Route**      | `GET /api/admin/degraded-copies` |
| **Controller** | `app/Http/Controllers/AdminController.php` → `degradedCopies()` |
| **Models used**| `Book`, `BookCopy` |
| **Migration**  | `database/migrations/2024_01_01_100002_create_book_copies_table.php` |
| **Auth**       | ✅ Bearer token + role = admin |

**How it works:**

The `book_copies` table has a `condition` ENUM column with 4 values:
- `good` — perfect condition
- `degraded` — stained pages or slightly damaged binding
- `damaged` — heavily damaged (unusable)
- `lost` — physically missing

This endpoint:
1. Looks for copies where `condition IN ('degraded', 'damaged')` (can be filtered to one with `?condition=degraded` or `?condition=damaged`).
2. Uses `withCount(['copies as degraded_count' => ...])` to count degraded copies per book.
3. Uses `having('degraded_count', '>', 0)` to exclude books with zero degraded copies.
4. Eager-loads the copies details (`copy_number`, `condition`, `condition_notes`) so the admin can see exactly which physical copies need attention.
5. Optionally filters by `category_id`.
6. Orders by `degraded_count DESC` (worst first).

**Postman setup:**
- Method: `GET`
- URL: `{{base_url}}/admin/degraded-copies`
- Headers: `Authorization: Bearer {token}`
- No body needed.
- Optional query params:
  - `?condition=degraded` — only degraded copies (not damaged)
  - `?condition=damaged` — only damaged copies
  - `?category_id=2` — filter to a specific category

**Success response (200):**
```json
{
  "success": true,
  "conditions_filter": ["degraded", "damaged"],
  "total_books_affected": 4,
  "data": [
    {
      "id": 12,
      "title": "Escoffier : Ma Cuisine",
      "author": "Auguste Escoffier",
      "isbn": "9782710508519",
      "category": { "id": 3, "name": "Cuisine Française Classique", "slug": "cuisine-francaise-classique" },
      "degraded_count": 3,
      "copies": [
        { "id": 22, "copy_number": "COPY-001", "condition": "damaged",  "condition_notes": "Couverture arrachée", "is_available": false },
        { "id": 23, "copy_number": "COPY-002", "condition": "degraded", "condition_notes": "Taches de café sur pages 45-52", "is_available": true }
      ]
    }
  ]
}
```

---

## Database Structure Overview

```
categories          (id, name, slug, description, color)
    │
    └── books       (id, category_id, title, slug, author, isbn, description,
    │               cover_image, published_year, publisher, language,
    │               total_copies, arrival_date)   ← slug added by migration
    │
    └── book_copies (id, book_id, copy_number, condition, condition_notes, is_available)
                    condition ENUM: 'good' | 'degraded' | 'damaged' | 'lost'
                        │
                        └── borrows (id, user_id, book_copy_id, borrowed_at,
                                    due_date, returned_at, status, return_notes)
                                    status ENUM: 'active' | 'returned' | 'overdue' | 'lost'

users               (id, name, email, password, role)
                    role ENUM: 'gourmand' | 'admin'
```

---

## Files Created / Modified Summary

| File | Action | Purpose |
|------|--------|---------|
| `app/Http/Controllers/BookController.php` | **Updated** | Added `showBySlug()` (SLUG feature); `slug` included in all `select()` projections |
| `app/Http/Controllers/AdminController.php` | **Created** | Statistics (A3) and degraded copies report (A4) |
| `app/Http/Controllers/CategoryController.php` | **Updated** | Added `update()` method (A1b), added role guard to `create()`, fixed `destroy()` to actually delete |
| `app/Models/Book.php` | **Updated** | Added `slug` to `$fillable`, added `booted()` auto-slug hooks |
| `database/migrations/2026_03_16_..._add_slug_to_books_table.php` | **Created** | Adds `slug` column + backfills existing rows |
| `routes/api.php` | **Updated** | All new routes including slug URL pattern |

All other models (`BookCopy`, `Borrow`, `Category`, `User`) and all original migrations were **already in place** and did not need modification.
