import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, ExternalLink, Calendar, Hash, Image, Play } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

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

interface Props {
  tv: Tv;
}

export default function ShowTv({ tv }: Props) {
  const getStatusBadge = (status: string) => {
    return status === 'active' ? (
      <Badge variant="default">Active</Badge>
    ) : (
      <Badge variant="secondary">Inactive</Badge>
    );
  };

  return (
    <AppLayout>
      <Head title={tv.title} />
      
      <div className="space-y-6 p-4">
        {/* Breadcrumb */}
        <div className="flex items-center space-x-2 text-sm text-muted-foreground">
          <span>Admin</span>
          <span>/</span>
          <span>TV</span>
          <span>/</span>
          <Link href="/admin/tv" className="hover:text-foreground">
            TVs
          </Link>
          <span>/</span>
          <span className="text-foreground">{tv.title}</span>
        </div>

        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="icon" asChild>
              <Link href="/admin/tv">
                <ArrowLeft className="h-4 w-4" />
              </Link>
            </Button>
            <div className="flex items-center space-x-2">
              <Play className="h-6 w-6" />
              <div>
                <h1 className="text-3xl font-bold tracking-tight">{tv.title}</h1>
                <p className="text-muted-foreground">
                  TV Channel Details
                </p>
              </div>
            </div>
          </div>
          <Button asChild>
            <Link href={`/admin/tv/${tv.id}/edit`}>
              <Edit className="h-4 w-4 mr-2" />
              Edit TV
            </Link>
          </Button>
        </div>

        {/* TV Information */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>TV Information</CardTitle>
              <CardDescription>
                Basic information about this TV channel
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Title</span>
                <span className="font-medium">{tv.title}</span>
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Category</span>
                <Badge variant="outline">
                  {tv.tv_category.name}
                </Badge>
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-muted-foreground">Status</span>
                {getStatusBadge(tv.status)}
              </div>
              
              <div className="space-y-2">
                <span className="text-sm font-medium text-muted-foreground">Streaming Link</span>
                <div className="flex items-center gap-2">
                  <code className="text-sm bg-muted p-2 rounded-md flex-1 break-all">
                    {tv.link}
                  </code>
                  <Button size="sm" variant="outline" asChild>
                    <a href={tv.link} target="_blank" rel="noopener noreferrer">
                      <ExternalLink className="h-4 w-4" />
                    </a>
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>TV Logo/Image</CardTitle>
              <CardDescription>
                Visual representation of the TV channel
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex items-center justify-center">
                {tv.image ? (
                  <div className="relative">
                    <img
                      src={tv.image?.startsWith('http') ? tv.image : `/storage/${tv.image}`}
                      alt={tv.title}
                      className="w-48 h-48 object-cover rounded-lg border shadow-sm"
                    />
                  </div>
                ) : (
                  <div className="w-48 h-48 bg-muted rounded-lg border flex items-center justify-center">
                    <div className="text-center">
                      <Image className="h-12 w-12 mx-auto mb-2 text-muted-foreground" />
                      <p className="text-sm text-muted-foreground">No image uploaded</p>
                    </div>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Metadata */}
        <Card>
          <CardHeader>
            <CardTitle>Metadata</CardTitle>
            <CardDescription>
              System information and timestamps
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Hash className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">ID</span>
                </div>
                <span className="font-mono text-sm">{tv.id}</span>
              </div>
              
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">Created</span>
                </div>
                <span className="text-sm">
                  {new Date(tv.created_at).toLocaleString()}
                </span>
              </div>
              
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium text-muted-foreground">Updated</span>
                </div>
                <span className="text-sm">
                  {new Date(tv.updated_at).toLocaleString()}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>
              Common actions for this TV channel
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <Button asChild>
                <a href={tv.link} target="_blank" rel="noopener noreferrer">
                  <Play className="h-4 w-4 mr-2" />
                  Watch Stream
                </a>
              </Button>
              <Button variant="outline" asChild>
                <Link href={`/admin/tv/categories/${tv.tv_category.id}`}>
                  View Category
                </Link>
              </Button>
              <Button variant="outline" asChild>
                <Link href={`/admin/tv?category=${tv.tv_category.id}`}>
                  View Similar TVs
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}