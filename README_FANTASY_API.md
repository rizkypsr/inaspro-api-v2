# Fantasy API Documentation

## Overview
The Fantasy API provides endpoints for managing fantasy sports events, including team registration, payment processing, and inventory management for shoes and t-shirts.

## Authentication
All API endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Base URL
```
/api/
```

## Fantasy Events

### List Events
```http
GET /api/fantasy-events
```

**Query Parameters:**
- `search` (string): Search events by title
- `status` (string): Filter by status (draft, open, closed, finished)
- `per_page` (integer): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Fantasy Football Championship",
        "description": "Annual championship event",
        "play_date": "2024-03-15",
        "base_fee": 150000,
        "status": "open",
        "created_at": "2024-01-15T10:00:00.000000Z",
        "teams_count": 4,
        "registrations_count": 25,
        "total_revenue": 3750000
      }
    ],
    "current_page": 1,
    "total": 10
  }
}
```

### Get Event Details
```http
GET /api/fantasy-events/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Fantasy Football Championship",
    "description": "Annual championship event",
    "play_date": "2024-03-15",
    "base_fee": 150000,
    "status": "open",
    "teams": [...],
    "shoes": [...],
    "statistics": {
      "total_registrations": 25,
      "confirmed_registrations": 20,
      "pending_registrations": 5,
      "total_revenue": 3750000
    }
  }
}
```

### Get Event Teams
```http
GET /api/fantasy-events/{id}/teams
```

### Get Event Shoes
```http
GET /api/fantasy-events/{id}/shoes
```

## Fantasy Teams

### List Teams for Event
```http
GET /api/fantasy-events/{eventId}/teams
```

**Query Parameters:**
- `search` (string): Search teams by name
- `available_only` (boolean): Show only teams with available slots
- `per_page` (integer): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Team Alpha",
        "slot_limit": 10,
        "registrations_count": 7,
        "available_slots": 3,
        "is_full": false,
        "tshirt_options": [...]
      }
    ]
  }
}
```

### Get Team Details
```http
GET /api/fantasy-events/{eventId}/teams/{teamId}
```

### Get Team Availability
```http
GET /api/fantasy-events/{eventId}/teams-availability
```

### Get Team Members
```http
GET /api/fantasy-events/{eventId}/teams/{teamId}/members
```

### Get Team T-shirt Options
```http
GET /api/fantasy-events/{eventId}/teams/{teamId}/tshirt-options
```

## Fantasy Shoes

### List Shoes for Event
```http
GET /api/fantasy-events/{eventId}/shoes
```

**Query Parameters:**
- `search` (string): Search shoes by name
- `available_only` (boolean): Show only shoes with available stock
- `per_page` (integer): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Nike Air Max",
        "price": 500000,
        "total_stock": 100,
        "available_stock": 85,
        "is_available": true,
        "sizes": [
          {
            "id": 1,
            "size": "40",
            "stock": 20,
            "available_stock": 18,
            "is_available": true
          }
        ]
      }
    ]
  }
}
```

### Get Shoe Details
```http
GET /api/fantasy-events/{eventId}/shoes/{shoeId}
```

### Get Shoe Sizes
```http
GET /api/fantasy-events/{eventId}/shoes/{shoeId}/sizes
```

### Get Shoes Availability
```http
GET /api/fantasy-events/{eventId}/shoes-availability
```

### Check Size Availability
```http
POST /api/fantasy-events/{eventId}/shoes/check-availability
```

**Request Body:**
```json
{
  "shoe_size_ids": [1, 2, 3]
}
```

### Get Popular Sizes
```http
GET /api/fantasy-events/{eventId}/shoes/popular-sizes
```

## Fantasy Registrations

### List User Registrations
```http
GET /api/fantasy-registrations
```

**Query Parameters:**
- `status` (string): Filter by status (pending, confirmed, cancelled)
- `event_id` (integer): Filter by event ID
- `per_page` (integer): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "registration_code": "REG-ABC12345",
        "fantasy_event_id": 1,
        "fantasy_event_team_id": 1,
        "registration_fee": 650000,
        "status": "pending",
        "fantasy_event": {...},
        "fantasy_event_team": {...},
        "registration_items": [...],
        "payments": [...]
      }
    ]
  }
}
```

