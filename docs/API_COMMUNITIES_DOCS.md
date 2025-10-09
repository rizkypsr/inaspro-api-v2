# Communities API Documentation

This document provides comprehensive information about the Communities API endpoints, including request/response examples and error handling.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All community endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

---

## Response Format
All API responses follow this consistent JSON structure:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data here
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors or error details here
    }
}
```

---

## Endpoints

### 1. List Communities
Get a paginated list of communities that the authenticated user can access.

**Endpoint:** `GET /communities`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/communities?page=1&per_page=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Communities retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Tech Enthusiasts",
        "description": "A community for technology lovers and professionals",
        "profile_image_url": "https://example.com/images/tech-community.jpg",
        "is_private": false,
        "created_by": 1,
        "created_at": "2025-10-08T10:00:00.000000Z",
        "updated_at": "2025-10-08T10:00:00.000000Z",
        "creator": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "members_count": 25,
        "posts_count": 12
      }
    ],
    "first_page_url": "http://localhost:8000/api/communities?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/communities?page=1",
    "next_page_url": null,
    "path": "http://localhost:8000/api/communities",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

### 2. Create Community
Create a new community.

**Endpoint:** `POST /communities`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Tech Enthusiasts",
  "description": "A community for technology lovers and professionals",
  "profile_image_url": "https://example.com/images/tech-community.jpg",
  "is_private": false
}
```

**Field Descriptions:**
- `name` (required): Community name (max 255 characters)
- `description` (optional): Community description
- `profile_image_url` (optional): URL to community profile image
- `is_private` (optional): Whether the community is private (default: false)

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/communities" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Enthusiasts",
    "description": "A community for technology lovers and professionals",
    "is_private": false
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Community created successfully",
  "data": {
    "id": 1,
    "name": "Tech Enthusiasts",
    "description": "A community for technology lovers and professionals",
    "profile_image_url": null,
    "is_private": false,
    "created_by": 1,
    "created_at": "2025-10-08T10:00:00.000000Z",
    "updated_at": "2025-10-08T10:00:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The community name field is required."],
    "profile_image_url": ["The profile image URL must be a valid URL."]
  }
}
```

---

### 3. Get Community Details
Retrieve detailed information about a specific community.

**Endpoint:** `GET /communities/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/communities/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Community retrieved successfully",
  "data": {
    "id": 1,
    "name": "Tech Enthusiasts",
    "description": "A community for technology lovers and professionals",
    "profile_image_url": "https://example.com/images/tech-community.jpg",
    "is_private": false,
    "created_by": 1,
    "created_at": "2025-10-08T10:00:00.000000Z",
    "updated_at": "2025-10-08T10:00:00.000000Z",
    "creator": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "members_count": 25,
    "posts_count": 12,
    "user_membership": {
      "role": "member",
      "status": "approved",
      "joined_at": "2025-10-08T11:00:00.000000Z"
    }
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Community not found"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Access denied. This is a private community."
}
```

---

### 4. Update Community
Update an existing community (only creator or admins can update).

**Endpoint:** `PUT /communities/{id}` or `PATCH /communities/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Updated Tech Community",
  "description": "An updated description for the community",
  "profile_image_url": "https://example.com/images/new-image.jpg",
  "is_private": true
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/communities/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Tech Community",
    "description": "An updated description for the community"
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Community updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Tech Community",
    "description": "An updated description for the community",
    "profile_image_url": "https://example.com/images/new-image.jpg",
    "is_private": true,
    "created_by": 1,
    "created_at": "2025-10-08T10:00:00.000000Z",
    "updated_at": "2025-10-08T12:00:00.000000Z"
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Only community creator or admins can update this community."
}
```

---

### 5. Delete Community
Delete a community (only creator can delete).

**Endpoint:** `DELETE /communities/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/communities/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Community deleted successfully"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Only the community creator can delete this community."
}
```

---

### 6. Join Community
Join a community as a member.

**Endpoint:** `POST /communities/{id}/join`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/communities/1/join" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200) - Public Community:**
```json
{
  "success": true,
  "message": "Successfully joined the community",
  "data": {
    "community_id": 1,
    "user_id": 2,
    "role": "member",
    "status": "approved",
    "joined_at": "2025-10-08T12:00:00.000000Z"
  }
}
```

**Success Response (200) - Private Community:**
```json
{
  "success": true,
  "message": "Join request submitted. Waiting for approval from community admins.",
  "data": {
    "community_id": 1,
    "user_id": 2,
    "role": "member",
    "status": "pending",
    "joined_at": null
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "You are already a member of this community"
}
```

---

### 7. Leave Community
Leave a community.

**Endpoint:** `DELETE /communities/{id}/leave`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/communities/1/leave" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Successfully left the community"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "You are not a member of this community"
}
```

---

### 8. List Community Members
Get a list of community members (only accessible by community members).

**Endpoint:** `GET /communities/{id}/members`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 15)
- `status` (optional): Filter by status (approved, pending)
- `role` (optional): Filter by role (member, admin)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/communities/1/members?status=approved&role=admin" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Community members retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "community_id": 1,
        "user_id": 1,
        "role": "admin",
        "status": "approved",
        "joined_at": "2025-10-08T10:00:00.000000Z",
        "created_at": "2025-10-08T10:00:00.000000Z",
        "updated_at": "2025-10-08T10:00:00.000000Z",
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      },
      {
        "id": 2,
        "community_id": 1,
        "user_id": 2,
        "role": "member",
        "status": "approved",
        "joined_at": "2025-10-08T11:00:00.000000Z",
        "created_at": "2025-10-08T11:00:00.000000Z",
        "updated_at": "2025-10-08T11:00:00.000000Z",
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/communities/1/members?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/communities/1/members?page=1",
    "next_page_url": null,
    "path": "http://localhost:8000/api/communities/1/members",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
  }
}
```

---

### 9. Approve/Reject Member
Approve or reject a pending member (only admins can perform this action).

**Endpoint:** `PUT /communities/{id}/members/{member_id}/approve`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "action": "approve"
}
```

