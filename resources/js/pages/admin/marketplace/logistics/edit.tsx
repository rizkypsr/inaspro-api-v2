import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ArrowLeft, Save } from 'lucide-react';
import { LogisticsEditPageProps, LogisticsFormData } from '@/types/logistics';

export default function EditLogistics({ shippingRate, provinces }: LogisticsEditPageProps) {
    const { data, setData, put, processing, errors } = useForm<LogisticsFormData>({
        province_id: shippingRate.province_id.toString(),
        courier: shippingRate.courier,
        rate: shippingRate.rate.toString(),
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'Logistics', href: '/admin/marketplace/logistics' },
        { title: 'Edit', href: `/admin/marketplace/logistics/${shippingRate.id}/edit` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/marketplace/logistics/${shippingRate.id}`);
    };

    const courierOptions = [
        'JNE',
        'TIKI',
        'POS Indonesia',
        'J&T Express',
        'SiCepat',
        'AnterAja',
        'Ninja Express',
        'Lion Parcel',
        'Wahana',
        'SAP Express',
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Shipping Rate - ${shippingRate.province?.name} (${shippingRate.courier})`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/admin/marketplace/logistics">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Logistics
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Edit Shipping Rate</h1>
                            <p className="text-muted-foreground">
                                Update shipping rate for {shippingRate.province?.name} - {shippingRate.courier}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Current Rate Info */}
                <Card>
                    <CardHeader>
                        <CardTitle>Current Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span className="font-medium text-muted-foreground">Province:</span>
                                <p className="font-semibold">{shippingRate.province?.name}</p>
                            </div>
                            <div>
                                <span className="font-medium text-muted-foreground">Courier:</span>
                                <p className="font-semibold">{shippingRate.courier}</p>
                            </div>
                            <div>
                                <span className="font-medium text-muted-foreground">Current Rate:</span>
                                <p className="font-semibold font-mono">{formatCurrency(shippingRate.rate)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Form */}
                <Card>
                    <CardHeader>
                        <CardTitle>Update Shipping Rate</CardTitle>
                        <CardDescription>
                            Modify the shipping rate information for this province and courier combination.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Province Selection */}
                                <div className="space-y-2">
                                    <Label htmlFor="province_id">Province *</Label>
                                    <Select
                                        value={data.province_id}
                                        onValueChange={(value) => setData('province_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a province" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {provinces.map((province) => (
                                                <SelectItem key={province.id} value={province.id.toString()}>
                                                    {province.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.province_id && (
                                        <p className="text-sm text-red-600">{errors.province_id}</p>
                                    )}
                                </div>

                                {/* Courier Selection */}
                                <div className="space-y-2">
                                    <Label htmlFor="courier">Courier *</Label>
                                    <Select
                                        value={data.courier}
                                        onValueChange={(value) => setData('courier', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a courier" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {courierOptions.map((courier) => (
                                                <SelectItem key={courier} value={courier}>
                                                    {courier}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.courier && (
                                        <p className="text-sm text-red-600">{errors.courier}</p>
                                    )}
                                </div>
                            </div>

                            {/* Rate Input */}
                            <div className="space-y-2">
                                <Label htmlFor="rate">Shipping Rate (IDR) *</Label>
                                <div className="relative">
                                    <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">
                                        Rp
                                    </span>
                                    <Input
                                        id="rate"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        placeholder="Enter shipping rate"
                                        value={data.rate}
                                        onChange={(e) => setData('rate', e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                                {errors.rate && (
                                    <p className="text-sm text-red-600">{errors.rate}</p>
                                )}
                                <p className="text-sm text-muted-foreground">
                                    Enter the shipping rate in Indonesian Rupiah (IDR)
                                </p>
                            </div>

                            {/* Form Actions */}
                            <div className="flex items-center justify-end space-x-4 pt-6 border-t">
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/admin/marketplace/logistics">
                                        Cancel
                                    </Link>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                            Updating...
                                        </>
                                    ) : (
                                        <>
                                            <Save className="mr-2 h-4 w-4" />
                                            Update Shipping Rate
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Help Section */}
                <Card>
                    <CardHeader>
                        <CardTitle>Guidelines</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4 text-sm text-muted-foreground">
                            <div>
                                <h4 className="font-medium text-foreground mb-2">Rate Update Guidelines:</h4>
                                <ul className="space-y-1 list-disc list-inside">
                                    <li>Consider market rates and competitor pricing when updating</li>
                                    <li>Ensure the new rate reflects current operational costs</li>
                                    <li>Notify relevant stakeholders of significant rate changes</li>
                                    <li>Document the reason for rate changes for future reference</li>
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-medium text-foreground mb-2">Impact Assessment:</h4>
                                <p>
                                    Rate changes may affect existing orders and customer expectations. 
                                    Consider the timing of updates and communicate changes appropriately 
                                    to maintain customer satisfaction.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}