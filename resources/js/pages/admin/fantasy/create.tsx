import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
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
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Save, AlertCircle, Plus, Trash2, Users, Shirt, ShoppingBag, Upload, X } from 'lucide-react';
import { Separator } from '@/components/ui/separator';

interface TeamData {
  name: string;
  slot_limit: string;
  tshirt_sizes: string[];
}

interface ShoeData {
  name: string;
  price: string;
  image: File | null;
  sizes: Array<{
    size: string;
    stock: string;
  }>;
}

interface FantasyCreateFormData {
  title: string;
  description: string;
  location: string;
  play_date: string;
  base_fee: string;
  status: string;
  teams: TeamData[];
  shoes: ShoeData[];
}

export default function CreateFantasy() {
  const { data, setData, post, processing, errors } = useForm<FantasyCreateFormData>({
    title: '',
    description: '',
    location: '',
    play_date: '',
    base_fee: '',
    status: 'draft',
    teams: [
      { name: '', slot_limit: '', tshirt_sizes: [''] },
      { name: '', slot_limit: '', tshirt_sizes: [''] }
    ],
    shoes: []
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admin/fantasy');
  };

  // Team Management Functions
  const addTeam = () => {
    setData('teams', [...data.teams, { name: '', slot_limit: '', tshirt_sizes: [''] }]);
  };

  const removeTeam = (index: number) => {
    if (data.teams.length > 2) {
      const newTeams = data.teams.filter((_, i) => i !== index);
      setData('teams', newTeams);
    }
  };

  const updateTeam = (index: number, field: keyof Omit<TeamData, 'tshirt_sizes'>, value: string) => {
    const newTeams = [...data.teams];
    newTeams[index][field] = value;
    setData('teams', newTeams);
  };

  // T-shirt Management Functions (now part of teams)
  const addTshirtToTeam = (teamIndex: number) => {
    const newTeams = [...data.teams];
    newTeams[teamIndex].tshirt_sizes.push('');
    setData('teams', newTeams);
  };

  const removeTshirtFromTeam = (teamIndex: number, tshirtIndex: number) => {
    if (data.teams[teamIndex].tshirt_sizes.length > 1) {
      const newTeams = [...data.teams];
      newTeams[teamIndex].tshirt_sizes = newTeams[teamIndex].tshirt_sizes.filter((_, i) => i !== tshirtIndex);
      setData('teams', newTeams);
    }
  };

  const updateTshirtSize = (teamIndex: number, tshirtIndex: number, value: string) => {
    const newTeams = [...data.teams];
    newTeams[teamIndex].tshirt_sizes[tshirtIndex] = value;
    setData('teams', newTeams);
  };

  // Shoes Management Functions
  const addShoe = () => {
    setData('shoes', [...data.shoes, { 
      name: '', 
      price: '', 
      image: null,
      sizes: [{ size: '', stock: '' }] 
    }]);
  };

  const removeShoe = (index: number) => {
    const newShoes = data.shoes.filter((_, i) => i !== index);
    setData('shoes', newShoes);
  };

  const updateShoe = (index: number, field: keyof Omit<ShoeData, 'sizes' | 'image'>, value: string) => {
    const newShoes = [...data.shoes];
    newShoes[index][field] = value;
    setData('shoes', newShoes);
  };

  const updateShoeImage = (index: number, file: File | null) => {
    const newShoes = [...data.shoes];
    newShoes[index].image = file;
    setData('shoes', newShoes);
  };

  const addShoeSize = (shoeIndex: number) => {
    const newShoes = [...data.shoes];
    newShoes[shoeIndex].sizes.push({ size: '', stock: '' });
    setData('shoes', newShoes);
  };

  const removeShoeSize = (shoeIndex: number, sizeIndex: number) => {
    if (data.shoes[shoeIndex].sizes.length > 1) {
      const newShoes = [...data.shoes];
      newShoes[shoeIndex].sizes = newShoes[shoeIndex].sizes.filter((_, i) => i !== sizeIndex);
      setData('shoes', newShoes);
    }
  };

  const updateShoeSize = (shoeIndex: number, sizeIndex: number, field: 'size' | 'stock', value: string) => {
    const newShoes = [...data.shoes];
    newShoes[shoeIndex].sizes[sizeIndex][field] = value;
    setData('shoes', newShoes);
  };

  const handleImageUpload = (index: number, event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      // Check file size (2MB = 2 * 1024 * 1024 bytes)
      if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        return;
      }
      
      // Check file type
      if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar');
        return;
      }
      
      updateShoeImage(index, file);
    }
  };

  const removeImage = (index: number) => {
    updateShoeImage(index, null);
  };

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Fantasy', href: '/admin/fantasy' },
    { title: 'Create', href: '/admin/fantasy/create' },
  ];

  return (
    <AppLayout>
      <Head title="Create Fantasy Event" />
      
      <div className="space-y-6 p-4">
        {/* Breadcrumb */}
        <div className="flex items-center space-x-2 text-sm text-muted-foreground">
          {breadcrumbs.map((crumb, index) => (
            <React.Fragment key={index}>
              {index > 0 && <span>/</span>}
              {index === breadcrumbs.length - 1 ? (
                <span className="text-foreground">{crumb.title}</span>
              ) : (
                <Link href={crumb.href} className="hover:text-foreground">
                  {crumb.title}
                </Link>
              )}
            </React.Fragment>
          ))}
        </div>

        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="outline" size="icon" asChild>
            <Link href="/admin/fantasy">
              <ArrowLeft className="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Create Fantasy Event</h1>
            <p className="text-muted-foreground">
              Create a new fantasy football event for participants
            </p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Basic Event Information */}
          <Card>
            <CardHeader>
              <CardTitle>Event Information</CardTitle>
              <CardDescription>
                Enter the basic details for the new fantasy event.
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Event Title */}
              <div className="space-y-2">
                <Label htmlFor="title">Event Title *</Label>
                <Input
                  id="title"
                  type="text"
                  value={data.title}
                  onChange={(e) => setData('title', e.target.value)}
                  placeholder="Enter event title"
                  className={errors.title ? 'border-red-500' : ''}
                />
                {errors.title && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.title}</AlertDescription>
                  </Alert>
                )}
              </div>

              {/* Description */}
              <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  placeholder="Enter event description"
                  rows={4}
                  className={errors.description ? 'border-red-500' : ''}
                />
                {errors.description && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.description}</AlertDescription>
                  </Alert>
                )}
              </div>

              {/* Location */}
              <div className="space-y-2">
                <Label htmlFor="location">Location *</Label>
                <Input
                  id="location"
                  type="text"
                  value={data.location}
                  onChange={(e) => setData('location', e.target.value)}
                  placeholder="Enter event location"
                  className={errors.location ? 'border-red-500' : ''}
                />
                {errors.location && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.location}</AlertDescription>
                  </Alert>
                )}
              </div>

              {/* Play Date */}
              <div className="space-y-2">
                <Label htmlFor="play_date">Play Date *</Label>
                <Input
                  id="play_date"
                  type="datetime-local"
                  value={data.play_date}
                  onChange={(e) => setData('play_date', e.target.value)}
                  className={errors.play_date ? 'border-red-500' : ''}
                />
                {errors.play_date && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.play_date}</AlertDescription>
                  </Alert>
                )}
              </div>

              {/* Base Fee */}
              <div className="space-y-2">
                <Label htmlFor="base_fee">Base Fee (IDR) *</Label>
                <Input
                  id="base_fee"
                  type="number"
                  step="0.01"
                  min="0"
                  value={data.base_fee}
                  onChange={(e) => setData('base_fee', e.target.value)}
                  placeholder="0.00"
                  className={errors.base_fee ? 'border-red-500' : ''}
                />
                {errors.base_fee && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.base_fee}</AlertDescription>
                  </Alert>
                )}
              </div>

              {/* Status */}
              <div className="space-y-2">
                <Label htmlFor="status">Status *</Label>
                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                  <SelectTrigger className={errors.status ? 'border-red-500' : ''}>
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="open">Open</SelectItem>
                    <SelectItem value="closed">Closed</SelectItem>
                    <SelectItem value="finished">Finished</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && (
                  <Alert variant="destructive">
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>{errors.status}</AlertDescription>
                  </Alert>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Teams Management */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle className="flex items-center gap-2">
                    <Users className="h-5 w-5" />
                    Teams Management *
                  </CardTitle>
                  <CardDescription>
                    Add teams for the fantasy event. Minimum 2 teams required. Each team can have their own t-shirt sizes.
                  </CardDescription>
                </div>
                <Button type="button" onClick={addTeam} size="sm">
                  <Plus className="h-4 w-4 mr-2" />
                  Add Team
                </Button>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {data.teams.map((team, teamIndex) => (
                <div key={teamIndex} className="border rounded-lg p-4 space-y-4">
                  <div className="flex items-center justify-between">
                    <h4 className="font-medium">Team {teamIndex + 1}</h4>
                    {data.teams.length > 2 && (
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => removeTeam(teamIndex)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    )}
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label>Team Name *</Label>
                      <Input
                        value={team.name}
                        onChange={(e) => updateTeam(teamIndex, 'name', e.target.value)}
                        placeholder="Enter team name"
                        className={errors[`teams.${teamIndex}.name`] ? 'border-red-500' : ''}
                      />
                      {errors[`teams.${teamIndex}.name`] && (
                        <Alert variant="destructive">
                          <AlertCircle className="h-4 w-4" />
                          <AlertDescription>{errors[`teams.${teamIndex}.name`]}</AlertDescription>
                        </Alert>
                      )}
                    </div>
                    <div className="space-y-2">
                      <Label>Slot Limit *</Label>
                      <Input
                        type="number"
                        min="1"
                        value={team.slot_limit}
                        onChange={(e) => updateTeam(teamIndex, 'slot_limit', e.target.value)}
                        placeholder="Enter slot limit"
                        className={errors[`teams.${teamIndex}.slot_limit`] ? 'border-red-500' : ''}
                      />
                      {errors[`teams.${teamIndex}.slot_limit`] && (
                        <Alert variant="destructive">
                          <AlertCircle className="h-4 w-4" />
                          <AlertDescription>{errors[`teams.${teamIndex}.slot_limit`]}</AlertDescription>
                        </Alert>
                      )}
                    </div>
                  </div>

                  <Separator />

                  {/* T-shirt Sizes for this team */}
                  <div className="space-y-4">
                    <div className="flex items-center justify-between">
                      <div>
                        <Label className="text-sm font-medium flex items-center gap-2">
                          <Shirt className="h-4 w-4" />
                          T-shirt Sizes for {team.name || `Team ${teamIndex + 1}`} *
                        </Label>
                        <p className="text-xs text-muted-foreground">At least one size required per team</p>
                      </div>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => addTshirtToTeam(teamIndex)}
                      >
                        <Plus className="h-4 w-4 mr-2" />
                        Add Size
                      </Button>
                    </div>
                    {team.tshirt_sizes.map((size, sizeIndex) => (
                      <div key={sizeIndex} className="flex items-center gap-2">
                        <Select 
                          value={size} 
                          onValueChange={(value) => updateTshirtSize(teamIndex, sizeIndex, value)}
                        >
                          <SelectTrigger className={errors[`teams.${teamIndex}.tshirt_sizes.${sizeIndex}`] ? 'border-red-500' : ''}>
                            <SelectValue placeholder="Select size" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="XS">XS</SelectItem>
                            <SelectItem value="S">S</SelectItem>
                            <SelectItem value="M">M</SelectItem>
                            <SelectItem value="L">L</SelectItem>
                            <SelectItem value="XL">XL</SelectItem>
                            <SelectItem value="XXL">XXL</SelectItem>
                          </SelectContent>
                        </Select>
                        {team.tshirt_sizes.length > 1 && (
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => removeTshirtFromTeam(teamIndex, sizeIndex)}
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>

          {/* Shoes Management */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle className="flex items-center gap-2">
                    <ShoppingBag className="h-5 w-5" />
                    Shoes Management
                  </CardTitle>
                  <CardDescription>
                    Add shoe options for the fantasy event (optional).
                  </CardDescription>
                </div>
                <Button type="button" onClick={addShoe} size="sm">
                  <Plus className="h-4 w-4 mr-2" />
                  Add Shoe
                </Button>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {data.shoes.length === 0 ? (
                <div className="text-center py-8 text-muted-foreground">
                  <ShoppingBag className="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No shoes added yet. Click "Add Shoe" to get started.</p>
                </div>
              ) : (
                data.shoes.map((shoe, shoeIndex) => (
                  <div key={shoeIndex} className="border rounded-lg p-4 space-y-4">
                    <div className="flex items-center justify-between">
                      <h4 className="font-medium">Shoe {shoeIndex + 1}</h4>
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => removeShoe(shoeIndex)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Shoe Name *</Label>
                        <Input
                          value={shoe.name}
                          onChange={(e) => updateShoe(shoeIndex, 'name', e.target.value)}
                          placeholder="Enter shoe name"
                          className={errors[`shoes.${shoeIndex}.name`] ? 'border-red-500' : ''}
                        />
                        {errors[`shoes.${shoeIndex}.name`] && (
                          <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>{errors[`shoes.${shoeIndex}.name`]}</AlertDescription>
                          </Alert>
                        )}
                      </div>
                      <div className="space-y-2">
                        <Label>Price (IDR) *</Label>
                        <Input
                          type="number"
                          step="0.01"
                          min="0"
                          value={shoe.price}
                          onChange={(e) => updateShoe(shoeIndex, 'price', e.target.value)}
                          placeholder="0.00"
                          className={errors[`shoes.${shoeIndex}.price`] ? 'border-red-500' : ''}
                        />
                        {errors[`shoes.${shoeIndex}.price`] && (
                          <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>{errors[`shoes.${shoeIndex}.price`]}</AlertDescription>
                          </Alert>
                        )}
                      </div>
                    </div>

                    {/* Image Upload */}
                    <div className="space-y-2">
                      <Label>Shoe Image</Label>
                      <div className="flex items-center gap-4">
                        <div className="flex-1">
                          <Input
                            type="file"
                            accept="image/*"
                            onChange={(e) => handleImageUpload(shoeIndex, e)}
                            className="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/80"
                          />
                          <p className="text-xs text-muted-foreground mt-1">
                            Maximum file size: 2MB. Supported formats: JPG, PNG, GIF
                          </p>
                        </div>
                        {shoe.image && (
                          <div className="flex items-center gap-2">
                            <span className="text-sm text-green-600">✓ {shoe.image.name}</span>
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => removeImage(shoeIndex)}
                            >
                              <X className="h-4 w-4" />
                            </Button>
                          </div>
                        )}
                      </div>
                      {errors[`shoes.${shoeIndex}.image`] && (
                        <Alert variant="destructive">
                          <AlertCircle className="h-4 w-4" />
                          <AlertDescription>{errors[`shoes.${shoeIndex}.image`]}</AlertDescription>
                        </Alert>
                      )}
                    </div>

                    <Separator />

                    {/* Shoe Sizes */}
                    <div className="space-y-4">
                      <div className="flex items-center justify-between">
                        <Label className="text-sm font-medium">Shoe Sizes *</Label>
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          onClick={() => addShoeSize(shoeIndex)}
                        >
                          <Plus className="h-4 w-4 mr-2" />
                          Add Size
                        </Button>
                      </div>
                      {shoe.sizes.map((size, sizeIndex) => (
                        <div key={sizeIndex} className="flex items-center gap-2">
                          <div className="flex-1">
                            <Input
                              value={size.size}
                              onChange={(e) => updateShoeSize(shoeIndex, sizeIndex, 'size', e.target.value)}
                              placeholder="Size (e.g., 42, 43, 44)"
                              className={errors[`shoes.${shoeIndex}.sizes.${sizeIndex}.size`] ? 'border-red-500' : ''}
                            />
                          </div>
                          <div className="flex-1">
                            <Input
                              type="number"
                              min="0"
                              value={size.stock}
                              onChange={(e) => updateShoeSize(shoeIndex, sizeIndex, 'stock', e.target.value)}
                              placeholder="Stock"
                              className={errors[`shoes.${shoeIndex}.sizes.${sizeIndex}.stock`] ? 'border-red-500' : ''}
                            />
                          </div>
                          {shoe.sizes.length > 1 && (
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => removeShoeSize(shoeIndex, sizeIndex)}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                ))
              )}
            </CardContent>
          </Card>

          {/* Submit Button */}
          <div className="flex items-center justify-end gap-4">
            <Button type="button" variant="outline" asChild>
              <Link href="/admin/fantasy">Cancel</Link>
            </Button>
            <Button type="submit" disabled={processing}>
              <Save className="h-4 w-4 mr-2" />
              {processing ? 'Creating...' : 'Create Fantasy Event'}
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}