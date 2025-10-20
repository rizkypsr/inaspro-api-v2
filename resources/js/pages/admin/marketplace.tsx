import AppLayout from '@/layouts/app-layout'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Plus, Package, TrendingUp, Users, DollarSign } from 'lucide-react'
import { Head } from '@inertiajs/react'
import { type BreadcrumbItem } from '@/types'

export default function Marketplace() {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Marketplace', href: '/admin/marketplace' }
    ]

    // Mock data for demonstration
    const stats = [
        {
            title: 'Total Products',
            value: '1,234',
            description: '+20.1% from last month',
            icon: Package,
        },
        {
            title: 'Active Orders',
            value: '89',
            description: '+12.5% from last month',
            icon: TrendingUp,
        },
        {
            title: 'Total Customers',
            value: '2,350',
            description: '+5.2% from last month',
            icon: Users,
        },
        {
            title: 'Revenue',
            value: '$45,231.89',
            description: '+8.1% from last month',
            icon: DollarSign,
        },
    ]

    const recentProducts = [
        {
            id: 1,
            name: 'Wireless Headphones',
            category: 'Electronics',
            price: '$99.99',
            stock: 45,
            status: 'active',
        },
        {
            id: 2,
            name: 'Coffee Mug',
            category: 'Home & Garden',
            price: '$12.99',
            stock: 120,
            status: 'active',
        },
        {
            id: 3,
            name: 'Laptop Stand',
            category: 'Office Supplies',
            price: '$29.99',
            stock: 8,
            status: 'low_stock',
        },
        {
            id: 4,
            name: 'Yoga Mat',
            category: 'Sports & Fitness',
            price: '$24.99',
            stock: 0,
            status: 'out_of_stock',
        },
    ]

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return <Badge variant="default">Active</Badge>
            case 'low_stock':
                return <Badge variant="secondary">Low Stock</Badge>
            case 'out_of_stock':
                return <Badge variant="destructive">Out of Stock</Badge>
            default:
                return <Badge variant="outline">Unknown</Badge>
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Marketplace - Admin Panel" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Marketplace</h1>
                        <p className="text-muted-foreground">
                            Manage your products, orders, and marketplace settings
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Product
                    </Button>
                </div>

                <Separator />

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {stats.map((stat, index) => (
                        <Card key={index}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    {stat.title}
                                </CardTitle>
                                <stat.icon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stat.value}</div>
                                <p className="text-xs text-muted-foreground">
                                    {stat.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Recent Products */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Products</CardTitle>
                        <CardDescription>
                            A list of your recent products and their current status
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentProducts.map((product) => (
                                <div key={product.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center space-x-4">
                                        <div className="w-10 h-10 bg-muted rounded-md flex items-center justify-center">
                                            <Package className="h-5 w-5 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <p className="font-medium">{product.name}</p>
                                            <p className="text-sm text-muted-foreground">{product.category}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <p className="font-medium">{product.price}</p>
                                            <p className="text-sm text-muted-foreground">Stock: {product.stock}</p>
                                        </div>
                                        {getStatusBadge(product.status)}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}