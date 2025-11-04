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
import { Plus, Search, MoreHorizontal, Edit, Trash2, Eye, Tv } from 'lucide-react';
import { useForm } from '@inertiajs/react';

interface TvCategory {
  id: number;
  name: string;
  description: string | null;
  status: 'active' | 'inactive';
  tvs_count: number;
  created_at: string;
  updated_at: string;
}

interface PaginatedTvCategories {
  data: TvCategory[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Props {
  tvCategories: PaginatedTvCategories;
  filters: {
    search?: string;
    status?: string;
  };
}

export default function TvCategories({ tvCategories, filters }: Props) {
  const { flash } = usePage().props as any;
  const [search, setSearch] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [deleteCategory, setDeleteCategory] = useState<TvCategory | null>(null);
  
  const { delete: destroy, processing } = useForm();

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'TV', href: '/admin/tv' },
    { title: 'Categories', href: '/admin/tv/categories' },
  ];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/tv/categories', {
      search: search || undefined,
      status: statusFilter === 'all' ? undefined : statusFilter,
    }, {
      preserveState: true,
      replace: true,
    });
  };

  const handleDelete = () => {
    if (!deleteCategory) return;
    
    destroy(`/admin/tv/categories/${deleteCategory.id}`, {
      onSuccess: () => setDeleteCategory(null),
    });
  };

  const getStatusBadge = (status: string) => {
    return status === 'active' ? (
      <Badge variant="default">Active</Badge>
    ) : (
      <Badge variant="secondary">Inactive</Badge>
    );
  };

  return (
    <AppLayout>
      <Head title="TV Categories" />
      
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

        {/* Breadcrumb */}
        <div className="flex items-center space-x-2 text-sm text-muted-foreground">
          {breadcrumbs.map((crumb, index) => (
            <React.Fragment key={index}>
              {index > 0 && <span>/</span>}
              <Link href={crumb.href} className="hover:text-foreground">
                {crumb.title}
              </Link>
            </React.Fragment>
          ))}
        </div>

        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <Tv className="h-6 w-6" />
              <h1 className="text-2xl font-bold">TV Categories</h1>
            </div>
          </div>
          <Button asChild>
            <Link href="/admin/tv/categories/create">
              <Plus className="h-4 w-4 mr-2" />
              Add Category
            </Link>
          </Button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>TV Categories</CardTitle>
            <CardDescription>
              Manage TV categories for organizing TV channels and content.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {/* Search and Filters */}
            <form onSubmit={handleSearch} className="flex gap-4">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  placeholder="Search categories..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-10"
                />
              </div>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="px-3 py-2 border border-input bg-background rounded-md text-sm"
              >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <Button type="submit">Search</Button>
            </form>

            {/* Table */}
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>TVs</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead className="w-[70px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {tvCategories.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                        No TV categories found.
                      </TableCell>
                    </TableRow>
                  ) : (
                    tvCategories.data.map((category) => (
                      <TableRow key={category.id}>
                        <TableCell className="font-medium">{category.name}</TableCell>
                        <TableCell className="max-w-xs truncate">
                          {category.description || '-'}
                        </TableCell>
                        <TableCell>
                          {getStatusBadge(category.status)}
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">
                            {category.tvs_count} TVs
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {new Date(category.created_at).toLocaleDateString()}
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
                                <Link href={`/admin/tv/categories/${category.id}`}>
                                  <Eye className="mr-2 h-4 w-4" />
                                  View
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/tv/categories/${category.id}/edit`}>
                                  <Edit className="mr-2 h-4 w-4" />
                                  Edit
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() => setDeleteCategory(category)}
                                className="text-red-600"
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>

            {/* Pagination */}
            {tvCategories.last_page > 1 && (
              <div className="flex items-center justify-between mt-6">
                <div className="text-sm text-muted-foreground">
                  Showing {((tvCategories.current_page - 1) * tvCategories.per_page) + 1} to{' '}
                  {Math.min(tvCategories.current_page * tvCategories.per_page, tvCategories.total)} of{' '}
                  {tvCategories.total} results
                </div>
                <div className="flex gap-2">
                  {tvCategories.links.map((link, index) => (
                    <Button
                      key={index}
                      variant={link.active ? "default" : "outline"}
                      size="sm"
                      disabled={!link.url}
                      asChild={!!link.url}
                    >
                      {link.url ? (
                        <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} />
                      ) : (
                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                      )}
                    </Button>
                  ))}
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Delete Confirmation Dialog */}
        <AlertDialog open={!!deleteCategory} onOpenChange={() => setDeleteCategory(null)}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Are you sure?</AlertDialogTitle>
              <AlertDialogDescription>
                This action cannot be undone. This will permanently delete the TV category
                "{deleteCategory?.name}" and remove it from our servers.
                {deleteCategory && deleteCategory.tvs_count > 0 && (
                  <span className="block mt-2 text-red-600 font-medium">
                    Warning: This category has {deleteCategory.tvs_count} TVs.
                    You cannot delete it until all TVs are moved or deleted.
                  </span>
                )}
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                onClick={handleDelete}
                disabled={processing || (deleteCategory?.tvs_count || 0) > 0}
                className="bg-red-600 hover:bg-red-700"
              >
                {processing ? 'Deleting...' : 'Delete'}
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </AppLayout>
  );
}