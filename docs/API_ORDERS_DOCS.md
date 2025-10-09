# Orders API Documentation

This document provides comprehensive information about the Orders API endpoints, including request/response examples and error handling.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All order endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

---

## Endpoints

### 1. List Orders
Get a paginated list of orders for the authenticated user.

**Endpoint:** `GET /orders`

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
curl -X GET "http://localhost:8000/api/orders?page=1&per_page=10" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Orders retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "order_number": "ORD-20241201-001",
        "total_amount": 150000,
        "status": "pending",
        "payment_status": "pending",
        "payment_method": "credit_card",
        "shipping_address": {
          "street": "123 Main St",
          "city": "Jakarta",
          "state": "DKI Jakarta",
          "postal_code": "12345",
          "country": "Indonesia"
        },
        "courier_name": "JNE",
        "tracking_number": null,
        "created_at": "2024-12-01T10:00:00.000000Z",
        "updated_at": "2024-12-01T10:00:00.000000Z",
        "items": [
          {
            "id": 1,
            "product_id": 1,
            "quantity": 2,
            "price": 50000,
            "total": 100000,
            "product": {
              "id": 1,
              "name": "Sample Product",
              "slug": "sample-product"
            }
          }
        ]
      }
    ],
    "first_page_url": "http://localhost:8000/api/orders?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/orders?page=1",
    "links": [],
    "next_page_url": null,
    "path": "http://localhost:8000/api/orders",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

### 2. Create Order
Create a new order from a cart with optional voucher application.

**Endpoint:** `POST /orders`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "cart_id": 1,
  "payment_method": "credit_card",
  "shipping_address": {
    "street": "123 Main St",
    "city": "Jakarta",
    "state": "DKI Jakarta",
    "postal_code": "12345",
    "country": "Indonesia"
  },
  "courier_name": "JNE",
  "global_voucher_codes": ["SAVE10", "WELCOME"],
  "product_voucher_codes": ["PROD20"]
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/orders" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cart_id": 1,
    "payment_method": "credit_card",
    "shipping_address": {
      "street": "123 Main St",
      "city": "Jakarta",
      "state": "DKI Jakarta",
      "postal_code": "12345",
      "country": "Indonesia"
    },
    "courier_name": "JNE",
    "global_voucher_codes": ["SAVE10"],
    "product_voucher_codes": ["PROD20"]
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20241201-001",
    "total_amount": 135000,
    "original_amount": 150000,
    "discount_amount": 15000,
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "credit_card",
    "shipping_address": {
      "street": "123 Main St",
      "city": "Jakarta",
      "state": "DKI Jakarta",
      "postal_code": "12345",
      "country": "Indonesia"
    },
    "courier_name": "JNE",
    "tracking_number": null,
    "created_at": "2024-12-01T10:00:00.000000Z",
    "updated_at": "2024-12-01T10:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 50000,
        "total": 100000,
        "product": {
          "id": 1,
          "name": "Sample Product",
          "slug": "sample-product"
        }
      }
    ],
    "global_vouchers": [
      {
        "id": 1,
        "code": "SAVE10",
        "discount_amount": 10000
      }
    ],
    "product_vouchers": [
      {
        "id": 1,
        "code": "PROD20",
        "discount_amount": 5000
      }
    ]
  }
}
```

**Validation Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "cart_id": ["The cart id field is required."],
    "payment_method": ["The payment method field is required."],
    "shipping_address.street": ["The street field is required."],
    "shipping_address.city": ["The city field is required."],
    "courier_name": ["The courier name field is required."]
  }
}
```

**Error Response - Cart Not Found (404):**
```json
{
  "success": false,
  "message": "Cart not found or empty"
}
```

**Error Response - Invalid Voucher (400):**
```json
{
  "success": false,
  "message": "Invalid voucher: INVALID_CODE"
}
```

---

### 3. Get Order Details
Retrieve details of a specific order.

