import { Head, Link } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { ArrowLeft, Tv } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

interface TvCategory {
  id: number;
  name: string;
  description: string | null;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

interface Props {
  tvCategory: TvCategory;
}

export default function EditTvCategory({ tvCategory }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: tvCategory.name,
    description: tvCategory.description || '',
    status: tvCategory.status,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/tv/categories/${tvCategory.id}`);
  };

  return (
    <AppLayout>
      <Head title={`Edit ${tvCategory.name}`} />
      
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
          <span className="text-foreground">Edit {tvCategory.name}</span>
        </div>

        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="outline" size="icon" asChild>
            <Link href="/admin/tv/categories">
              <ArrowLeft className="h-4 w-4" />
            </Link>
          </Button>
          <div className="flex items-center space-x-2">
            <Tv className="h-6 w-6" />
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Edit TV Category</h1>
              <p className="text-muted-foreground">
                Update the TV category information
              </p>
            </div>
          </div>
        </div>

        {/* Form */}
        <Card>
          <CardHeader>
            <CardTitle>TV Category Information</CardTitle>
            <CardDescription>
              Update the details for this TV category.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="name">Name *</Label>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter category name"
                    className={errors.name ? 'border-red-500' : ''}
                  />
                  {errors.name && (
                    <p className="text-sm text-red-600">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status">Status *</Label>
                  <Select
                    value={data.status}
                    onValueChange={(value: 'active' | 'inactive') => setData('status', value)}
                  >
                    <SelectTrigger className={errors.status ? 'border-red-500' : ''}>
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                  </Select>
                  {errors.status && (
                    <p className="text-sm text-red-600">{errors.status}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                  placeholder="Enter category description (optional)"
                  rows={4}
                  className={errors.description ? 'border-red-500' : ''}
                />
                {errors.description && (
                  <p className="text-sm text-red-600">{errors.description}</p>
                )}
              </div>

              <div className="flex items-center gap-4">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Updating...' : 'Update TV Category'}
                </Button>
                <Button variant="outline" asChild>
                  <Link href="/admin/tv/categories">
                    Cancel
                  </Link>
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}