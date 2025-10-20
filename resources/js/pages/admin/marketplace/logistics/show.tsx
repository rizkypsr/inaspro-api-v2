import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { ArrowLeft, Edit, Trash2, Calendar, MapPin, Truck, DollarSign } from 'lucide-react';
import { LogisticsShowPageProps } from '@/types/logistics';

export default function ShowLogistics({ shippingRate }: LogisticsShowPageProps) {
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const { delete: destroy, processing } = useForm();

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'Logistics', href: '/admin/marketplace/logistics' },
        { title: 'Details', href: `/admin/marketplace/logistics/${shippingRate.id}` },
    ];

    const handleDelete = () => {
        destroy(`/admin/marketplace/logistics/${shippingRate.id}`);
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Shipping Rate - ${shippingRate.province?.name} (${shippingRate.courier})`} />
            
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
                            <h1 className="text-3xl font-bold tracking-tight">Shipping Rate Details</h1>
                            <p className="text-muted-foreground">
                                {shippingRate.province?.name} - {shippingRate.courier}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/marketplace/logistics/${shippingRate.id}/edit`}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button variant="destructive" onClick={() => setShowDeleteDialog(true)}>
                            <Trash2 className="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                {/* Main Information */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Primary Details */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Truck className="mr-2 h-5 w-5" />
                                    Shipping Information
                                </CardTitle>
                                <CardDescription>
                                    Detailed information about this shipping rate configuration
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-4">
                                        <div className="flex items-center space-x-3">
                                            <MapPin className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Province</p>
                                                <p className="text-lg font-semibold">
                                                    {shippingRate.province?.name || 'Unknown Province'}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div className="flex items-center space-x-3">
                                            <Truck className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Courier Service</p>
                                                <Badge variant="secondary" className="text-base px-3 py-1">
                                                    {shippingRate.courier}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div className="flex items-center space-x-3">
                                            <DollarSign className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Shipping Rate</p>
                                                <p className="text-2xl font-bold font-mono text-green-600">
                                                    {formatCurrency(shippingRate.rate)}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div className="flex items-center space-x-3">
                                            <Calendar className="h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Created</p>
                                                <p className="text-sm">
                                                    {formatDate(shippingRate.created_at)}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Stats */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button className="w-full" asChild>
                                    <Link href={`/admin/marketplace/logistics/${shippingRate.id}/edit`}>
                                        <Edit className="mr-2 h-4 w-4" />
                                        Edit Rate
                                    </Link>
                                </Button>
                                <Button variant="outline" className="w-full" asChild>
                                    <Link href="/admin/marketplace/logistics/create">
                                        Create Similar Rate
                                    </Link>
                                </Button>
                                <Button 
                                    variant="destructive" 
                                    className="w-full"
                                    onClick={() => setShowDeleteDialog(true)}
                                >
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    Delete Rate
                                </Button>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Rate Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">ID:</span>
                                        <span className="font-mono">#{shippingRate.id}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Province ID:</span>
                                        <span className="font-mono">#{shippingRate.province_id}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Last Updated:</span>
                                        <span>{formatDate(shippingRate.updated_at)}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Additional Information */}
                <Card>
                    <CardHeader>
                        <CardTitle>Rate Guidelines & Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div>
                                <h4 className="font-medium mb-3">About This Rate</h4>
                                <div className="space-y-2 text-muted-foreground">
                                    <p>
                                        This shipping rate applies to deliveries from your warehouse to 
                                        destinations within <strong>{shippingRate.province?.name}</strong> using 
                                        <strong> {shippingRate.courier}</strong> courier service.
                                    </p>
                                    <p>
                                        The rate of <strong>{formatCurrency(shippingRate.rate)}</strong> is 
                                        the base shipping cost that will be applied to orders shipped to this province.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h4 className="font-medium mb-3">Usage Notes</h4>
                                <ul className="space-y-1 text-muted-foreground list-disc list-inside">
                                    <li>Rate applies to standard package sizes and weights</li>
                                    <li>Additional charges may apply for oversized items</li>
                                    <li>Delivery times may vary based on location within the province</li>
                                    <li>Rate is subject to courier service terms and conditions</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Dialog */}
            <AlertDialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete the shipping rate for{' '}
                            <strong>{shippingRate.province?.name}</strong> - <strong>{shippingRate.courier}</strong>.
                            <br /><br />
                            Any existing orders using this shipping rate will not be affected, but new orders 
                            will not be able to use this rate configuration.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleDelete}
                            disabled={processing}
                            className="bg-red-600 hover:bg-red-700"
                        >
                            {processing ? 'Deleting...' : 'Delete Shipping Rate'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}