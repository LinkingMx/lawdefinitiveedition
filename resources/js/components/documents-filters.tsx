import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Branch, DocumentType } from '@/types';
import { router } from '@inertiajs/react';
import { Building2, FileText, Filter, X } from 'lucide-react';
import { useState } from 'react';

interface DocumentsFiltersProps {
    documentTypes: DocumentType[];
    branches: Branch[];
    filters: {
        document_type_id?: string | null;
        branch_id?: string | null;
    };
}

export function DocumentsFilters({
    documentTypes,
    branches,
    filters,
}: DocumentsFiltersProps) {
    const [documentTypeSearch, setDocumentTypeSearch] = useState('');
    const [branchSearch, setBranchSearch] = useState('');

    const hasActiveFilters = filters.document_type_id || filters.branch_id;

    const handleFilterChange = (key: string, value: string) => {
        const params = new URLSearchParams(window.location.search);

        if (value === 'all' || !value) {
            params.delete(key);
        } else {
            params.set(key, value);
        }

        router.get(
            `/documents?${params.toString()}`,
            {},
            { preserveState: true },
        );
    };

    const handleClearFilters = () => {
        router.get('/documents', {}, { preserveState: true });
    };

    // Filter document types based on search
    const filteredDocumentTypes = documentTypes.filter((type) =>
        type.name.toLowerCase().includes(documentTypeSearch.toLowerCase()),
    );

    // Filter branches based on search
    const filteredBranches = branches.filter((branch) =>
        branch.name.toLowerCase().includes(branchSearch.toLowerCase()),
    );

    return (
        <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <Filter className="h-4 w-4" />
                <span className="font-medium">Filtrar por:</span>
            </div>

            {/* Document Type Filter */}
            <div className="w-full sm:w-64">
                <Select
                    value={filters.document_type_id?.toString() || 'all'}
                    onValueChange={(value) =>
                        handleFilterChange('document_type_id', value)
                    }
                >
                    <SelectTrigger>
                        <div className="flex items-center gap-2">
                            <FileText className="h-4 w-4 text-muted-foreground" />
                            <SelectValue placeholder="Tipo de Documento" />
                        </div>
                    </SelectTrigger>
                    <SelectContent>
                        <div className="border-b p-2">
                            <input
                                type="text"
                                placeholder="Buscar tipo..."
                                className="w-full rounded-md border px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-ring"
                                value={documentTypeSearch}
                                onChange={(e) =>
                                    setDocumentTypeSearch(e.target.value)
                                }
                                onClick={(e) => e.stopPropagation()}
                            />
                        </div>
                        <SelectItem value="all">Todos los tipos</SelectItem>
                        {filteredDocumentTypes.length > 0 ? (
                            filteredDocumentTypes.map((type) => (
                                <SelectItem
                                    key={type.id}
                                    value={type.id.toString()}
                                >
                                    {type.name}
                                </SelectItem>
                            ))
                        ) : (
                            <div className="px-2 py-6 text-center text-sm text-muted-foreground">
                                No se encontraron resultados
                            </div>
                        )}
                    </SelectContent>
                </Select>
            </div>

            {/* Branch Filter */}
            <div className="w-full sm:w-64">
                <Select
                    value={filters.branch_id?.toString() || 'all'}
                    onValueChange={(value) =>
                        handleFilterChange('branch_id', value)
                    }
                >
                    <SelectTrigger>
                        <div className="flex items-center gap-2">
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                            <SelectValue placeholder="Sucursal" />
                        </div>
                    </SelectTrigger>
                    <SelectContent>
                        <div className="border-b p-2">
                            <input
                                type="text"
                                placeholder="Buscar sucursal..."
                                className="w-full rounded-md border px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-ring"
                                value={branchSearch}
                                onChange={(e) =>
                                    setBranchSearch(e.target.value)
                                }
                                onClick={(e) => e.stopPropagation()}
                            />
                        </div>
                        <SelectItem value="all">
                            Todas las sucursales
                        </SelectItem>
                        {filteredBranches.length > 0 ? (
                            filteredBranches.map((branch) => (
                                <SelectItem
                                    key={branch.id}
                                    value={branch.id.toString()}
                                >
                                    {branch.name}
                                </SelectItem>
                            ))
                        ) : (
                            <div className="px-2 py-6 text-center text-sm text-muted-foreground">
                                No se encontraron resultados
                            </div>
                        )}
                    </SelectContent>
                </Select>
            </div>

            {/* Clear Filters Button */}
            {hasActiveFilters && (
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleClearFilters}
                    className="gap-2"
                >
                    <X className="h-4 w-4" />
                    Limpiar filtros
                </Button>
            )}
        </div>
    );
}
