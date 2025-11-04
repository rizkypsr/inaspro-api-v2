import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Upload, X } from 'lucide-react';
import { useState, useRef } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface TvCategory {
  id: number;
  name: string;
}

interface Props {
  tvCategories: TvCategory[];
}

export default function CreateTv({ tvCategories }: Props) {
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const { data, setData, post, processing, errors } = useForm({
    tv_category_id: '',
    title: '',
    link: '',
    image: null as File | null,
    status: 'active' as 'active' | 'inactive',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('tv_category_id', data.tv_category_id);
    formData.append('title', data.title);
    formData.append('link', data.link);
    formData.append('status', data.status);
    if (data.image) {
      formData.append('image', data.image);
    }

    post('/admin/tv', {
      forceFormData: true,
    });
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // Check file size (2MB limit)
      if (file.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB');
        return;
      }

      // Check file type
      if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
      }

      setData('image', file);
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        setImagePreview(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const removeImage = () => {
    setData('image', null);
    setImagePreview(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  return (
    <AppLayout>
      <Head title="Create TV" />
      
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
          <span className="text-foreground">Create</span>
        </div>

        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="outline" size="icon" asChild>
            <Link href="/admin/tv">
              <ArrowLeft className="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Create TV</h1>
            <p className="text-muted-foreground">
              Add a new TV channel or streaming link
            </p>
          </div>
        </div>

        {/* Form */}
        <Card>
          <CardHeader>
            <CardTitle>TV Information</CardTitle>
            <CardDescription>
              Enter the details for the new TV channel
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <Label htmlFor="tv_category_id">Category *</Label>
                  <Select
                    value={data.tv_category_id}
                    onValueChange={(value) => setData('tv_category_id', value)}
                  >
                    <SelectTrigger className={errors.tv_category_id ? 'border-red-500' : ''}>
                      <SelectValue placeholder="Select a category" />
                    </SelectTrigger>
                    <SelectContent>
                      {tvCategories.map((category) => (
                        <SelectItem key={category.id} value={category.id.toString()}>
                          {category.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.tv_category_id && (
                    <p className="text-sm text-red-500">{errors.tv_category_id}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={data.status}
                    onValueChange={(value: 'active' | 'inactive') => setData('status', value)}
                  >
                    <SelectTrigger className={errors.status ? 'border-red-500' : ''}>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">Active</SelectItem>
                      <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                  </Select>
                  {errors.status && (
                    <p className="text-sm text-red-500">{errors.status}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="title">Title *</Label>
                <Input
                  id="title"
                  type="text"
                  value={data.title}
                  onChange={(e) => setData('title', e.target.value)}
                  className={errors.title ? 'border-red-500' : ''}
                  placeholder="Enter TV title"
                />
                {errors.title && (
                  <p className="text-sm text-red-500">{errors.title}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="link">Streaming Link *</Label>
                <Input
                  id="link"
                  type="url"
                  value={data.link}
                  onChange={(e) => setData('link', e.target.value)}
                  className={errors.link ? 'border-red-500' : ''}
                  placeholder="https://example.com/stream"
                />
                {errors.link && (
                  <p className="text-sm text-red-500">{errors.link}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="image">TV Logo/Image</Label>
                <div className="space-y-4">
                  {imagePreview ? (
                    <div className="relative inline-block">
                      <img
                        src={imagePreview}
                        alt="Preview"
                        className="w-32 h-32 object-cover rounded-lg border"
                      />
                      <Button
                        type="button"
                        variant="destructive"
                        size="icon"
                        className="absolute -top-2 -right-2 h-6 w-6"
                        onClick={removeImage}
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  ) : (
                    <div
                      className="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8 text-center cursor-pointer hover:border-muted-foreground/50 transition-colors"
                      onClick={() => fileInputRef.current?.click()}
                    >
                      <Upload className="h-8 w-8 mx-auto mb-2 text-muted-foreground" />
                      <p className="text-sm text-muted-foreground mb-1">
                        Click to upload TV logo or image
                      </p>
                      <p className="text-xs text-muted-foreground">
                        PNG, JPG, GIF up to 2MB
                      </p>
                    </div>
                  )}
                  
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/*"
                    onChange={handleImageChange}
                    className="hidden"
                  />
                  
                  {errors.image && (
                    <p className="text-sm text-red-500">{errors.image}</p>
                  )}
                </div>
              </div>

              <div className="flex gap-4">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create TV'}
                </Button>
                <Button type="button" variant="outline" asChild>
                  <Link href="/admin/tv">Cancel</Link>
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}