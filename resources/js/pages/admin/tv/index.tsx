import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Plus, Search, Filter, MoreHorizontal, Edit, Trash2, Eye, Tv, Image } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';

interface TvCategory {
  id: number;
  name: string;
}

interface Tv {
  id: number;
  title: string;
  link: string;
  image: string | null;
  status: 'active' | 'inactive';
  tv_category: TvCategory;
  created_at: string;
  updated_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedTvs {
  data: Tv[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
}

interface Props {
  tvs: PaginatedTvs;
  tvCategories: TvCategory[];
  filters: {
    search?: string;
    category?: string;
    status?: string;
    sort?: string;
  };
}

export default function TvIndex({ tvs, tvCategories, filters }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [selectedCategory, setSelectedCategory] = useState(filters.category || 'all');
  const [selectedStatus, setSelectedStatus] = useState(filters.status || 'all');
  const [selectedSort, setSelectedSort] = useState(filters.sort || 'created_at_desc');
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const { delete: destroy, processing } = useForm();

  const handleSearch = () => {
    const sortParts = selectedSort.split('_');
    const sortBy = sortParts.slice(0, -1).join('_');
    const sortOrder = sortParts[sortParts.length - 1];
    
    router.get('/admin/tv', {
      search: search || undefined,
      tv_category_id: selectedCategory === 'all' ? undefined : selectedCategory,
      status: selectedStatus === 'all' ? undefined : selectedStatus,
      sort_by: sortBy || undefined,
      sort_order: sortOrder || undefined,
    }, {
      preserveState: true,
      replace: true,
    });
  };

  const handleReset = () => {
    setSearch('');
    setSelectedCategory('all');
    setSelectedStatus('all');
    setSelectedSort('created_at_desc');
    router.get('/admin/tv', {}, {
      preserveState: true,
      replace: true,
    });
  };

  const handleDelete = (id: number) => {
    destroy(`/admin/tv/${id}`, {
      onSuccess: () => {
        setDeleteId(null);
      },
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
      <Head title="TV Management" />
      
      <div className="space-y-6 p-4">
        {/* Breadcrumb */}
        <div className="flex items-center space-x-2 text-sm text-muted-foreground">
          <span>Admin</span>
          <span>/</span>
          <span>TV</span>
          <span>/</span>
          <span className="text-foreground">TVs</span>
        </div>

        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Tv className="h-6 w-6" />
            <div>
              <h1 className="text-3xl font-bold tracking-tight">TV Management</h1>
              <p className="text-muted-foreground">
                Manage TV channels and streaming links
              </p>
            </div>
          </div>
          <Button asChild>
            <Link href="/admin/tv/create">
              <Plus className="h-4 w-4 mr-2" />
              Add TV
            </Link>
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-4 w-4" />
              Filters
            </CardTitle>
            <CardDescription>
              Search and filter TV channels
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search TVs..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-10"
                  onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                />
              </div>
              
              <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                <SelectTrigger>
                  <SelectValue placeholder="All Categories" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  {tvCategories.map((category) => (
                    <SelectItem key={category.id} value={category.id.toString()}>
                      {category.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              
              <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                <SelectTrigger>
                  <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
              
              <Select value={selectedSort} onValueChange={setSelectedSort}>
                <SelectTrigger>
                  <SelectValue placeholder="Sort by" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="created_at_desc">Newest First</SelectItem>
                  <SelectItem value="created_at_asc">Oldest First</SelectItem>
                  <SelectItem value="title_asc">Title A-Z</SelectItem>
                  <SelectItem value="title_desc">Title Z-A</SelectItem>
                </SelectContent>
              </Select>
              
              <div className="flex gap-2">
                <Button onClick={handleSearch} className="flex-1">
                  Search
                </Button>
                <Button variant="outline" onClick={handleReset}>
                  Reset
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* TV Table */}
        <Card>
          <CardHeader>
            <CardTitle>TVs ({tvs.total})</CardTitle>
            <CardDescription>
              List of all TV channels and streaming links
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Image</TableHead>
                    <TableHead>Title</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Created</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {tvs.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-8">
                        <div className="flex flex-col items-center gap-2">
                          <Tv className="h-8 w-8 text-muted-foreground" />
                          <p className="text-muted-foreground">No TVs found</p>
                          <Button asChild size="sm">
                            <Link href="/admin/tv/create">Add First TV</Link>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ) : (
                    tvs.data.map((tv) => (
                      <TableRow key={tv.id}>
                        <TableCell>
                          <div className="w-12 h-12 bg-muted rounded-md flex items-center justify-center overflow-hidden">
                            {tv.image ? (
                              <img 
                                src={`/storage/${tv.image}`} 
                                alt={tv.title}
                                className="w-full h-full object-cover"
                              />
                            ) : (
                              <Image className="h-6 w-6 text-muted-foreground" />
                            )}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div>
                            <div className="font-medium">{tv.title}</div>
                            <div className="text-sm text-muted-foreground truncate max-w-xs">
                              {tv.link}
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">
                            {tv.tv_category.name}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {getStatusBadge(tv.status)}
                        </TableCell>
                        <TableCell>
                          <div className="text-sm">
                            {new Date(tv.created_at).toLocaleDateString()}
                          </div>
                        </TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="icon">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/tv/${tv.id}`}>
                                  <Eye className="h-4 w-4 mr-2" />
                                  View
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/tv/${tv.id}/edit`}>
                                  <Edit className="h-4 w-4 mr-2" />
                                  Edit
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() => setDeleteId(tv.id)}
                                className="text-red-600"
                              >
                                <Trash2 className="h-4 w-4 mr-2" />
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
            {tvs.last_page > 1 && (
              <div className="flex items-center justify-between mt-4">
                <div className="text-sm text-muted-foreground">
                  Showing {((tvs.current_page - 1) * tvs.per_page) + 1} to{' '}
                  {Math.min(tvs.current_page * tvs.per_page, tvs.total)} of {tvs.total} results
                </div>
                <div className="flex gap-2">
                  {tvs.links.map((link, index) => (
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
      <AlertDialog open={deleteId !== null} onOpenChange={() => setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              This action cannot be undone. This will permanently delete the TV
              and remove all associated data.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => deleteId && handleDelete(deleteId)}
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