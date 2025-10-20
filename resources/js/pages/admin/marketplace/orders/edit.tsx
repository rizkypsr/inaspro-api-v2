import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  ArrowLeft, 
  Truck, 
  CreditCard, 
  Upload,
  X,
  Package,
  User,
  MapPin,
  Calendar,
  DollarSign,
  Settings
} from 'lucide-react';
import { Order, OrderEditPageProps, TrackingFormData, PaymentProofFormData, OrderFormData } from '@/types/order';

interface OrderEditProps extends OrderEditPageProps {}

export default function OrderEdit({ order }: OrderEditProps) {
  const [previewImage, setPreviewImage] = useState<string | null>(
    order.payment_proof ? `/storage/${order.payment_proof}` : null
  );

  // Form for updating tracking number
  const trackingForm = useForm<TrackingFormData>({
    tracking_number: order.tracking_number || '',
  });

  // Form for uploading payment proof
  const paymentForm = useForm<PaymentProofFormData>({
    payment_proof: null,
  });

  // Form for updating order status
  const statusForm = useForm<OrderFormData>({
    status: order.status,
    payment_status: order.payment_status,
  });

  const handleTrackingSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    trackingForm.put(`/admin/marketplace/orders/${order.id}/tracking`, {
      onSuccess: () => {
        // Success handled by Inertia
      }
    });
  };

  const handlePaymentProofSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    paymentForm.post(`/admin/marketplace/orders/${order.id}/payment-proof`, {
      onSuccess: () => {
        // Success handled by Inertia
      }
    });
  };

  const handleStatusSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    statusForm.put(`/admin/marketplace/orders/${order.id}`, {
      onSuccess: () => {
        // Success handled by Inertia
      }
    });
  };

  const handleRemovePaymentProof = () => {
    router.delete(`/admin/marketplace/orders/${order.id}/payment-proof`, {
      onSuccess: () => {
        setPreviewImage(null);
      }
    });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      paymentForm.setData('payment_proof', file);
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        setPreviewImage(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const getStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'pending': return 'secondary';
      case 'paid': return 'default';
      case 'shipped': return 'outline';
      case 'completed': return 'default';
      case 'cancelled': return 'destructive';
      default: return 'secondary';
    }
  };

  const getPaymentStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'pending': return 'secondary';
      case 'paid': return 'default';
      case 'failed': return 'destructive';
      case 'refunded': return 'outline';
      default: return 'secondary';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <AppLayout>
      <Head title={`Edit Order #${order.uuid}`} />
      
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => router.get('/admin/marketplace/orders')}
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Orders
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Edit Order #{order.uuid}</h1>
            <p className="text-muted-foreground">
              Update tracking information and payment proof
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Order Details */}
          <div className="lg:col-span-2 space-y-6">
            {/* Order Information */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="h-5 w-5" />
                  Order Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Order Number</Label>
                    <p className="font-medium">{order.uuid}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Order Date</Label>
                    <p>{formatDate(order.created_at)}</p>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Status</Label>
                    <div className="mt-1">
                      <Badge variant={getStatusBadgeVariant(order.status)}>
                        {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                      </Badge>
                    </div>
                  </div>
                  <div>
                    <Label className="text-sm font-medium text-muted-foreground">Payment Status</Label>
                    <div className="mt-1">
                      <Badge variant={getPaymentStatusBadgeVariant(order.payment_status)}>
                        {order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}
                      </Badge>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Customer Information */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Customer Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">Customer</Label>
                  <p className="font-medium">{order.user?.name}</p>
                  <p className="text-sm text-muted-foreground">{order.user?.email}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium text-muted-foreground">Shipping Address</Label>
                  <p className="whitespace-pre-line">{order.shipping_address}</p>
                </div>
              </CardContent>
            </Card>

            {/* Order Items */}
            <Card>
              <CardHeader>
                <CardTitle>Order Items</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {order.order_items?.map((item) => (
                    <div key={item.id} className="flex items-center gap-4 p-4 border rounded-lg">
                      <div className="flex-1">
                        <h4 className="font-medium">{item.product_variant?.product?.name}</h4>
                        <p className="text-sm text-muted-foreground">
                          Quantity: {item.quantity} × {formatCurrency(item.price)}
                        </p>
                      </div>
                      <div className="text-right">
                        <p className="font-medium">{formatCurrency(item.quantity * item.price)}</p>
                      </div>
                    </div>
                  ))}
                  <Separator />
                  <div className="flex justify-between items-center font-medium">
                    <span>Total Amount</span>
                    <span>{formatCurrency(order.total_amount)}</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Actions */}
          <div className="space-y-6">
            {/* Update Order Status */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="h-5 w-5" />
                  Order Status
                </CardTitle>
                <CardDescription>
                  Update the order status and payment status
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleStatusSubmit} className="space-y-4">
                  <div>
                    <Label htmlFor="status">Order Status</Label>
                    <Select
                      value={statusForm.data.status}
                      onValueChange={(value) => statusForm.setData('status', value as any)}
                    >
                      <SelectTrigger className={statusForm.errors.status ? 'border-red-500' : ''}>
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="paid">Paid</SelectItem>
                        <SelectItem value="shipped">Shipped</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                      </SelectContent>
                    </Select>
                    {statusForm.errors.status && (
                      <p className="text-sm text-red-500 mt-1">
                        {statusForm.errors.status}
                      </p>
                    )}
                  </div>
                  <div>
                    <Label htmlFor="payment_status">Payment Status</Label>
                    <Select
                      value={statusForm.data.payment_status}
                      onValueChange={(value) => statusForm.setData('payment_status', value as any)}
                    >
                      <SelectTrigger className={statusForm.errors.payment_status ? 'border-red-500' : ''}>
                        <SelectValue placeholder="Select payment status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="paid">Paid</SelectItem>
                        <SelectItem value="failed">Failed</SelectItem>
                        <SelectItem value="refunded">Refunded</SelectItem>
                      </SelectContent>
                    </Select>
                    {statusForm.errors.payment_status && (
                      <p className="text-sm text-red-500 mt-1">
                        {statusForm.errors.payment_status}
                      </p>
                    )}
                  </div>
                  <Button 
                    type="submit" 
                    className="w-full"
                    disabled={statusForm.processing}
                  >
                    {statusForm.processing ? 'Updating...' : 'Update Status'}
                  </Button>
                </form>
              </CardContent>
            </Card>

            {/* Update Tracking Number */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Truck className="h-5 w-5" />
                  Tracking Information
                </CardTitle>
                <CardDescription>
                  Update the tracking number for this order
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleTrackingSubmit} className="space-y-4">
                  <div>
                    <Label htmlFor="tracking_number">Tracking Number</Label>
                    <Input
                      id="tracking_number"
                      type="text"
                      value={trackingForm.data.tracking_number}
                      onChange={(e) => trackingForm.setData('tracking_number', e.target.value)}
                      placeholder="Enter tracking number"
                      className={trackingForm.errors.tracking_number ? 'border-red-500' : ''}
                    />
                    {trackingForm.errors.tracking_number && (
                      <p className="text-sm text-red-500 mt-1">
                        {trackingForm.errors.tracking_number}
                      </p>
                    )}
                  </div>
                  <Button 
                    type="submit" 
                    className="w-full"
                    disabled={trackingForm.processing}
                  >
                    {trackingForm.processing ? 'Updating...' : 'Update Tracking'}
                  </Button>
                </form>
              </CardContent>
            </Card>

            {/* Payment Proof */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CreditCard className="h-5 w-5" />
                  Payment Proof
                </CardTitle>
                <CardDescription>
                  Upload or update payment proof image
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* Current Payment Proof */}
                {previewImage && (
                  <div className="space-y-2">
                    <Label>Current Payment Proof</Label>
                    <div className="relative">
                      <img
                        src={previewImage}
                        alt="Payment Proof"
                        className="w-full h-48 object-cover rounded-lg border"
                      />
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        className="absolute top-2 right-2"
                        onClick={handleRemovePaymentProof}
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                )}

                {/* Upload New Payment Proof */}
                <form onSubmit={handlePaymentProofSubmit} className="space-y-4">
                  <div>
                    <Label htmlFor="payment_proof">
                      {previewImage ? 'Replace Payment Proof' : 'Upload Payment Proof'}
                    </Label>
                    <Input
                      id="payment_proof"
                      type="file"
                      accept="image/*"
                      onChange={handleFileChange}
                      className={paymentForm.errors.payment_proof ? 'border-red-500' : ''}
                    />
                    {paymentForm.errors.payment_proof && (
                      <p className="text-sm text-red-500 mt-1">
                        {paymentForm.errors.payment_proof}
                      </p>
                    )}
                  </div>
                  <Button 
                    type="submit" 
                    className="w-full"
                    disabled={paymentForm.processing || !paymentForm.data.payment_proof}
                  >
                    <Upload className="h-4 w-4 mr-2" />
                    {paymentForm.processing ? 'Uploading...' : 'Upload Payment Proof'}
                  </Button>
                </form>
              </CardContent>
            </Card>

            {/* Order Summary */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <DollarSign className="h-5 w-5" />
                  Order Summary
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span>{formatCurrency(order.total_amount - (order.shipping_cost || 0))}</span>
                </div>
                {order.shipping_cost && (
                  <div className="flex justify-between">
                    <span>Shipping</span>
                    <span>{formatCurrency(order.shipping_cost)}</span>
                  </div>
                )}
                <Separator />
                <div className="flex justify-between font-medium">
                  <span>Total</span>
                  <span>{formatCurrency(order.total_amount)}</span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}