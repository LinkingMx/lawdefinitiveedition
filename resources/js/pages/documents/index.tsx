import { DocumentCard } from '@/components/document-card';
import { DocumentDetailsDialog } from '@/components/document-details-dialog';
import { EmptyState } from '@/components/empty-state';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import { Document, PaginatedDocuments } from '@/types';
import { Head, router } from '@inertiajs/react';
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
        router.visit(
            route('documents.download', {
                document: document.id,
            }),
            {
                method: 'get',
                onSuccess: () => {
                    toast.success('Download started', {
                        description: `${document.original_filename} is being downloaded.`,
                    });
                    setDownloadingId(null);
                },
                onError: () => {
                    toast.error('Download failed', {
                        description:
                            'There was an error downloading the document.',
                    });
                    setDownloadingId(null);
                },
            },
        );
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
            <Head title="Documents" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Documents
                    </h1>
                    <p className="text-muted-foreground mt-2">
                        View and download documents from your assigned branches.
                    </p>
                </div>

                {/* Results count */}
                {documents.data.length > 0 && (
                    <div className="text-sm text-muted-foreground">
                        Showing {documents.meta.from} to {documents.meta.to} of{' '}
                        {documents.meta.total} documents
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
                            <div className="flex justify-center mt-8">
                                <Pagination>
                                    <PaginationContent>
                                        {documents.links[0].url && (
                                            <PaginationItem>
                                                <PaginationPrevious
                                                    href={
                                                        documents.links[0].url ||
                                                        '#'
                                                    }
                                                />
                                            </PaginationItem>
                                        )}

                                        {documents.links
                                            .slice(1, -1)
                                            .map((link, index) => (
                                                <PaginationItem key={index}>
                                                    <PaginationLink
                                                        href={link.url || '#'}
                                                        isActive={link.active}
                                                    >
                                                        {link.label}
                                                    </PaginationLink>
                                                </PaginationItem>
                                            ))}

                                        {documents.links[
                                            documents.links.length - 1
                                        ].url && (
                                            <PaginationItem>
                                                <PaginationNext
                                                    href={
                                                        documents.links[
                                                            documents.links
                                                                .length - 1
                                                        ].url || '#'
                                                    }
                                                />
                                            </PaginationItem>
                                        )}
                                    </PaginationContent>
                                </Pagination>
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
