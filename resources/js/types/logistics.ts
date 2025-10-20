export interface Province {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

export interface ShippingRate {
    id: number;
    province_id: number;
    courier: string;
    rate: number;
    created_at: string;
    updated_at: string;
    province?: Province;
}

export interface PaginatedShippingRates {
    data: ShippingRate[];
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

export interface LogisticsFilters {
    search?: string;
    province_id?: string;
    courier?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
}

export interface LogisticsPageProps {
    shippingRates: PaginatedShippingRates;
    provinces: Province[];
    filters: LogisticsFilters;
}

export interface LogisticsFormData {
    province_id: string;
    courier: string;
    rate: string;
}

export interface LogisticsCreatePageProps {
    provinces: Province[];
}

export interface LogisticsEditPageProps {
    shippingRate: ShippingRate;
    provinces: Province[];
}

export interface LogisticsShowPageProps {
    shippingRate: ShippingRate;
}