**Endpoint:** `GET /orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/orders/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Order retrieved successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20241201-001",
    "total_amount": 135000,
    "original_amount": 150000,
    "discount_amount": 15000,
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "credit_card",
    "shipping_address": {
      "street": "123 Main St",
      "city": "Jakarta",
      "state": "DKI Jakarta",
      "postal_code": "12345",
      "country": "Indonesia"
    },
    "courier_name": "JNE",
    "tracking_number": null,
    "created_at": "2024-12-01T10:00:00.000000Z",
    "updated_at": "2024-12-01T10:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 50000,
        "total": 100000,
        "product": {
          "id": 1,
          "name": "Sample Product",
          "slug": "sample-product",
          "image": "product-image.jpg"
        }
      }
    ],
    "global_vouchers": [
      {
        "id": 1,
        "code": "SAVE10",
        "discount_amount": 10000
      }
    ],
    "product_vouchers": [
      {
        "id": 1,
        "code": "PROD20",
        "discount_amount": 5000
      }
    ]
  }
}
```

**Error Response - Order Not Found (404):**
```json
{
  "success": false,
  "message": "Order not found"
}
```

**Error Response - Unauthorized (403):**
```json
{
  "success": false,
  "message": "Unauthorized to access this order"
}
```

---

### 4. Update Order
Update limited order information (shipping address, courier, tracking number).

**Endpoint:** `PUT /orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "shipping_address": {
    "street": "456 New Street",
    "city": "Bandung",
    "state": "West Java",
    "postal_code": "54321",
    "country": "Indonesia"
  },
  "courier_name": "TIKI",
  "tracking_number": "TK123456789"
}
```

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/orders/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "shipping_address": {
      "street": "456 New Street",
      "city": "Bandung",
      "state": "West Java",
      "postal_code": "54321",
      "country": "Indonesia"
    },
    "courier_name": "TIKI",
    "tracking_number": "TK123456789"
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Order updated successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20241201-001",
    "total_amount": 135000,
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "credit_card",
    "shipping_address": {
      "street": "456 New Street",
      "city": "Bandung",
      "state": "West Java",
      "postal_code": "54321",
      "country": "Indonesia"
    },
    "courier_name": "TIKI",
    "tracking_number": "TK123456789",
    "updated_at": "2024-12-01T11:00:00.000000Z"
  }
}
```

**Error Response - Cannot Update (400):**
```json
{
  "success": false,
  "message": "Cannot update order with status: shipped"
}
```

---

### 5. Cancel Order
Cancel an order (soft delete).

**Endpoint:** `DELETE /orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/orders/1" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

**Error Response - Cannot Cancel (400):**
```json
{
  "success": false,
  "message": "Cannot cancel order with status: shipped"
}
```

---

### 6. Update Order Status (Admin Only)
Update the status of an order. This endpoint is typically used by admin users.

**Endpoint:** `PATCH /orders/{id}/status`

**Headers:**
```
Authorization: Bearer {admin-token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "status": "processing"
}
```

**Example Request:**
```bash
curl -X PATCH "http://localhost:8000/api/orders/1/status" \
  -H "Authorization: Bearer admin-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "processing"
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20241201-001",
    "status": "processing",
    "updated_at": "2024-12-01T12:00:00.000000Z"
  }
}
```

**Validation Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

---

### 7. Update Payment Status
Update the payment status of an order.

**Endpoint:** `PATCH /orders/{id}/payment-status`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "payment_status": "paid"
}
```

**Example Request:**
```bash
curl -X PATCH "http://localhost:8000/api/orders/1/payment-status" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "payment_status": "paid"
  }'
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment status updated successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20241201-001",
    "payment_status": "paid",
    "status": "confirmed",
    "updated_at": "2024-12-01T12:30:00.000000Z"
  }
}
```

---

## Status Values

### Order Status
- `pending`: Order is created but not yet processed
- `confirmed`: Order is confirmed and ready for processing
- `processing`: Order is being prepared
- `shipped`: Order has been shipped
- `delivered`: Order has been delivered
- `cancelled`: Order has been cancelled

### Payment Status
- `pending`: Payment is pending
- `paid`: Payment has been completed
- `failed`: Payment has failed
- `refunded`: Payment has been refunded

## Error Codes

| HTTP Status | Error Type | Description |
|-------------|------------|-------------|
| 400 | Bad Request | Invalid request data or business logic error |
| 401 | Unauthorized | Missing or invalid authentication token |
| 403 | Forbidden | User doesn't have permission to access the resource |
| 404 | Not Found | Requested resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors in request data |
| 500 | Internal Server Error | Server-side error occurred |

## Common Error Response Format

All error responses follow this structure:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

For validation errors, the `errors` object contains field-specific error messages. For other errors, the `errors` field may be omitted.