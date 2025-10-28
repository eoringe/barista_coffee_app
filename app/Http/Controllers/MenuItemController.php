<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the menu items.
     */
    public function index(): JsonResponse
    {
        $items = MenuItem::with('category')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Menu items fetched successfully',
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created menu item in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'coffee_title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'single_price' => 'required|integer|min:0',
            'double_price' => 'required|integer|min:0',
            'available' => 'boolean',
            'portion_available' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Find or create the category
            $category = Category::firstOrCreate(
                ['name' => $validated['category']],
                ['name' => $validated['category']]
            );

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = Str::slug($validated['coffee_title']) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('menu_images', $imageName, 'public');
            }

            // Create the menu item
            $menuItem = MenuItem::create([
                'coffee_title' => $validated['coffee_title'],
                'single_price' => $validated['single_price'],
                'double_price' => $validated['double_price'],
                'available' => $validated['available'] ?? true,
                'portion_available' => $validated['portion_available'],
                'image_path' => $imagePath,
                'category_id' => $category->id,
            ]);

            // Load the category relationship for the response
            $menuItem->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Menu item created successfully',
                'data' => $menuItem,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing menu item (partial updates supported).
     */
    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $validated = $request->validate([
            'coffee_title' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'single_price' => 'sometimes|integer|min:0',
            'double_price' => 'sometimes|integer|min:0',
            'available' => 'sometimes|boolean',
            'portion_available' => 'sometimes|integer|min:0',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            Log::info('MenuItem update called', [
                'menu_item_id' => $menuItem->id,
                'inputs' => $request->all(),
                'has_file_image' => $request->hasFile('image'),
            ]);
            // Update or relink category if provided
            if (array_key_exists('category', $validated)) {
                $category = Category::firstOrCreate([
                    'name' => $validated['category'],
                ], [
                    'name' => $validated['category'],
                ]);
                $menuItem->category_id = $category->id;
            } elseif (array_key_exists('category_id', $validated)) {
                $menuItem->category_id = (int) $validated['category_id'];
            }

            // Replace image if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($menuItem->image_path) {
                    Storage::disk('public')->delete($menuItem->image_path);
                }

                $baseTitle = $validated['coffee_title'] ?? $menuItem->coffee_title;
                $image = $request->file('image');
                $imageName = Str::slug($baseTitle) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $menuItem->image_path = $image->storeAs('menu_images', $imageName, 'public');
            }

            // Explicitly assign scalar fields with coercion from form-data
            if (array_key_exists('coffee_title', $validated)) {
                $menuItem->coffee_title = $validated['coffee_title'];
            }
            if (array_key_exists('single_price', $validated)) {
                $menuItem->single_price = (int) $validated['single_price'];
            }
            if (array_key_exists('double_price', $validated)) {
                $menuItem->double_price = (int) $validated['double_price'];
            }
            if ($request->has('available')) {
                $bool = filter_var($request->input('available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $menuItem->available = $bool === null ? (bool) $request->input('available') : $bool;
            }
            if (array_key_exists('portion_available', $validated)) {
                $menuItem->portion_available = (int) $validated['portion_available'];
            }

            $menuItem->save();

            $menuItem->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => $menuItem,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(MenuItem $menuItem): JsonResponse
    {
        try {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            $menuItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
