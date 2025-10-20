import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import marketplace from '@/routes/admin/marketplace';

interface Category {
    id: number;
    name: string;
}

interface ProductVariant {
    sku: string;
    variant_name: string;
    price: string;
    stock: string;
    status: string;
    image_url: string;
    image?: File | null;
}

interface Props {
    categories: Category[];
}

export default function CreateProduct({ categories }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        category_id: '',
        name: '',
        description: '',
        base_price: '',
        status: 'active',
        variants: [
            {
                sku: '',
                variant_name: '',
                price: '',
                stock: '0',
                status: 'active',
                image_url: ''
            }
        ] as ProductVariant[]
    });

    const addVariant = () => {
        setData('variants', [
            ...data.variants,
            {
                sku: '',
                variant_name: '',
                price: '',
                stock: '0',
                status: 'active',
                image_url: ''
            }
        ]);
    };

    const removeVariant = (index: number) => {
        if (data.variants.length > 1) {
            const newVariants = data.variants.filter((_, i) => i !== index);
            setData('variants', newVariants);
        }
    };

    const updateVariant = (index: number, field: keyof ProductVariant, value: string | File | null) => {
        const newVariants = [...data.variants];
        newVariants[index] = { ...newVariants[index], [field]: value };
        setData('variants', newVariants);
    };

    const handleImageChange = (index: number, file: File | null) => {
        updateVariant(index, 'image', file);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/marketplace/products');
    };

    return (
        <AppLayout>
            <Head title="Create Product" />
            
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
                            <h1 className="text-2xl font-bold">Create Product</h1>
                            <p className="text-muted-foreground">Add a new product to the marketplace</p>
                        </div>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Product Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Product Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Enter product name"
                                        className={errors.name ? 'border-red-500' : ''}
                                    />
                                    {errors.name && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.name}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="category_id">Category</Label>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) => setData('category_id', value)}
                                    >
                                        <SelectTrigger className={errors.category_id ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.category_id}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="base_price">Base Price</Label>
                                    <Input
                                        id="base_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.base_price}
                                        onChange={(e) => setData('base_price', e.target.value)}
                                        placeholder="0.00"
                                        className={errors.base_price ? 'border-red-500' : ''}
                                    />
                                    {errors.base_price && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.base_price}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="status">Status</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) => setData('status', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.status}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter product description"
                                    rows={4}
                                    className={errors.description ? 'border-red-500' : ''}
                                />
                                {errors.description && (
                                    <Alert variant="destructive">
                                        <AlertDescription>{errors.description}</AlertDescription>
                                    </Alert>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Product Variants Section */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Product Variants</CardTitle>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={addVariant}
                            >
                                <Plus className="h-4 w-4 mr-2" />
                                Add Variant
                            </Button>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            At least one variant is required for each product.
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-6">
                            {data.variants.map((variant, index) => (
                                <div key={index} className="border rounded-lg p-4 space-y-4">
                                    <div className="flex items-center justify-between">
                                        <h4 className="font-medium">Variant {index + 1}</h4>
                                        {data.variants.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => removeVariant(index)}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor={`sku-${index}`}>SKU</Label>
                                            <Input
                                                id={`sku-${index}`}
                                                type="text"
                                                value={variant.sku}
                                                onChange={(e) => updateVariant(index, 'sku', e.target.value)}
                                                placeholder="Enter SKU"
                                                className={errors[`variants.${index}.sku`] ? 'border-red-500' : ''}
                                            />
                                            {errors[`variants.${index}.sku`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.sku`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor={`variant_name-${index}`}>Variant Name</Label>
                                            <Input
                                                id={`variant_name-${index}`}
                                                type="text"
                                                value={variant.variant_name}
                                                onChange={(e) => updateVariant(index, 'variant_name', e.target.value)}
                                                placeholder="e.g., Red, Large, etc."
                                                className={errors[`variants.${index}.variant_name`] ? 'border-red-500' : ''}
                                            />
                                            {errors[`variants.${index}.variant_name`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.variant_name`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor={`price-${index}`}>Price</Label>
                                            <Input
                                                id={`price-${index}`}
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={variant.price}
                                                onChange={(e) => updateVariant(index, 'price', e.target.value)}
                                                placeholder="0.00"
                                                className={errors[`variants.${index}.price`] ? 'border-red-500' : ''}
                                            />
                                            {errors[`variants.${index}.price`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.price`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor={`stock-${index}`}>Stock</Label>
                                            <Input
                                                id={`stock-${index}`}
                                                type="number"
                                                min="0"
                                                value={variant.stock}
                                                onChange={(e) => updateVariant(index, 'stock', e.target.value)}
                                                placeholder="0"
                                                className={errors[`variants.${index}.stock`] ? 'border-red-500' : ''}
                                            />
                                            {errors[`variants.${index}.stock`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.stock`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor={`status-${index}`}>Status</Label>
                                            <Select
                                                value={variant.status}
                                                onValueChange={(value) => updateVariant(index, 'status', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="active">Active</SelectItem>
                                                    <SelectItem value="inactive">Inactive</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            {errors[`variants.${index}.status`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.status`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor={`image-${index}`}>Variant Image (Optional)</Label>
                                            <Input
                                                id={`image-${index}`}
                                                type="file"
                                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] || null;
                                                    handleImageChange(index, file);
                                                }}
                                                className={errors[`variants.${index}.image`] ? 'border-red-500' : ''}
                                            />
                                            {variant.image && (
                                                <p className="text-sm text-muted-foreground">
                                                    Selected: {variant.image.name}
                                                </p>
                                            )}
                                            {errors[`variants.${index}.image`] && (
                                                <Alert variant="destructive">
                                                    <AlertDescription>{errors[`variants.${index}.image`]}</AlertDescription>
                                                </Alert>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}

                            {errors.variants && (
                                <Alert variant="destructive">
                                    <AlertDescription>{errors.variants}</AlertDescription>
                                </Alert>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="pt-6">
                        <form onSubmit={handleSubmit}>
                            <div className="flex justify-end space-x-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => window.history.back()}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Creating...' : 'Create Product'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}