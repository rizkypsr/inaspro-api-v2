import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Plus, Search, MoreHorizontal, Edit, Trash2, Eye, Filter, ArrowUpDown } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import { LogisticsPageProps, ShippingRate } from '@/types/logistics';

export default function Logistics({ shippingRates, provinces, filters }: LogisticsPageProps) {
    const { flash } = usePage().props as any;
    const [search, setSearch] = useState(filters.search || '');
    const [provinceFilter, setProvinceFilter] = useState(filters.province_id || 'all');
    const [courierFilter, setCourierFilter] = useState(filters.courier || '');
    const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
    const [sortOrder, setSortOrder] = useState(filters.sort_order || 'desc');
    const [deleteShippingRate, setDeleteShippingRate] = useState<ShippingRate | null>(null);
    const [selectedItems, setSelectedItems] = useState<number[]>([]);
    
    const { delete: destroy, processing } = useForm();

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Marketplace', href: '/admin/marketplace' },
        { title: 'Logistics', href: '/admin/marketplace/logistics' },
    ];

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/marketplace/logistics', {
            search,
            province_id: provinceFilter === 'all' ? '' : provinceFilter,
            courier: courierFilter,
            sort_by: sortBy,
            sort_order: sortOrder,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSort = (column: string) => {
        const newSortOrder = sortBy === column && sortOrder === 'asc' ? 'desc' : 'asc';
        setSortBy(column);
        setSortOrder(newSortOrder);
        
        router.get('/admin/marketplace/logistics', {
            search,
            province_id: provinceFilter === 'all' ? '' : provinceFilter,
            courier: courierFilter,
            sort_by: column,
            sort_order: newSortOrder,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (shippingRate: ShippingRate) => {
        destroy(`/admin/marketplace/logistics/${shippingRate.id}`, {
            onSuccess: () => setDeleteShippingRate(null),
        });
    };

    const handleBulkDelete = () => {
        if (selectedItems.length === 0) return;
        
        router.delete('/admin/marketplace/logistics', {
            data: { ids: selectedItems },
            onSuccess: () => setSelectedItems([]),
        });
    };

    const toggleSelectAll = () => {
        if (selectedItems.length === shippingRates.data.length) {
            setSelectedItems([]);
        } else {
            setSelectedItems(shippingRates.data.map(item => item.id));
        }
    };

    const toggleSelectItem = (id: number) => {
        setSelectedItems(prev => 
            prev.includes(id) 
                ? prev.filter(item => item !== id)
                : [...prev, id]
        );
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Logistics" />
            
            <div className="space-y-6 p-4">
                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                        {flash.error}
                    </div>
                )}

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Logistics</h1>
                        <p className="text-muted-foreground">
                            Manage shipping rates and logistics operations
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        {selectedItems.length > 0 && (
                            <Button variant="destructive" onClick={handleBulkDelete}>
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete Selected ({selectedItems.length})
                            </Button>
                        )}
                        <Button asChild>
                            <Link href="/admin/marketplace/logistics/create">
                                <Plus className="mr-2 h-4 w-4" />
                                Add Shipping Rate
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Content */}
                <Card>
                    <CardHeader>
                        <CardTitle>Shipping Rates</CardTitle>
                        <CardDescription>
                            Manage shipping rates for different provinces and couriers.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {/* Filters */}
                        <form onSubmit={handleSearch} className="mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                                    <Input
                                        placeholder="Search shipping rates..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                                <Select value={provinceFilter} onValueChange={setProvinceFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Provinces" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Provinces</SelectItem>
                                        {provinces.map((province) => (
                                            <SelectItem key={province.id} value={province.id.toString()}>
                                                {province.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <Input
                                    placeholder="Filter by courier..."
                                    value={courierFilter}
                                    onChange={(e) => setCourierFilter(e.target.value)}
                                />
                                <Button type="submit">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>
                        </form>

                        {/* Table */}
                        {shippingRates.data.length > 0 ? (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="w-12">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedItems.length === shippingRates.data.length}
                                                    onChange={toggleSelectAll}
                                                    className="rounded border-gray-300"
                                                />
                                            </TableHead>
                                            <TableHead>
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('province_id')}
                                                    className="h-auto p-0 font-semibold"
                                                >
                                                    Province
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </TableHead>
                                            <TableHead>
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('courier')}
                                                    className="h-auto p-0 font-semibold"
                                                >
                                                    Courier
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </TableHead>
                                            <TableHead>
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('rate')}
                                                    className="h-auto p-0 font-semibold"
                                                >
                                                    Rate
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </TableHead>
                                            <TableHead>
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort('created_at')}
                                                    className="h-auto p-0 font-semibold"
                                                >
                                                    Created
                                                    <ArrowUpDown className="ml-2 h-4 w-4" />
                                                </Button>
                                            </TableHead>
                                            <TableHead className="w-12"></TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {shippingRates.data.map((shippingRate) => (
                                            <TableRow key={shippingRate.id}>
                                                <TableCell>
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedItems.includes(shippingRate.id)}
                                                        onChange={() => toggleSelectItem(shippingRate.id)}
                                                        className="rounded border-gray-300"
                                                    />
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    {shippingRate.province?.name || 'Unknown Province'}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {shippingRate.courier}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="font-mono">
                                                    {formatCurrency(shippingRate.rate)}
                                                </TableCell>
                                                <TableCell className="text-muted-foreground">
                                                    {formatDate(shippingRate.created_at)}
                                                </TableCell>
                                                <TableCell>
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" className="h-8 w-8 p-0">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/admin/marketplace/logistics/${shippingRate.id}`}>
                                                                    <Eye className="mr-2 h-4 w-4" />
                                                                    View
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/admin/marketplace/logistics/${shippingRate.id}/edit`}>
                                                                    <Edit className="mr-2 h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                className="text-red-600"
                                                                onClick={() => setDeleteShippingRate(shippingRate)}
                                                            >
                                                                <Trash2 className="mr-2 h-4 w-4" />
                                                                Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <p className="text-muted-foreground">
                                    No shipping rates found. Create your first shipping rate to get started.
                                </p>
                                <Button asChild className="mt-4">
                                    <Link href="/admin/marketplace/logistics/create">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Add Shipping Rate
                                    </Link>
                                </Button>
                            </div>
                        )}

                        {/* Pagination */}
                        {shippingRates.data.length > 0 && shippingRates.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((shippingRates.current_page - 1) * shippingRates.per_page) + 1} to{' '}
                                    {Math.min(shippingRates.current_page * shippingRates.per_page, shippingRates.total)} of{' '}
                                    {shippingRates.total} results
                                </div>
                                <div className="flex items-center space-x-2">
                                    {shippingRates.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Dialog */}
            <AlertDialog open={!!deleteShippingRate} onOpenChange={() => setDeleteShippingRate(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete the shipping rate for{' '}
                            <strong>{deleteShippingRate?.province?.name}</strong> - <strong>{deleteShippingRate?.courier}</strong>.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => deleteShippingRate && handleDelete(deleteShippingRate)}
                            disabled={processing}
                            className="bg-red-600 hover:bg-red-700"
                        >
                            {processing ? 'Deleting...' : 'Delete'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}