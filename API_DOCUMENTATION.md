# Barista Coffee App - API Documentation

## Overview

This document provides comprehensive API documentation for the Barista Coffee App backend. The API is built with Laravel and provides endpoints for managing coffee menu items and categories.

## Base URL

```
http://your-domain.com/api
```

## Authentication

Currently, the API has minimal authentication requirements:
- The `/user` endpoint requires `auth:sanctum` middleware
- Menu item endpoints (`/menu-items`) are currently **unprotected** and do not require authentication

## Response Format

All API responses follow a consistent JSON format:

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message (in development)"
}
```

## HTTP Status Codes

- `200` - OK (successful GET, PUT, PATCH)
- `201` - Created (successful POST)
- `422` - Unprocessable Entity (validation errors)
- `500` - Internal Server Error

---

## Endpoints

### 1. User Authentication

#### Get Current User
```http
GET /api/user
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2024-01-01T00:00:00.000000Z",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

---

### 2. Menu Items

#### Create Menu Item
```http
POST /api/menu-items
```

**Content-Type:** `multipart/form-data` (for file uploads) or `application/json`

**Request Body:**
```json
{
  "coffee_title": "Espresso",
  "category": "Hot Coffee",
  "single_price": 250,
  "double_price": 400,
  "available": true,
  "portion_available": 50,
  "image": "file" // Optional image file
}
```

**Field Descriptions:**
- `coffee_title` (required): Name of the coffee item (max 255 characters)
- `category` (required): Category name (max 255 characters) - will be created if it doesn't exist
- `single_price` (required): Price for single portion in cents (integer, min: 0)
- `double_price` (required): Price for double portion in cents (integer, min: 0)
- `available` (optional): Whether the item is available (boolean, defaults to true)
- `portion_available` (required): Number of portions available (integer, min: 0)
- `image` (optional): Image file (jpeg, png, jpg, gif, max 2MB)

**Success Response (201):**
```json
{
  "success": true,
  "message": "Menu item created successfully",
  "data": {
    "id": 1,
    "coffee_title": "Espresso",
    "single_price": 250,
    "double_price": 400,
    "available": true,
    "portion_available": 50,
    "image_path": "menu_images/espresso_1640995200.jpg",
    "image_url": "http://your-domain.com/storage/menu_images/espresso_1640995200.jpg",
    "category_id": 1,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "category": {
      "id": 1,
      "name": "Hot Coffee",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

**Error Response (422) - Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "coffee_title": ["The coffee title field is required."],
    "single_price": ["The single price must be an integer."]
  }
}
```

#### Update Menu Item
```http
PUT /api/menu-items/{id}
PATCH /api/menu-items/{id}
```

**Content-Type:** `multipart/form-data` (for file uploads) or `application/json`

**URL Parameters:**
- `id` (required): The ID of the menu item to update

