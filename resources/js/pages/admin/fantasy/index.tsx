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
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { 
  Search, 
  Filter, 
  MoreHorizontal, 
  Eye, 
  Calendar,
  MapPin,
  Users,
  Trophy,
  DollarSign,
  ArrowUpDown,
  Plus
} from 'lucide-react';

interface FantasyEvent {
  id: number;
  name: string;
  description: string;
  location: string;
  play_date: string;
  base_fee: number;
  status: 'draft' | 'published' | 'ongoing' | 'completed' | 'cancelled';
  created_at: string;
  creator: {
    id: number;
    name: string;
  };
  teams_count: number;
  registrations_count: number;
}

interface FantasyPageProps {
  fantasyEvents: {
    data: FantasyEvent[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
  filters: {
    search?: string;
    status?: string;
    date_from?: string;
    date_to?: string;
    sort_by?: string;
    sort_order?: string;
  };
}

export default function FantasyIndex({ fantasyEvents, filters }: FantasyPageProps) {
  const { flash } = usePage().props as any;
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [dateFromFilter, setDateFromFilter] = useState(filters.date_from || '');
  const [dateToFilter, setDateToFilter] = useState(filters.date_to || '');
  const [sortBy, setSortBy] = useState(filters.sort_by || 'created_at');
  const [sortOrder, setSortOrder] = useState(filters.sort_order || 'desc');

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Fantasy', href: '/admin/fantasy' },
  ];

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const params = {
      search: searchTerm || undefined,
      status: statusFilter === 'all' ? undefined : statusFilter,
      date_from: dateFromFilter || undefined,
      date_to: dateToFilter || undefined,
      sort_by: sortBy,
      sort_order: sortOrder,
    };
    router.get('/admin/fantasy', params);
  };

  const handleSort = (column: string) => {
    const newSortOrder = sortBy === column && sortOrder === 'asc' ? 'desc' : 'asc';
    setSortBy(column);
    setSortOrder(newSortOrder);
    
    const params = {
      search: searchTerm || undefined,
      status: statusFilter === 'all' ? undefined : statusFilter,
      date_from: dateFromFilter || undefined,
      date_to: dateToFilter || undefined,
      sort_by: column,
      sort_order: newSortOrder,
    };
    router.get('/admin/fantasy', params);
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
      draft: 'outline',
      published: 'default',
      ongoing: 'secondary',
      completed: 'secondary',
      cancelled: 'destructive',
    };

    const labels: Record<string, string> = {
      draft: 'Draft',
      published: 'Published',
      ongoing: 'Ongoing',
      completed: 'Completed',
      cancelled: 'Cancelled',
    };

    return (
      <Badge variant={variants[status] || 'outline'}>
        {labels[status] || status}
      </Badge>
    );
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Fantasy Events" />
      
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
            <h1 className="text-3xl font-bold tracking-tight">Fantasy Events</h1>
            <p className="text-muted-foreground">
              Manage fantasy football events and registrations
            </p>
          </div>
          <Button asChild>
            <Link href="/admin/fantasy/create">
              <Plus className="mr-2 h-4 w-4" />
              Create Event
            </Link>
          </Button>
        </div>

        {/* Content */}
        <Card>
          <CardHeader>
            <CardTitle>Fantasy Events</CardTitle>
            <CardDescription>
              View and manage all fantasy football events.
            </CardDescription>
          </CardHeader>
          <CardContent>
            {/* Filters */}
            <form onSubmit={handleSearch} className="mb-6">
              <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    placeholder="Search events..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger>
                    <SelectValue placeholder="All Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                    <SelectItem value="ongoing">Ongoing</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
                <Input
                  type="date"
                  placeholder="From Date"
                  value={dateFromFilter}
                  onChange={(e) => setDateFromFilter(e.target.value)}
                />
                <Input
                  type="date"
                  placeholder="To Date"
                  value={dateToFilter}
                  onChange={(e) => setDateToFilter(e.target.value)}
                />
                <Button type="submit">
                  <Filter className="mr-2 h-4 w-4" />
                  Filter
                </Button>
              </div>
            </form>

            {/* Table */}
            {fantasyEvents.data.length > 0 ? (
              <div className="rounded-md border">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('name')}
                      >
                        <div className="flex items-center">
                          Event Name
                          <ArrowUpDown className="ml-2 h-4 w-4" />
                        </div>
                      </TableHead>
                      <TableHead>Location</TableHead>
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('play_date')}
                      >
                        <div className="flex items-center">
                          Play Date
                          <ArrowUpDown className="ml-2 h-4 w-4" />
                        </div>
                      </TableHead>
                      <TableHead>Base Fee</TableHead>
                      <TableHead>Teams</TableHead>
                      <TableHead>Registrations</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Creator</TableHead>
                      <TableHead 
                        className="cursor-pointer hover:bg-muted/50"
                        onClick={() => handleSort('created_at')}
                      >
                        <div className="flex items-center">
                          Created
                          <ArrowUpDown className="ml-2 h-4 w-4" />
                        </div>
                      </TableHead>
                      <TableHead className="w-12">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {fantasyEvents.data.map((event) => (
                      <TableRow key={event.id}>
                        <TableCell className="font-medium">
                          <div>
                            <div className="font-semibold">{event.name}</div>
                            {event.description && (
                              <div className="text-sm text-muted-foreground truncate max-w-xs">
                                {event.description}
                              </div>
                            )}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <MapPin className="mr-1 h-4 w-4 text-muted-foreground" />
                            {event.location}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <Calendar className="mr-1 h-4 w-4 text-muted-foreground" />
                            {formatDate(event.play_date)}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <DollarSign className="mr-1 h-4 w-4 text-muted-foreground" />
                            {formatCurrency(event.base_fee)}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <Users className="mr-1 h-4 w-4 text-muted-foreground" />
                            {event.teams_count}
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <Trophy className="mr-1 h-4 w-4 text-muted-foreground" />
                            {event.registrations_count}
                          </div>
                        </TableCell>
                        <TableCell>
                          {getStatusBadge(event.status)}
                        </TableCell>
                        <TableCell>{event.creator.name}</TableCell>
                        <TableCell>{formatDate(event.created_at)}</TableCell>
                        <TableCell>
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" className="h-8 w-8 p-0">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem asChild>
                                <Link href={`/admin/fantasy/${event.id}`}>
                                  <Eye className="mr-2 h-4 w-4" />
                                  View Details
                                </Link>
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
              <div className="text-center py-8">
                <Trophy className="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No fantasy events</h3>
                <p className="mt-1 text-sm text-muted-foreground">
                  No fantasy events found matching your criteria.
                </p>
              </div>
            )}

            {/* Pagination */}
            {fantasyEvents.last_page > 1 && (
              <div className="flex items-center justify-between mt-6">
                <div className="text-sm text-muted-foreground">
                  Showing {fantasyEvents.from} to {fantasyEvents.to} of {fantasyEvents.total} results
                </div>
                <div className="flex items-center space-x-2">
                  {Array.from({ length: fantasyEvents.last_page }, (_, i) => i + 1).map((page) => (
                    <Button
                      key={page}
                      variant={page === fantasyEvents.current_page ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => {
                        const params = {
                          search: searchTerm || undefined,
                          status: statusFilter === 'all' ? undefined : statusFilter,
                          date_from: dateFromFilter || undefined,
                          date_to: dateToFilter || undefined,
                          sort_by: sortBy,
                          sort_order: sortOrder,
                          page,
                        };
                        router.get('/admin/fantasy', params);
                      }}
                    >
                      {page}
                    </Button>
                  ))}
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}