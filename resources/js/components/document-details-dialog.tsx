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
                        <div className="min-w-0 flex-1">
                            <DialogTitle className="text-lg">
                                {document.original_filename}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Detalles e información del documento
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
                            <Badge variant="destructive">Vencido</Badge>
                        )}
                        {!document.is_expired && document.expires_at && (
                            <Badge
                                variant="outline"
                                className="border-green-500 text-green-600"
                            >
                                Vigente
                            </Badge>
                        )}
                        <Badge variant="outline">{document.branch.name}</Badge>
                    </div>

                    {/* Description */}
                    {document.description && (
                        <div>
                            <h4 className="mb-2 text-sm font-medium">
                                Descripción
                            </h4>
                            <p className="text-sm text-muted-foreground">
                                {document.description}
                            </p>
                        </div>
                    )}

                    {/* Metadata Grid */}
                    <div className="grid grid-cols-2 gap-4 rounded-lg border p-4">
                        <div>
                            <p className="mb-1 text-xs text-muted-foreground">
                                Tamaño del Archivo
                            </p>
                            <p className="text-sm font-medium">
                                {formatBytes(document.file_size)}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-xs text-muted-foreground">
                                Tipo de Archivo
                            </p>
                            <p className="text-sm font-medium">
                                {document.mime_type}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-xs text-muted-foreground">
                                Subido por
                            </p>
                            <p className="text-sm font-medium">
                                {document.uploaded_by_user.name}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-xs text-muted-foreground">
                                Fecha de Subida
                            </p>
                            <p className="text-sm font-medium">
                                {formatDate(document.created_at)}
                            </p>
                        </div>
                        {document.expires_at && (
                            <div>
                                <p className="mb-1 text-xs text-muted-foreground">
                                    Fecha de Vencimiento
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
                            <p className="mb-1 text-xs text-muted-foreground">
                                Sucursal
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
                        <div className="space-y-2 rounded-lg border p-4">
                            <h4 className="mb-2 text-sm font-medium">
                                Información de la Sucursal
                            </h4>
                            {document.branch.address && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Dirección
                                    </p>
                                    <p className="text-sm">
                                        {document.branch.address}
                                    </p>
                                </div>
                            )}
                            {document.branch.contact_email && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Correo Electrónico
                                    </p>
                                    <p className="text-sm">
                                        {document.branch.contact_email}
                                    </p>
                                </div>
                            )}
                            {document.branch.contact_phone && (
                                <div>
                                    <p className="text-xs text-muted-foreground">
                                        Teléfono
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
                        Cerrar
                    </Button>
                    <Button onClick={() => onDownload(document)}>
                        <Download className="mr-2 h-4 w-4" />
                        Descargar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
