import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { formatBytes, formatDate, getFileIcon } from '@/lib/document-utils';
import { Document } from '@/types';
import { Download } from 'lucide-react';

interface DocumentDetailsDialogProps {
    document: Document | null;
    open: boolean;
    onClose: () => void;
    onDownload: (document: Document) => void;
}

export function DocumentDetailsDialog({
    document,
    open,
    onClose,
    onDownload,
}: DocumentDetailsDialogProps) {
    if (!document) return null;

    const FileIcon = getFileIcon(document.mime_type);

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-muted p-3">
                            <FileIcon className="h-6 w-6 text-muted-foreground" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <DialogTitle className="text-lg">
                                {document.original_filename}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Document details and information
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    {/* Badges */}
                    <div className="flex flex-wrap gap-2">
                        <Badge variant="secondary">
                            {document.document_type.name}
                        </Badge>
                        {document.is_expired && (
                            <Badge variant="destructive">Expired</Badge>
                        )}
                        {!document.is_expired && document.expires_at && (
                            <Badge
                                variant="outline"
                                className="border-green-500 text-green-600"
                            >
                                Valid
                            </Badge>
                        )}
                        <Badge variant="outline">{document.branch.name}</Badge>
                    </div>

                    {/* Description */}
                    {document.description && (
                        <div>
                            <h4 className="text-sm font-medium mb-2">
                                Description
                            </h4>
                            <p className="text-sm text-muted-foreground">
                                {document.description}
                            </p>
                        </div>
                    )}

                    {/* Metadata Grid */}
                    <div className="grid grid-cols-2 gap-4 rounded-lg border p-4">
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">
                                File Size
                            </p>
                            <p className="text-sm font-medium">
                                {formatBytes(document.file_size)}
                            </p>
                        </div>
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">
                                File Type
                            </p>
                            <p className="text-sm font-medium">
                                {document.mime_type}
                            </p>
                        </div>
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">
                                Uploaded By
                            </p>
                            <p className="text-sm font-medium">
                                {document.uploaded_by_user.name}
                            </p>
                        </div>
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">
                                Uploaded Date
                            </p>
                            <p className="text-sm font-medium">
                                {formatDate(document.created_at)}
                            </p>
                        </div>
                        {document.expires_at && (
                            <div>
                                <p className="text-xs text-muted-foreground mb-1">
                                    Expiration Date
                                </p>
                                <p
                                    className={`text-sm font-medium ${
                                        document.is_expired
                                            ? 'text-red-600'
                                            : ''
                                    }`}
                                >
                                    {formatDate(document.expires_at)}
                                </p>
                            </div>
                        )}
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">
                                Branch
                            </p>
                            <p className="text-sm font-medium">
                                {document.branch.name}
                            </p>
                        </div>
                    </div>

                    {/* Branch Details (if available) */}
                    {(document.branch.address ||
                        document.branch.contact_email ||
                        document.branch.contact_phone) && (
                        <div className="rounded-lg border p-4 space-y-2">
                            <h4 className="text-sm font-medium mb-2">
                                Branch Information
                            </h4>
                            {document.branch.address && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Address
                                    </p>
                                    <p className="text-sm">
                                        {document.branch.address}
                                    </p>
                                </div>
                            )}
                            {document.branch.contact_email && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Email
                                    </p>
                                    <p className="text-sm">
                                        {document.branch.contact_email}
                                    </p>
                                </div>
                            )}
                            {document.branch.contact_phone && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Phone
                                    </p>
                                    <p className="text-sm">
                                        {document.branch.contact_phone}
                                    </p>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Close
                    </Button>
                    <Button onClick={() => onDownload(document)}>
                        <Download className="h-4 w-4 mr-2" />
                        Download
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
