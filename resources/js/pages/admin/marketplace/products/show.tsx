import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { ArrowLeft, Edit, Trash2, Eye, Loader2 } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayout from '@/layouts/app-layout';

interface Category {
    id: number;
    name: string;
}

interface ProductVariant {
    id: number;
    sku: string;
    variant_name: string;
    price: string;
    stock: number;
    status: string;
    image_url: string | null;
}

interface Product {
    id: number;
    category_id: number;
    name: string;
    description: string;
    base_price: string;
    status: string;
    created_at: string;
    updated_at: string;
    category: Category;
    variants: ProductVariant[];
}

interface Props {
    product: Product;
}

export default function ShowProduct({ product }: Props) {
    const [selectedImage, setSelectedImage] = useState<string | null>(null);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const { delete: destroy, processing } = useForm({});

    const handleDelete = () => {
        setDeleteError(null);
        const confirmed = window.confirm('Hapus produk ini? Tindakan tidak dapat dibatalkan.');
        if (!confirmed) return;

        destroy(`/admin/marketplace/products/${product.id}` as any, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit('/admin/marketplace/products');
            },
            onError: () => {
                setDeleteError('Gagal menghapus produk. Silakan coba lagi.');
            },
        });
    };

    const formatPrice = (price: string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(parseFloat(price));
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getStatusBadge = (status: string) => {
        return status === 'active' ? (
            <Badge variant="default" className="bg-green-100 text-green-800">Active</Badge>
        ) : (
            <Badge variant="secondary" className="bg-red-100 text-red-800">Inactive</Badge>
        );
    };

    return (
        <AppLayout>
            <Head title={`Product - ${product.name}`} />
            
            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => window.history.back()}
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold">{product.name}</h1>
                            <p className="text-muted-foreground">Product details and information</p>
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <Link href={`/admin/marketplace/products/${product.id}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="h-4 w-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="destructive" size="sm" onClick={handleDelete} disabled={processing}>
                            {processing ? (
                                <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                            ) : (
                                <Trash2 className="h-4 w-4 mr-2" />
                            )}
                            {processing ? 'Deleting...' : 'Delete'}
                        </Button>
                    </div>
                </div>

                {deleteError && (
                    <Alert variant="destructive">
                        <AlertTitle>Gagal Menghapus</AlertTitle>
                        <AlertDescription>{deleteError}</AlertDescription>
                    </Alert>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Product Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Product Name</label>
                                        <p className="text-sm font-medium">{product.name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Category</label>
                                        <p className="text-sm font-medium">{product.category.name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Base Price</label>
                                        <p className="text-sm font-medium">{formatPrice(product.base_price)}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Status</label>
                                        <div className="mt-1">
                                            <Badge variant={product.status === 'active' ? 'default' : 'secondary'}>
                                                {product.status === 'active' ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                
                                {product.description && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Description</label>
                                        <p className="text-sm mt-1 whitespace-pre-wrap">{product.description}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Product Variants Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Product Variants</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {product.variants && product.variants.length > 0 ? (
                                    <div className="space-y-4">
                                        {product.variants.map((variant) => (
                                            <div key={variant.id} className="border rounded-lg p-4">
                                                <div className="flex items-start space-x-4">
                                                    {/* Variant Image */}
                                                    <div className="flex-shrink-0">
                                                        {variant.image_url ? (
                                                            <Dialog>
                                                                <DialogTrigger asChild>
                                                                    <div className="relative cursor-pointer group">
                                                                        <img
                                                                            src={variant.image_url}
                                                                            alt={variant.variant_name}
                                                                            className="w-20 h-20 object-cover rounded-lg border"
                                                                        />
                                                                        <div className="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                                                            <Eye className="h-6 w-6 text-white" />
                                                                        </div>
                                                                    </div>
                                                                </DialogTrigger>
                                                                <DialogContent className="max-w-3xl">
                                                                    <div className="flex items-center justify-center">
                                                                        <img
                                                                            src={variant.image_url}
                                                                            alt={variant.variant_name}
                                                                            className="max-w-full max-h-[80vh] object-contain"
                                                                        />
                                                                    </div>
                                                                </DialogContent>
                                                            </Dialog>
                                                        ) : (
                                                            <div className="w-20 h-20 bg-gray-200 rounded-lg border flex items-center justify-center">
                                                                <span className="text-gray-400 text-xs">No Image</span>
                                                            </div>
                                                        )}
                                                    </div>

                                                    {/* Variant Details */}
                                                    <div className="flex-1 min-w-0">
                                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                            <div>
                                                                <label className="text-xs font-medium text-muted-foreground">SKU</label>
                                                                <p className="text-sm font-medium">{variant.sku}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-xs font-medium text-muted-foreground">Variant Name</label>
                                                                <p className="text-sm font-medium">{variant.variant_name}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-xs font-medium text-muted-foreground">Price</label>
                                                                <p className="text-sm font-medium">{formatPrice(variant.price)}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-xs font-medium text-muted-foreground">Stock</label>
                                                                <p className="text-sm font-medium">{variant.stock}</p>
                                                            </div>
                                                        </div>
                                                        <div className="mt-2">
                                                            <label className="text-xs font-medium text-muted-foreground">Status</label>
                                                            <div className="mt-1">
                                                                {getStatusBadge(variant.status)}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-muted-foreground">No variants found for this product.</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Metadata</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Product ID</label>
                                    <p className="text-sm font-medium">#{product.id}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Created At</label>
                                    <p className="text-sm">{formatDate(product.created_at)}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                                    <p className="text-sm">{formatDate(product.updated_at)}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