### Create Registration
```http
POST /api/fantasy-registrations
```

**Request Body:**
```json
{
  "fantasy_event_id": 1,
  "fantasy_event_team_id": 1,
  "items": [
    {
      "type": "tshirt",
      "tshirt_option_id": 1
    },
    {
      "type": "shoe",
      "shoe_size_id": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration created successfully",
  "data": {
    "id": 1,
    "registration_code": "REG-ABC12345",
    "registration_fee": 650000,
    "status": "pending",
    ...
  }
}
```

### Get Registration Details
```http
GET /api/fantasy-registrations/{id}
```

### Cancel Registration
```http
PUT /api/fantasy-registrations/{id}/cancel
```

### Get Registration Summary for Event
```http
GET /api/fantasy-events/{eventId}/registration-summary
```

## Fantasy Payments

### List User Payments
```http
GET /api/fantasy-payments
```

**Query Parameters:**
- `status` (string): Filter by status (pending, approved, rejected)
- `payment_method` (string): Filter by method (bank_transfer, e_wallet, cash)
- `per_page` (integer): Items per page (default: 15)

### Submit Payment
```http
POST /api/fantasy-payments
```

**Request Body (multipart/form-data):**
```
fantasy_registration_id: 1
payment_method: bank_transfer
amount: 650000
payment_proof: [file]
notes: "Payment via BCA transfer"
```

**Response:**
```json
{
  "success": true,
  "message": "Payment submitted successfully",
  "data": {
    "id": 1,
    "payment_code": "PAY-XYZ98765",
    "payment_method": "bank_transfer",
    "amount": 650000,
    "status": "pending",
    "payment_proof": "fantasy/payments/1234567890_abc123.jpg"
  }
}
```

### Get Payment Details
```http
GET /api/fantasy-payments/{id}
```

### Update Payment Proof
```http
PUT /api/fantasy-payments/{id}/proof
```

**Request Body (multipart/form-data):**
```
payment_proof: [file]
notes: "Updated payment proof"
```

### Get Payment Methods
```http
GET /api/fantasy-payments/methods
```

**Response:**
```json
{
  "success": true,
  "data": {
    "bank_transfer": {
      "name": "Bank Transfer",
      "accounts": [
        {
          "bank": "BCA",
          "account_number": "1234567890",
          "account_name": "INASPRO"
        }
      ]
    },
    "e_wallet": {
      "name": "E-Wallet",
      "accounts": [
        {
          "provider": "GoPay",
          "number": "081234567890",
          "name": "INASPRO"
        }
      ]
    },
    "cash": {
      "name": "Cash",
      "description": "Pay directly at the event location"
    }
  }
}
```

### Get Payment Statistics
```http
GET /api/fantasy-payments/statistics
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message (in development)"
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Validation Rules

### Registration Creation
- `fantasy_event_id`: Required, must exist in fantasy_events table
- `fantasy_event_team_id`: Required, must exist in fantasy_event_teams table
- `items`: Required array with at least 1 item
- `items.*.type`: Required, must be 'tshirt' or 'shoe'
- `items.*.tshirt_option_id`: Required if type is 'tshirt'
- `items.*.shoe_size_id`: Required if type is 'shoe'

### Payment Submission
- `fantasy_registration_id`: Required, must exist and belong to user
- `payment_method`: Required, must be 'bank_transfer', 'e_wallet', or 'cash'
- `amount`: Required, must be numeric and match registration fee
- `payment_proof`: Optional image file (jpeg, png, jpg, max 2MB)
- `notes`: Optional string, max 500 characters

## Rate Limiting
API endpoints are rate limited to 60 requests per minute per user.

## File Storage
Payment proof files are stored in the `storage/app/public/fantasy/payments/` directory and accessible via the `/storage/fantasy/payments/` URL path.