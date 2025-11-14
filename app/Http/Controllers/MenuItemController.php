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
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');
        
        // Filter by special flag if provided
        if ($request->has('special')) {
            $query->where('special', filter_var($request->special, FILTER_VALIDATE_BOOLEAN));
        }
        
        $items = $query->orderByDesc('created_at')->get();

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
            'special' => 'boolean',
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
                'special' => $validated['special'] ?? false,
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
     * Update an existing menu item using POST request.
     */
    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        Log::info('========== UPDATE MENU ITEM STARTED ==========');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Menu Item ID: ' . $menuItem->id);
        Log::info('All Request Data:', $request->all());
        Log::info('Request Headers:', $request->headers->all());
        Log::info('Has File (image): ' . ($request->hasFile('image') ? 'YES' : 'NO'));
        
        try {
            Log::info('Starting validation...');
            
            // Validate the request
            $validated = $request->validate([
                'coffee_title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'single_price' => 'required|integer|min:0',
                'double_price' => 'required|integer|min:0',
                'available' => 'boolean',
                'special' => 'boolean',
                'portion_available' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            Log::info('Validation passed. Validated data:', $validated);

            // Handle category
            Log::info('Finding or creating category: ' . $validated['category']);
            $category = Category::firstOrCreate(
                ['name' => $validated['category']],
                ['name' => $validated['category']]
            );
            Log::info('Category ID: ' . $category->id);

            // Handle image upload if provided
            $imagePath = $menuItem->image_path; // Keep existing image by default
            if ($request->hasFile('image')) {
                Log::info('New image uploaded, processing...');
                
                // Delete old image if exists
                if ($menuItem->image_path) {
                    Log::info('Deleting old image: ' . $menuItem->image_path);
                    Storage::disk('public')->delete($menuItem->image_path);
                }
                
                $baseTitle = $validated['coffee_title'];
                $image = $request->file('image');
                $imageName = Str::slug($baseTitle) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('menu_images', $imageName, 'public');
                
                Log::info('New image saved: ' . $imagePath);
            } else {
                Log::info('No new image uploaded, keeping existing: ' . ($imagePath ?? 'none'));
            }

            // Prepare data for update
            $updateData = [
                'coffee_title' => $validated['coffee_title'],
                'category_id' => $category->id,
                'single_price' => (int) $validated['single_price'],
                'double_price' => (int) $validated['double_price'],
                'available' => isset($validated['available']) ? (bool) $validated['available'] : true,
                'special' => isset($validated['special']) ? (bool) $validated['special'] : false,
                'portion_available' => (int) $validated['portion_available'],
                'image_path' => $imagePath,
            ];

            Log::info('Update data prepared:', $updateData);
            
            // Update the menu item
            $menuItem->update($updateData);
            $menuItem->load('category');

            Log::info('Menu item updated successfully');
            Log::info('Updated menu item data:', $menuItem->toArray());
            Log::info('========== UPDATE MENU ITEM COMPLETED ==========');

            return response()->json([
                'success' => true,
                'message' => 'Menu item updated successfully',
                'data' => $menuItem,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('========== VALIDATION ERROR ==========');
            Log::error('Validation errors:', $e->errors());
            Log::error('========================================');
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('========== UPDATE ERROR ==========');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile());
            Log::error('Error line: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('===================================');
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu item: ' . $e->getMessage(),
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
