<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
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
}
