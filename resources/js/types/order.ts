export interface Order {
  id: string;
  uuid: string;
  user_id: string;
  cart_id: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  tracking_number?: string;
  payment_proof?: string;
  total_amount: number;
  shipping_cost: number;
  shipping_address: string;
  notes?: string;
  created_at: string;
  updated_at: string;
  user?: User;
  cart?: Cart;
  shipping_rate?: ShippingRate;
  order_items?: OrderItem[];
  global_vouchers?: GlobalVoucher[];
  product_vouchers?: ProductVoucher[];
}

export interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
}

export interface Cart {
  id: string;
  user_id: string;
  total_amount: number;
  created_at: string;
  updated_at: string;
}

export interface ShippingRate {
  id: string;
  province_id: string;
  courier: string;
  rate: number;
  created_at: string;
  updated_at: string;
  province?: Province;
}

export interface Province {
  id: string;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface OrderItem {
  id: string;
  order_id: string;
  product_variant_id: string;
  quantity: number;
  price: number;
  total: number;
  product_variant?: ProductVariant;
}

export interface ProductVariant {
  id: string;
  product_id: string;
  variant_name: string;
  price: number;
  stock: number;
  product?: Product;
}

export interface Product {
  id: string;
  name: string;
  slug: string;
  price: number;
  image?: string;
}

export interface GlobalVoucher {
  id: string;
  code: string;
  discount_type: 'percentage' | 'fixed';
  discount_value: number;
  minimum_amount?: number;
  maximum_discount?: number;
  is_active: boolean;
  expires_at?: string;
}

export interface ProductVoucher {
  id: string;
  product_id: string;
  code: string;
  discount_type: 'percentage' | 'fixed';
  discount_value: number;
  minimum_quantity?: number;
  maximum_discount?: number;
  is_active: boolean;
  expires_at?: string;
}

export interface OrderFilters {
  search?: string;
  status?: string;
  payment_status?: string;
  date_from?: string;
  date_to?: string;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

export interface OrderFormData {
  status?: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_status?: 'pending' | 'paid' | 'failed' | 'refunded';
  tracking_number?: string;
  notes?: string;
}

export interface TrackingFormData {
  tracking_number: string;
}

export interface PaymentProofFormData {
  payment_proof: File | null;
}

export interface BulkUpdateFormData {
  order_ids: string[];
  action: 'update_status' | 'update_payment_status';
  status?: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_status?: 'pending' | 'paid' | 'failed' | 'refunded';
}

export interface OrdersPageProps {
  orders: {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  filters: OrderFilters;
  stats?: {
    total_orders: number;
    pending_orders: number;
    processing_orders: number;
    shipped_orders: number;
    delivered_orders: number;
    cancelled_orders: number;
    pending_payments: number;
    paid_orders: number;
    total_revenue: number;
  };
}

export interface OrderShowPageProps {
  order: Order;
}

export interface OrderEditPageProps {
  order: Order;
}