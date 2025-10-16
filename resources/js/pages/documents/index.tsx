import { DocumentCard } from '@/components/document-card';
import { DocumentDetailsDialog } from '@/components/document-details-dialog';
import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import { download as documentsDownload } from '@/routes/documents';
import { Document, PaginatedDocuments } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface DocumentsPageProps {
    documents: PaginatedDocuments;
}

export default function DocumentsIndex({ documents }: DocumentsPageProps) {
    const [selectedDocument, setSelectedDocument] = useState<Document | null>(
        null,
    );
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [downloadingId, setDownloadingId] = useState<number | null>(null);

    const handleDownload = (document: Document) => {
        setDownloadingId(document.id);

        // Use router.visit for download to trigger file download
        router.visit(documentsDownload.url({ document: document.id }), {
            method: 'get',
            onSuccess: () => {
                toast.success('Descarga iniciada', {
                    description: `${document.original_filename} se está descargando.`,
                });
                setDownloadingId(null);
            },
            onError: () => {
                toast.error('Error en la descarga', {
                    description:
                        'Hubo un error al descargar el documento.',
                });
                setDownloadingId(null);
            },
        });
    };

    const handleViewDetails = (document: Document) => {
        setSelectedDocument(document);
        setIsDialogOpen(true);
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
                    <p className="text-muted-foreground mt-2">
                        Visualiza y descarga documentos de tus sucursales asignadas.
                    </p>
                </div>

                {/* Results count */}
                {documents.data.length > 0 && (
                    <div className="text-sm text-muted-foreground">
                        Mostrando {documents.meta.from} a {documents.meta.to} de{' '}
                        {documents.meta.total} documentos
                    </div>
                )}

                {/* Documents Grid */}
                {documents.data.length > 0 ? (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            {documents.data.map((document) => (
                                <DocumentCard
                                    key={document.id}
                                    document={document}
                                    onDownload={handleDownload}
                                    onViewDetails={handleViewDetails}
                                />
                            ))}
                        </div>

                        {/* Pagination */}
                        {documents.meta.last_page > 1 && (
                            <div className="flex justify-center items-center gap-2 mt-8">
                                {documents.links[0].url ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link href={documents.links[0].url}>
                                            <ChevronLeft className="h-4 w-4 mr-1" />
                                            Anterior
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled
                                    >
                                        <ChevronLeft className="h-4 w-4 mr-1" />
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
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link
                                            href={
                                                documents.links[
                                                    documents.links.length - 1
                                                ].url
                                            }
                                        >
                                            Siguiente
                                            <ChevronRight className="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled
                                    >
                                        Siguiente
                                        <ChevronRight className="h-4 w-4 ml-1" />
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
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {Array.from({ length: 8 }).map((_, i) => (
                <Skeleton key={i} className="h-64 rounded-lg" />
            ))}
        </div>
    );
}
