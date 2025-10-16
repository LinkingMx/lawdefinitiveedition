import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Branch {
    id: number;
    name: string;
    address: string | null;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
}

export interface DocumentType {
    id: number;
    name: string;
    description: string | null;
}

export interface Document {
    id: number;
    document_type_id: number;
    branch_id: number;
    description: string | null;
    expires_at: string | null;
    file_path: string;
    original_filename: string;
    file_size: number;
    mime_type: string;
    uploaded_by: number;
    created_at: string;
    updated_at: string;
    is_expired: boolean;
    document_type: DocumentType;
    branch: Branch;
    uploaded_by_user: {
        id: number;
        name: string;
        avatar?: string;
    };
}

export interface PaginatedDocuments {
    data: Document[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
}
