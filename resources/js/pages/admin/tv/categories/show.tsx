import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Tv, Calendar, Hash } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface TvCategory {
  id: number;
  name: string;
  description: string | null;
  status: 'active' | 'inactive';
  tvs_count: number;
  created_at: string;
  updated_at: string;
}

interface Props {
  tvCategory: TvCategory;
}

export default function ShowTvCategory({ tvCategory }: Props) {
  const getStatusBadge = (status: string) => {
    return status === 'active' ? (
      <Badge variant="default">Active</Badge>
    ) : (
      <Badge variant="secondary">Inactive</Badge>
    );
  };

  return (
    <AppLayout>
      <Head title={tvCategory.name} />
      
      <div className="space-y-6 p-4">
        {/* Breadcrumb */}
        <div className="flex items-center space-x-2 text-sm text-muted-foreground">
          <span>Admin</span>
          <span>/</span>
          <span>TV</span>
          <span>/</span>
          <Link href="/admin/tv/categories" className="hover:text-foreground">
            Categories
          </Link>
          <span>/</span>
          <span className="text-foreground">{tvCategory.name}</span>
        </div>

        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="icon" asChild>
              <Link href="/admin/tv/categories">
                <ArrowLeft className="h-4 w-4" />
              </Link>
            </Button>
            <div className="flex items-center space-x-2">
              <Tv className="h-6 w-6" />
              <div>
                <h1 className="text-3xl font-bold tracking-tight">{tvCategory.name}</h1>
                <p className="text-muted-foreground">
                  TV Category Details
                </p>
              </div>
            </div>
          </div>
          <Button asChild>
            <Link href={`/admin/tv/categories/${tvCategory.id}/edit`}>
              <Edit className="h-4 w-4 mr-2" />
              Edit Category
            </Link>
          </Button>
        </div>

        {/* Category Information */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Category Information</CardTitle>
              <CardDescription>
                Basic information about this TV category
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Name</span>
                <span className="font-medium">{tvCategory.name}</span>
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Status</span>
                {getStatusBadge(tvCategory.status)}
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Total TVs</span>
                <Badge variant="outline">
                  {tvCategory.tvs_count} TVs
                </Badge>
              </div>
              
              {tvCategory.description && (
                <div className="space-y-2">
                  <span className="text-sm font-medium text-muted-foreground">Description</span>
                  <p className="text-sm bg-muted p-3 rounded-md">
                    {tvCategory.description}
                  </p>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Metadata</CardTitle>
              <CardDescription>
                System information and timestamps
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Hash className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">ID</span>
                </div>
                <span className="font-mono text-sm">{tvCategory.id}</span>
              </div>
              
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">Created</span>
                </div>
                <span className="text-sm">
                  {new Date(tvCategory.created_at).toLocaleString()}
                </span>
              </div>
              
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">Updated</span>
                </div>
                <span className="text-sm">
                  {new Date(tvCategory.updated_at).toLocaleString()}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>
              Common actions for this TV category
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <Button asChild>
                <Link href={`/admin/tv?category=${tvCategory.id}`}>
                  View TVs in this Category
                </Link>
              </Button>
              <Button variant="outline" asChild>
                <Link href="/admin/tv/create">
                  Add New TV to this Category
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}