import { DocumentCard } from '@/components/document-card';
import { DocumentDetailsDialog } from '@/components/document-details-dialog';
import { DocumentsFilters } from '@/components/documents-filters';
import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import {
    download as documentsDownload,
    preview as documentsPreview,
} from '@/routes/documents';
import { Branch, Document, DocumentType, PaginatedDocuments } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface DocumentsPageProps {
    documents: PaginatedDocuments;
    documentTypes: DocumentType[];
    branches: Branch[];
    filters: {
        document_type_id?: string | null;
        branch_id?: string | null;
    };
}

export default function DocumentsIndex({
    documents,
    documentTypes,
    branches,
    filters,
}: DocumentsPageProps) {
    const [selectedDocument, setSelectedDocument] = useState<Document | null>(
        null,
    );
    const [isDialogOpen, setIsDialogOpen] = useState(false);

    const handleDownload = (document: Document) => {
        // Use direct link to trigger file download
        const downloadUrl = documentsDownload.url({ document: document.id });
        window.location.href = downloadUrl;

        toast.success('Descarga iniciada', {
            description: `${document.original_filename} se está descargando.`,
        });
    };

    const handleViewDetails = (document: Document) => {
        setSelectedDocument(document);
        setIsDialogOpen(true);
    };

    const handlePreview = (document: Document) => {
        // Open file in new tab for preview
        const previewUrl = documentsPreview.url({ document: document.id });
        window.open(previewUrl, '_blank');
    };

    const handleCloseDialog = () => {
        setIsDialogOpen(false);
        // Delay clearing selectedDocument to allow dialog animation to complete
        setTimeout(() => setSelectedDocument(null), 200);
    };

    return (
        <AppLayout>
            <Head title="Documentos" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Documentos
                    </h1>
                    <p className="mt-2 text-muted-foreground">
                        Visualiza y descarga documentos de tus sucursales
                        asignadas.
                    </p>
                </div>

                {/* Filters */}
                <DocumentsFilters
                    documentTypes={documentTypes}
                    branches={branches}
                    filters={filters}
                />

                {/* Results count */}
                {documents.data.length > 0 && documents.from && (
                    <div className="text-sm text-muted-foreground">
                        Mostrando {documents.from} a {documents.to} de{' '}
                        {documents.total} documentos
                    </div>
                )}

                {/* Documents Grid */}
                {documents.data.length > 0 ? (
                    <>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {documents.data.map((document) => (
                                <DocumentCard
                                    key={document.id}
                                    document={document}
                                    onDownload={handleDownload}
                                    onViewDetails={handlePreview}
                                />
                            ))}
                        </div>

                        {/* Pagination */}
                        {documents.last_page > 1 && (
                            <div className="mt-8 flex items-center justify-center gap-2">
                                {documents.links[0].url ? (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={documents.links[0].url}>
                                            <ChevronLeft className="mr-1 h-4 w-4" />
                                            Anterior
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled
                                    >
                                        <ChevronLeft className="mr-1 h-4 w-4" />
                                        Anterior
                                    </Button>
                                )}

                                <div className="flex items-center gap-2">
                                    {documents.links
                                        .slice(1, -1)
                                        .map((link, index) =>
                                            link.url ? (
                                                <Button
                                                    key={index}
                                                    variant={
                                                        link.active
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link href={link.url}>
                                                        {link.label}
                                                    </Link>
                                                </Button>
                                            ) : (
                                                <Button
                                                    key={index}
                                                    variant={
                                                        link.active
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                    size="sm"
                                                    disabled
                                                >
                                                    {link.label}
                                                </Button>
                                            ),
                                        )}
                                </div>

                                {documents.links[documents.links.length - 1]
                                    .url ? (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={
                                                documents.links[
                                                    documents.links.length - 1
                                                ].url
                                            }
                                        >
                                            Siguiente
                                            <ChevronRight className="ml-1 h-4 w-4" />
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled
                                    >
                                        Siguiente
                                        <ChevronRight className="ml-1 h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                        )}
                    </>
                ) : (
                    <EmptyState />
                )}
            </div>

            {/* Document Details Dialog */}
            <DocumentDetailsDialog
                document={selectedDocument}
                open={isDialogOpen}
                onClose={handleCloseDialog}
                onDownload={handleDownload}
            />
        </AppLayout>
    );
}

// Loading skeleton (can be used for initial page load)
function DocumentsLoading() {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {Array.from({ length: 8 }).map((_, i) => (
                <Skeleton key={i} className="h-64 rounded-lg" />
            ))}
        </div>
    );
}