**Field Descriptions:**
- `action` (required): Either "approve" or "reject"

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/communities/1/members/2/approve" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "approve"
  }'
```

**Success Response (200) - Approve:**
```json
{
  "success": true,
  "message": "Member approved successfully",
  "data": {
    "id": 2,
    "community_id": 1,
    "user_id": 2,
    "role": "member",
    "status": "approved",
    "joined_at": "2025-10-08T12:00:00.000000Z",
    "created_at": "2025-10-08T11:00:00.000000Z",
    "updated_at": "2025-10-08T12:00:00.000000Z"
  }
}
```

**Success Response (200) - Reject:**
```json
{
  "success": true,
  "message": "Member rejected and removed from community"
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Only community admins can approve/reject members."
}
```

---

### 10. Create Community Post
Create a new post in a community (only approved members can post).

**Endpoint:** `POST /communities/{id}/posts`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "caption": "Check out this amazing tech news!",
  "images": [
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg"
  ]
}
```

**Field Descriptions:**
- `caption` (optional): Post caption/content
- `images` (optional): Array of image URLs (max 10 images)

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/communities/1/posts" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "caption": "Check out this amazing tech news!",
    "images": ["https://example.com/image1.jpg"]
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Post created successfully",
  "data": {
    "id": 1,
    "community_id": 1,
    "admin_id": 2,
    "caption": "Check out this amazing tech news!",
    "created_at": "2025-10-08T12:00:00.000000Z",
    "updated_at": "2025-10-08T12:00:00.000000Z",
    "images": [
      {
        "id": 1,
        "post_id": 1,
        "image_url": "https://example.com/image1.jpg",
        "position": 1,
        "created_at": "2025-10-08T12:00:00.000000Z"
      }
    ],
    "admin": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    }
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Only approved community members can create posts."
}
```

---

### 11. List Community Posts
Get a paginated list of posts from a community.

**Endpoint:** `GET /communities/{id}/posts`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 15)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/communities/1/posts?page=1&per_page=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Community posts retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "community_id": 1,
        "admin_id": 2,
        "caption": "Check out this amazing tech news!",
        "created_at": "2025-10-08T12:00:00.000000Z",
        "updated_at": "2025-10-08T12:00:00.000000Z",
        "images": [
          {
            "id": 1,
            "post_id": 1,
            "image_url": "https://example.com/image1.jpg",
            "position": 1,
            "created_at": "2025-10-08T12:00:00.000000Z"
          }
        ],
        "admin": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/communities/1/posts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/communities/1/posts?page=1",
    "next_page_url": null,
    "path": "http://localhost:8000/api/communities/1/posts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

## Error Codes

| HTTP Status | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 400 | Bad request (e.g., already a member) |
| 401 | Unauthorized (invalid or missing token) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Internal server error |

---

## Community Privacy

### Public Communities
- Anyone can view the community
- Anyone can join immediately
- Members can view posts and create new posts

### Private Communities
- Only members can view the community details
- Join requests require approval from admins
- Only approved members can view and create posts

---

## Member Roles

### Member
- Can view community posts
- Can create new posts
- Can leave the community

### Admin
- All member permissions
- Can approve/reject join requests
- Can update community details
- Cannot delete the community (only creator can)

### Creator
- All admin permissions
- Can delete the community
- Automatically becomes an admin when community is created

---

## Rate Limiting

API endpoints may be subject to rate limiting. If you exceed the rate limit, you'll receive a `429 Too Many Requests` response.

---

## Pagination

Most list endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination information is included in the response under the `data` object with standard Laravel pagination structure.