import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
  ArrowLeft,
  Calendar,
  MapPin,
  DollarSign,
  Users,
  Trophy,
  Shirt,
  ShoppingBag,
  CreditCard,
  User,
  Package,
  CheckCircle,
  XCircle,
  Clock,
  AlertCircle,
  Edit,
  Save
} from 'lucide-react';

interface FantasyEvent {
  id: number;
  title: string;
  description: string;
  location: string;
  play_date: string;
  base_fee: number;
  status: 'draft' | 'open' | 'closed' | 'finished';
  created_at: string;
  creator: {
    id: number;
    name: string;
  };
}

interface FantasyEventTeam {
  id: number;
  name: string;
  slot_limit: number;
  created_at: string;
  registrations_count: number;
  tshirt_options?: Array<{
    id: number;
    size: string;
  }>;
}

interface FantasyTshirtOption {
  id: number;
  size: string;
  fantasy_event_team: {
    id: number;
    name: string;
  };
}

interface FantasyShoe {
  id: number;
  name: string;
  price: number;
  created_at: string;
  sizes: Array<{
    id: number;
    size: string;
    stock: number;
    reserved_stock: number;
    available_stock: number;
  }>;
}

interface FantasyRegistration {
  id: number;
  registration_fee: number;
  status: 'pending' | 'confirmed' | 'cancelled';
  created_at: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
  fantasy_event_team: {
    id: number;
    name: string;
  };
  registration_items: Array<{
    id: number;
    item_type: 'tshirt' | 'shoe';
    price: number;
    fantasy_tshirt_option?: {
      id: number;
      size: string;
    };
    fantasy_shoe_size?: {
      id: number;
      size: string;
      fantasy_shoe: {
        id: number;
        name: string;
      };
    };
  }>;
  payments: Array<{
    id: number;
    amount: number;
    status: 'pending' | 'confirmed' | 'failed' | 'refunded' | 'waiting' | 'rejected';
    method: string;
    created_at: string;
  }>;
}

interface FantasyPayment {
  id: number;
  amount: number;
  status: 'pending' | 'confirmed' | 'failed' | 'refunded' | 'waiting' | 'rejected';
  method: string;
  created_at: string;
  fantasy_registration: {
    id: number;
    registration_code: string;
    user: {
      id: number;
      name: string;
      email: string;
    };
  };
}

interface FantasyShowProps {
  fantasyEvent: FantasyEvent;
  teams: FantasyEventTeam[];
  tshirtOptions: FantasyTshirtOption[];
  shoes: FantasyShoe[];
  registrations: FantasyRegistration[];
  payments: FantasyPayment[];
}