**Request Body (all fields optional for partial updates):**
```json
{
  "coffee_title": "Updated Espresso",
  "category": "Premium Coffee",
  "single_price": 300,
  "double_price": 500,
  "available": false,
  "portion_available": 25,
  "image": "file" // Optional new image file
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Menu item updated successfully",
  "data": {
    "id": 1,
    "coffee_title": "Updated Espresso",
    "single_price": 300,
    "double_price": 500,
    "available": false,
    "portion_available": 25,
    "image_path": "menu_images/updated-espresso_1640995200.jpg",
    "image_url": "http://your-domain.com/storage/menu_images/updated-espresso_1640995200.jpg",
    "category_id": 2,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "category": {
      "id": 2,
      "name": "Premium Coffee",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

**Error Response (422) - No Fields Provided:**
```json
{
  "success": false,
  "message": "No valid fields provided"
}
```

---

## Data Models

### MenuItem
```json
{
  "id": 1,
  "coffee_title": "Espresso",
  "single_price": 250,
  "double_price": 400,
  "available": true,
  "portion_available": 50,
  "image_path": "menu_images/espresso_1640995200.jpg",
  "image_url": "http://your-domain.com/storage/menu_images/espresso_1640995200.jpg",
  "category_id": 1,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "category": {
    "id": 1,
    "name": "Hot Coffee",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Category
```json
{
  "id": 1,
  "name": "Hot Coffee",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

---

## Frontend Integration Examples

### JavaScript/Fetch API

#### Create Menu Item
```javascript
const createMenuItem = async (menuItemData) => {
  const formData = new FormData();
  
  // Add text fields
  formData.append('coffee_title', menuItemData.coffee_title);
  formData.append('category', menuItemData.category);
  formData.append('single_price', menuItemData.single_price);
  formData.append('double_price', menuItemData.double_price);
  formData.append('available', menuItemData.available);
  formData.append('portion_available', menuItemData.portion_available);
  
  // Add image file if provided
  if (menuItemData.image) {
    formData.append('image', menuItemData.image);
  }
  
  try {
    const response = await fetch('/api/menu-items', {
      method: 'POST',
      body: formData,
      // Don't set Content-Type header - let browser set it for FormData
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Menu item created:', result.data);
      return result.data;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error('Error creating menu item:', error);
    throw error;
  }
};
```

#### Update Menu Item
```javascript
const updateMenuItem = async (id, updateData) => {
  const formData = new FormData();
  
  // Add only the fields that need to be updated
  Object.keys(updateData).forEach(key => {
    if (updateData[key] !== undefined) {
      formData.append(key, updateData[key]);
    }
  });
  
  try {
    const response = await fetch(`/api/menu-items/${id}`, {
      method: 'PATCH',
      body: formData,
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Menu item updated:', result.data);
      return result.data;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error('Error updating menu item:', error);
    throw error;
  }
};
```

### React Example

```jsx
import React, { useState } from 'react';

const MenuItemForm = ({ onSubmit, initialData = {} }) => {
  const [formData, setFormData] = useState({
    coffee_title: initialData.coffee_title || '',
    category: initialData.category || '',
    single_price: initialData.single_price || '',
    double_price: initialData.double_price || '',
    available: initialData.available ?? true,
    portion_available: initialData.portion_available || '',
    image: null,
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const result = await onSubmit(formData);
      console.log('Success:', result);
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked, files } = e.target;
    
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : 
              type === 'file' ? files[0] : 
              value
    }));
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="coffee_title"
        value={formData.coffee_title}
        onChange={handleInputChange}
        placeholder="Coffee Title"
        required
      />
      
      <input
        type="text"
        name="category"
        value={formData.category}
        onChange={handleInputChange}
        placeholder="Category"
        required
      />
      
      <input
        type="number"
        name="single_price"
        value={formData.single_price}
        onChange={handleInputChange}
        placeholder="Single Price (cents)"
        required
        min="0"
      />
      
      <input
        type="number"
        name="double_price"
        value={formData.double_price}
        onChange={handleInputChange}
        placeholder="Double Price (cents)"
        required
        min="0"
      />
      
      <input
        type="number"
        name="portion_available"
        value={formData.portion_available}
        onChange={handleInputChange}
        placeholder="Portions Available"
        required
        min="0"
      />
      
      <label>
        <input
          type="checkbox"
          name="available"
          checked={formData.available}
          onChange={handleInputChange}
        />
        Available
      </label>
      
      <input
        type="file"
        name="image"
        onChange={handleInputChange}
        accept="image/jpeg,image/png,image/jpg,image/gif"
      />
      
      <button type="submit">Submit</button>
    </form>
  );
};
```

---

## Important Notes

### Image Handling
- Images are stored in the `storage/app/public/menu_images/` directory
- The `image_url` field provides the full public URL to access the image
- Make sure to run `php artisan storage:link` on the server to create the symbolic link
- Supported formats: JPEG, PNG, JPG, GIF
- Maximum file size: 2MB

### Price Format
- All prices are stored in **cents** (not dollars)
- Example: $2.50 should be sent as `250`

### Category Management
- Categories are automatically created when a new category name is provided
- Categories are linked to menu items via `category_id`

### Error Handling
- Always check the `success` field in responses
- Validation errors will be returned in the `errors` object
- Server errors will include an `error` field with details

### CORS
- If making requests from a different domain, ensure CORS is properly configured
- The API should include appropriate CORS headers

---

## Testing the API

You can test the API using tools like:
- **Postman**
- **Insomnia**
- **curl** commands
- **Browser Developer Tools**

### Example curl commands:

#### Create Menu Item
```bash
curl -X POST http://your-domain.com/api/menu-items \
  -F "coffee_title=Test Coffee" \
  -F "category=Test Category" \
  -F "single_price=300" \
  -F "double_price=500" \
  -F "available=true" \
  -F "portion_available=10" \
  -F "image=@/path/to/image.jpg"
```

#### Update Menu Item
```bash
curl -X PATCH http://your-domain.com/api/menu-items/1 \
  -F "coffee_title=Updated Coffee" \
  -F "single_price=350"
```

---

## Support

For any questions or issues with the API, please contact the backend development team.
