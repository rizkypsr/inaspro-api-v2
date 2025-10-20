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
import { LogisticsCreatePageProps, LogisticsFormData } from '@/types/logistics';

export default function CreateLogistics({ provinces }: LogisticsCreatePageProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LogisticsFormData>({
        province_id: '',
        courier: '',
        rate: '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'Logistics', href: '/admin/marketplace/logistics' },
        { title: 'Create', href: '/admin/marketplace/logistics/create' },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/marketplace/logistics', {
            onSuccess: () => reset(),
        });
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Shipping Rate" />
            
            <div className="space-y-6 p-4">
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
                            <h1 className="text-3xl font-bold tracking-tight">Create Shipping Rate</h1>
                            <p className="text-muted-foreground">
                                Add a new shipping rate for a province and courier
                            </p>
                        </div>
                    </div>
                </div>

                {/* Form */}
                <Card>
                    <CardHeader>
                        <CardTitle>Shipping Rate Details</CardTitle>
                        <CardDescription>
                            Enter the shipping rate information for the selected province and courier.
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
                                            Creating...
                                        </>
                                    ) : (
                                        <>
                                            <Save className="mr-2 h-4 w-4" />
                                            Create Shipping Rate
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}