export default function FantasyShow({ 
  fantasyEvent, 
  teams, 
  tshirtOptions, 
  shoes, 
  registrations,
  payments 
}: FantasyShowProps) {
  const [activeTab, setActiveTab] = useState('overview');
  const [editingTeam, setEditingTeam] = useState<FantasyEventTeam | null>(null);
    const [editingPayment, setEditingPayment] = useState<FantasyPayment | null>(null);
    const [editingEventStatus, setEditingEventStatus] = useState(false);
    const [editingTshirt, setEditingTshirt] = useState<FantasyTshirtOption | null>(null);

  // Team update form
  const teamForm = useForm({
    name: '',
    slot_limit: 0,
  });

  // Payment status update form
  const paymentForm = useForm({
    status: '',
  });

  const tshirtForm = useForm({
    size: '',
  });

  // Event status update form
  const eventStatusForm = useForm({
    status: fantasyEvent.status,
  });

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Fantasy', href: '/admin/fantasy' },
    { title: fantasyEvent.title, href: `/admin/fantasy/${fantasyEvent.id}` },
  ];

  const getStatusBadge = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
      draft: 'outline',
      published: 'default',
      ongoing: 'secondary',
      completed: 'secondary',
      cancelled: 'destructive',
      pending: 'outline',
      confirmed: 'default',
      waiting: 'outline',
      rejected: 'destructive',
      failed: 'destructive',
      refunded: 'secondary',
    };

    const labels: Record<string, string> = {
      draft: 'Draft',
      published: 'Published',
      ongoing: 'Ongoing',
      completed: 'Completed',
      cancelled: 'Cancelled',
      pending: 'Pending',
      confirmed: 'Confirmed',
      waiting: 'Waiting',
      rejected: 'Rejected',
      failed: 'Failed',
      refunded: 'Refunded',
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
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatDateOnly = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  // Calculate totals
  const totalRegistrations = registrations.length;
  const totalRevenue = payments
    .filter(p => p.status === 'confirmed')
    .reduce((sum, payment) => sum + payment.amount, 0);

  const totalTeamSlots = teams.reduce((sum, team) => sum + team.slot_limit, 0);
  const totalRegisteredSlots = registrations.filter(r => r.status === 'confirmed').length;

  const handleEditTeam = (team: FantasyEventTeam) => {
    setEditingTeam(team);
    teamForm.setData({
      name: team.name,
      slot_limit: team.slot_limit,
    });
  };

  const handleUpdateTeam = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingTeam) return;

    teamForm.put(`/admin/fantasy/teams/${editingTeam.id}`, {
      onSuccess: () => {
        setEditingTeam(null);
        teamForm.reset();
      },
    });
  };

  const handleEditPayment = (payment: FantasyPayment) => {
    setEditingPayment(payment);
    paymentForm.setData({
      status: payment.status,
    });
  };

  const handleUpdatePaymentStatus = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPayment) return;

    paymentForm.put(`/admin/fantasy/payments/${editingPayment.id}`, {
      onSuccess: () => {
        setEditingPayment(null);
        paymentForm.reset();
      },
    });
  };

  const handleUpdateEventStatus = (e: React.FormEvent) => {
        e.preventDefault();

        eventStatusForm.put(`/admin/fantasy/${fantasyEvent.id}/status`, {
            onSuccess: () => {
                setEditingEventStatus(false);
            },
        });
    };

    const handleEditTshirt = (tshirt: { id: number; size: string }, team: FantasyEventTeam) => {
        const fullTshirt: FantasyTshirtOption = {
            id: tshirt.id,
            size: tshirt.size,
            fantasy_event_team: {
                id: team.id,
                name: team.name,
            }
        };
        setEditingTshirt(fullTshirt);
        tshirtForm.setData('size', tshirt.size);
    };

    const handleUpdateTshirt = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingTshirt) return;

        tshirtForm.put(`/admin/fantasy/tshirts/${editingTshirt.id}`, {
            onSuccess: () => {
                setEditingTshirt(null);
                tshirtForm.reset();
            },
        });
    };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Fantasy Event: ${fantasyEvent.title}`} />
      
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Button variant="outline" size="sm" asChild>
              <Link href="/admin/fantasy">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Fantasy Events
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">{fantasyEvent.title}</h1>
              <p className="text-muted-foreground">
                Created by {fantasyEvent.creator.name} on {formatDate(fantasyEvent.created_at)}
              </p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            {getStatusBadge(fantasyEvent.status)}
            <Dialog open={editingEventStatus} onOpenChange={setEditingEventStatus}>
              <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                  <Edit className="h-4 w-4 mr-1" />
                  Update Status
                </Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>Update Event Status</DialogTitle>
                  <DialogDescription>
                    Change the status of this fantasy event.
                  </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleUpdateEventStatus}>
                  <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-4 items-center gap-4">
                      <Label htmlFor="event-status" className="text-right">
                        Status
                      </Label>
                      <Select
                         value={eventStatusForm.data.status}
                         onValueChange={(value: 'draft' | 'open' | 'closed' | 'finished') => eventStatusForm.setData('status', value)}
                       >
                        <SelectTrigger className="col-span-3">
                          <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                           <SelectItem value="draft">Draft</SelectItem>
                           <SelectItem value="open">Open</SelectItem>
                           <SelectItem value="closed">Closed</SelectItem>
                           <SelectItem value="finished">Finished</SelectItem>
                         </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <DialogFooter>
                    <Button type="submit" disabled={eventStatusForm.processing}>
                      <Save className="h-4 w-4 mr-1" />
                      {eventStatusForm.processing ? 'Updating...' : 'Update Status'}
                    </Button>
                  </DialogFooter>
                </form>
              </DialogContent>
            </Dialog>
          </div>
        </div>

        {/* Overview Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Registrations</CardTitle>
              <Trophy className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalRegistrations}</div>
              <p className="text-xs text-muted-foreground">
                {totalRegisteredSlots} confirmed of {totalTeamSlots} slots
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatCurrency(totalRevenue)}</div>
              <p className="text-xs text-muted-foreground">
                From confirmed payments
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Teams</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{teams.length}</div>
              <p className="text-xs text-muted-foreground">
                {totalTeamSlots} total slots
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Base Fee</CardTitle>
              <CreditCard className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatCurrency(fantasyEvent.base_fee)}</div>
              <p className="text-xs text-muted-foreground">
                Per registration
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Event Details */}
        <Card>
          <CardHeader>
            <CardTitle>Event Details</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div className="flex items-center space-x-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="font-medium">Play Date:</span>
                  <span>{formatDateOnly(fantasyEvent.play_date)}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <MapPin className="h-4 w-4 text-muted-foreground" />
                  <span className="font-medium">Location:</span>
                  <span>{fantasyEvent.location}</span>
                </div>
              </div>
              <div className="space-y-4">
                <div className="flex items-center space-x-2">
                  <DollarSign className="h-4 w-4 text-muted-foreground" />
                  <span className="font-medium">Base Fee:</span>
                  <span>{formatCurrency(fantasyEvent.base_fee)}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <User className="h-4 w-4 text-muted-foreground" />
                  <span className="font-medium">Creator:</span>
                  <span>{fantasyEvent.creator.name}</span>
                </div>
              </div>
            </div>
            {fantasyEvent.description && (
              <div className="mt-4">
                <span className="font-medium">Description:</span>
                <p className="mt-2 text-muted-foreground">{fantasyEvent.description}</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Tabs */}
        <Tabs value={activeTab} onValueChange={setActiveTab}>
          <TabsList className="grid w-full grid-cols-6">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="teams">Teams ({teams.length})</TabsTrigger>
            <TabsTrigger value="tshirts">T-Shirts</TabsTrigger>
            <TabsTrigger value="shoes">Shoes ({shoes.length})</TabsTrigger>
            <TabsTrigger value="registrations">Registrations ({registrations.length})</TabsTrigger>
            <TabsTrigger value="payments">Payments ({payments.length})</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Registration Status</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {['pending', 'confirmed', 'cancelled'].map(status => {
                      const count = registrations.filter(r => r.status === status).length;
                      return (
                        <div key={status} className="flex justify-between items-center">
                          <span className="capitalize">{status}</span>
                          <div className="flex items-center space-x-2">
                            <span className="font-medium">{count}</span>
                            {getStatusBadge(status)}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardHeader>
                  <CardTitle>Payment Status</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {['pending', 'confirmed', 'waiting', 'failed', 'rejected', 'refunded'].map(status => {
                      const count = payments.filter(p => p.status === status).length;
                      const amount = payments
                        .filter(p => p.status === status)
                        .reduce((sum, p) => sum + p.amount, 0);
                      return (
                        <div key={status} className="flex justify-between items-center">
                          <span className="capitalize">{status}</span>
                          <div className="flex items-center space-x-2">
                            <span className="font-medium">{count} ({formatCurrency(amount)})</span>
                            {getStatusBadge(status)}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="teams" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Teams</CardTitle>
                <CardDescription>
                  Manage teams for this fantasy event.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {teams.length > 0 ? (
                  <div className="rounded-md border">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Team Name</TableHead>
                          <TableHead>Slot Limit</TableHead>
                          <TableHead>Registrations</TableHead>
                          <TableHead>Available Slots</TableHead>
                          <TableHead>T-Shirt Sizes</TableHead>
                          <TableHead>Created</TableHead>
                          <TableHead>Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {teams.map((team) => (
                          <TableRow key={team.id}>
                            <TableCell className="font-medium">{team.name}</TableCell>
                            <TableCell>{team.slot_limit}</TableCell>
                            <TableCell>{team.registrations_count}</TableCell>
                            <TableCell>
                              <span className={team.slot_limit - team.registrations_count <= 0 ? 'text-red-600' : 'text-green-600'}>
                                {team.slot_limit - team.registrations_count}
                              </span>
                            </TableCell>
                            <TableCell>
                              {team.tshirt_options?.map(option => option.size).join(', ') || 'No sizes'}
                            </TableCell>
                            <TableCell>{formatDate(team.created_at)}</TableCell>
                            <TableCell>
                              <Dialog>
                                <DialogTrigger asChild>
                                  <Button variant="outline" size="sm" onClick={() => handleEditTeam(team)}>
                                    <Edit className="h-4 w-4 mr-1" />
                                    Edit
                                  </Button>
                                </DialogTrigger>
                                <DialogContent>
                                  <DialogHeader>
                                    <DialogTitle>Edit Team</DialogTitle>
                                    <DialogDescription>
                                      Update team information for {team.name}.
                                    </DialogDescription>
                                  </DialogHeader>
                                  <form onSubmit={handleUpdateTeam}>
                                    <div className="grid gap-4 py-4">
                                      <div className="grid grid-cols-4 items-center gap-4">
                                        <Label htmlFor="name" className="text-right">
                                          Name
                                        </Label>
                                        <Input
                                          id="name"
                                          value={teamForm.data.name}
                                          onChange={(e) => teamForm.setData('name', e.target.value)}
                                          className="col-span-3"
                                        />
                                      </div>
                                      <div className="grid grid-cols-4 items-center gap-4">
                                        <Label htmlFor="slot_limit" className="text-right">
                                          Slot Limit
                                        </Label>
                                        <Input
                                          id="slot_limit"
                                          type="number"
                                          value={teamForm.data.slot_limit}
                                          onChange={(e) => teamForm.setData('slot_limit', parseInt(e.target.value))}
                                          className="col-span-3"
                                        />
                                      </div>
                                    </div>
                                    <DialogFooter>
                                      <Button type="submit" disabled={teamForm.processing}>
                                        <Save className="h-4 w-4 mr-1" />
                                        {teamForm.processing ? 'Saving...' : 'Save Changes'}
                                      </Button>
                                    </DialogFooter>
                                  </form>
                                </DialogContent>
                              </Dialog>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Users className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No teams</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                      No teams have been created for this event yet.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="tshirts" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>T-Shirt Options</CardTitle>
                <CardDescription>
                  Available t-shirt sizes by team for this event.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {teams.length > 0 ? (
                  <div className="space-y-6">
                    {teams.map((team) => (
                      <div key={team.id} className="border rounded-lg p-4">
                        <h3 className="text-lg font-semibold mb-4">{team.name}</h3>
                        {team.tshirt_options && team.tshirt_options.length > 0 ? (
                          <div className="rounded-md border">
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>Size</TableHead>
                                  <TableHead>Team</TableHead>
                                  <TableHead>Actions</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {team.tshirt_options.map((option) => (
                                  <TableRow key={option.id}>
                                    <TableCell className="font-medium">{option.size}</TableCell>
                                    <TableCell>{team.name}</TableCell>
                                    <TableCell>
                                      <Dialog>
                                        <DialogTrigger asChild>
                                          <Button 
                                            variant="outline" 
                                            size="sm"
                                            onClick={() => handleEditTshirt(option, team)}
                                          >
                                            <Edit className="h-4 w-4 mr-1" />
                                            Edit
                                          </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                          <DialogHeader>
                                            <DialogTitle>Edit T-Shirt Option</DialogTitle>
                                            <DialogDescription>
                                              Update the t-shirt size for {team.name}.
                                            </DialogDescription>
                                          </DialogHeader>
                                          <form onSubmit={handleUpdateTshirt}>
                                            <div className="space-y-4">
                                              <div>
                                                <Label htmlFor="size">Size</Label>
                                                <Input
                                                  id="size"
                                                  value={tshirtForm.data.size}
                                                  onChange={(e) => tshirtForm.setData('size', e.target.value)}
                                                  placeholder="Enter t-shirt size"
                                                />
                                                {tshirtForm.errors.size && (
                                                  <p className="text-sm text-red-600 mt-1">{tshirtForm.errors.size}</p>
                                                )}
                                              </div>
                                            </div>
                                            <DialogFooter className="mt-4">
                                              <Button type="submit" disabled={tshirtForm.processing}>
                                                <Save className="h-4 w-4 mr-1" />
                                                {tshirtForm.processing ? 'Saving...' : 'Save Changes'}
                                              </Button>
                                            </DialogFooter>
                                          </form>
                                        </DialogContent>
                                      </Dialog>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </div>
                        ) : (
                          <p className="text-muted-foreground">No t-shirt sizes available for this team.</p>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Shirt className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No teams</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                      No teams have been created for this event yet.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="shoes" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Shoe Options</CardTitle>
                <CardDescription>
                  Available shoe options and sizes for this event.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {shoes.length > 0 ? (
                  <div className="space-y-6">
                    {shoes.map((shoe) => (
                      <div key={shoe.id} className="border rounded-lg p-4">
                        <div className="flex justify-between items-center mb-4">
                          <h3 className="text-lg font-semibold">{shoe.name}</h3>
                          <span className="text-lg font-bold">{formatCurrency(shoe.price)}</span>
                        </div>
                        {shoe.sizes.length > 0 ? (
                          <div className="rounded-md border">
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>Size</TableHead>
                                  <TableHead>Stock</TableHead>
                                  <TableHead>Reserved</TableHead>
                                  <TableHead>Available</TableHead>
                                  <TableHead>Status</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {shoe.sizes.map((size) => (
                                  <TableRow key={size.id}>
                                    <TableCell className="font-medium">{size.size}</TableCell>
                                    <TableCell>{size.stock}</TableCell>
                                    <TableCell>{size.reserved_stock}</TableCell>
                                    <TableCell>
                                      <span className={size.available_stock <= 0 ? 'text-red-600' : 'text-green-600'}>
                                        {size.available_stock}
                                      </span>
                                    </TableCell>
                                    <TableCell>
                                      {size.available_stock > 0 ? (
                                        <Badge variant="default">Available</Badge>
                                      ) : (
                                        <Badge variant="destructive">Out of Stock</Badge>
                                      )}
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </div>
                        ) : (
                          <p className="text-muted-foreground">No sizes available for this shoe.</p>
                        )}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <ShoppingBag className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No shoes</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                      No shoe options have been created for this event yet.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="registrations" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Registrations</CardTitle>
                <CardDescription>
                  All registrations for this fantasy event.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {registrations.length > 0 ? (
                  <div className="rounded-md border">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>User</TableHead>
                          <TableHead>Team</TableHead>
                          <TableHead>Registration Fee</TableHead>
                          <TableHead>Registration Status</TableHead>
                          <TableHead>Payment Status</TableHead>
                          <TableHead>Items</TableHead>
                          <TableHead>Created</TableHead>
                          <TableHead>Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {registrations.map((registration) => (
                          <TableRow key={registration.id}>
                            <TableCell>
                              <div>
                                <div className="font-medium">{registration.user.name}</div>
                                <div className="text-sm text-muted-foreground">{registration.user.email}</div>
                              </div>
                            </TableCell>
                            <TableCell>{registration.fantasy_event_team.name}</TableCell>
                            <TableCell>{formatCurrency(registration.registration_fee)}</TableCell>
                            <TableCell>{getStatusBadge(registration.status)}</TableCell>
                            <TableCell>
                              {registration.payments.length > 0 ? (
                                <div className="space-y-1">
                                  {registration.payments.map((payment, index) => (
                                    <div key={index}>
                                      {getStatusBadge(payment.status)}
                                    </div>
                                  ))}
                                </div>
                              ) : (
                                <Badge variant="outline">No Payment</Badge>
                              )}
                            </TableCell>
                            <TableCell>
                              <div className="space-y-1">
                                {registration.registration_items.map((item, index) => (
                                  <div key={index} className="text-sm">
                                    {item.fantasy_tshirt_option ? (
                                      <span>T-Shirt: {item.fantasy_tshirt_option.size}</span>
                                    ) : item.fantasy_shoe_size ? (
                                      <span>Shoe: {item.fantasy_shoe_size.fantasy_shoe.name} - {item.fantasy_shoe_size.size}</span>
                                    ) : (
                                      <span>Unknown item</span>
                                    )}
                                  </div>
                                ))}
                              </div>
                            </TableCell>
                            <TableCell>{formatDate(registration.created_at)}</TableCell>
                            <TableCell>
                              {registration.payments.length > 0 ? (
                                <div className="space-y-1">
                                  {registration.payments.map((payment, index) => (
                                    <Button 
                                      key={index}
                                      variant="outline" 
                                      size="sm" 
                                      onClick={() => handleEditPayment({
                                        ...payment,
                                        fantasy_registration: {
                                          id: registration.id,
                                          registration_code: `REG-${registration.id}`,
                                          user: registration.user
                                        }
                                      })}
                                    >
                                      <Edit className="h-4 w-4 mr-1" />
                                      Update Payment
                                    </Button>
                                  ))}
                                </div>
                              ) : (
                                <span className="text-muted-foreground text-sm">No actions</span>
                              )}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Trophy className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No registrations</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                      No registrations have been made for this event yet.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Payment Status Update Dialog */}
            {editingPayment && (
              <Dialog open={!!editingPayment} onOpenChange={() => setEditingPayment(null)}>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Update Payment Status</DialogTitle>
                    <DialogDescription>
                      Change the status of payment for {editingPayment.fantasy_registration.user.name}.
                    </DialogDescription>
                  </DialogHeader>
                  <form onSubmit={handleUpdatePaymentStatus}>
                    <div className="grid gap-4 py-4">
                      <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="status" className="text-right">
                          Status
                        </Label>
                        <Select
                          value={paymentForm.data.status}
                          onValueChange={(value) => paymentForm.setData('status', value)}
                        >
                          <SelectTrigger className="col-span-3">
                            <SelectValue placeholder="Select status" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="waiting">Waiting</SelectItem>
                            <SelectItem value="confirmed">Confirmed</SelectItem>
                            <SelectItem value="rejected">Rejected</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                            <SelectItem value="refunded">Refunded</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                    <DialogFooter>
                      <Button type="submit" disabled={paymentForm.processing}>
                        <Save className="h-4 w-4 mr-1" />
                        {paymentForm.processing ? 'Updating...' : 'Update Status'}
                      </Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            )}
          </TabsContent>

          <TabsContent value="payments" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Payments</CardTitle>
                <CardDescription>
                  All payments for this fantasy event with status management.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {payments.length > 0 ? (
                  <div className="rounded-md border">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Registration Code</TableHead>
                          <TableHead>User</TableHead>
                          <TableHead>Amount</TableHead>
                          <TableHead>Method</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Created</TableHead>
                          <TableHead>Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {payments.map((payment) => (
                          <TableRow key={payment.id}>
                            <TableCell className="font-medium">
                              {payment.fantasy_registration.registration_code}
                            </TableCell>
                            <TableCell>
                              <div>
                                <div className="font-medium">{payment.fantasy_registration.user.name}</div>
                                <div className="text-sm text-muted-foreground">{payment.fantasy_registration.user.email}</div>
                              </div>
                            </TableCell>
                            <TableCell>{formatCurrency(payment.amount)}</TableCell>
                            <TableCell className="capitalize">{payment.method}</TableCell>
                            <TableCell>{getStatusBadge(payment.status)}</TableCell>
                            <TableCell>{formatDate(payment.created_at)}</TableCell>
                            <TableCell>
                              <Dialog>
                                <DialogTrigger asChild>
                                  <Button variant="outline" size="sm" onClick={() => handleEditPayment(payment)}>
                                    <Edit className="h-4 w-4 mr-1" />
                                    Update Status
                                  </Button>
                                </DialogTrigger>
                                <DialogContent>
                                  <DialogHeader>
                                    <DialogTitle>Update Payment Status</DialogTitle>
                                    <DialogDescription>
                                      Change the status of payment for {payment.fantasy_registration.user.name}.
                                    </DialogDescription>
                                  </DialogHeader>
                                  <form onSubmit={handleUpdatePaymentStatus}>
                                    <div className="grid gap-4 py-4">
                                      <div className="grid grid-cols-4 items-center gap-4">
                                        <Label htmlFor="status" className="text-right">
                                          Status
                                        </Label>
                                        <Select
                                          value={paymentForm.data.status}
                                          onValueChange={(value) => paymentForm.setData('status', value)}
                                        >
                                          <SelectTrigger className="col-span-3">
                                            <SelectValue placeholder="Select status" />
                                          </SelectTrigger>
                                          <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="waiting">Waiting</SelectItem>
                                            <SelectItem value="confirmed">Confirmed</SelectItem>
                                            <SelectItem value="rejected">Rejected</SelectItem>
                                            <SelectItem value="failed">Failed</SelectItem>
                                            <SelectItem value="refunded">Refunded</SelectItem>
                                          </SelectContent>
                                        </Select>
                                      </div>
                                    </div>
                                    <DialogFooter>
                                      <Button type="submit" disabled={paymentForm.processing}>
                                        <Save className="h-4 w-4 mr-1" />
                                        {paymentForm.processing ? 'Updating...' : 'Update Status'}
                                      </Button>
                                    </DialogFooter>
                                  </form>
                                </DialogContent>
                              </Dialog>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <CreditCard className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-semibold text-muted-foreground">No payments</h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                      No payments have been made for this event yet.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </AppLayout>
  );
}