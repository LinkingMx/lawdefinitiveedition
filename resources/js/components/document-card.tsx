import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from '@/components/ui/card';
import {
    formatBytes,
    formatDate,
    getFileIcon,
    truncateFilename,
} from '@/lib/document-utils';
import { Document } from '@/types';
import { Download, Eye } from 'lucide-react';

interface DocumentCardProps {
    document: Document;
    onDownload: (document: Document) => void;
    onViewDetails: (document: Document) => void;
}

export function DocumentCard({
    document,
    onDownload,
    onViewDetails,
}: DocumentCardProps) {
    const FileIcon = getFileIcon(document.mime_type);

    return (
        <Card
            className={`group transition-all duration-200 hover:shadow-lg ${
                document.is_expired ? 'border-red-500' : ''
            }`}
        >
            <CardHeader className="space-y-2 pb-3">
                <div className="flex items-start justify-between gap-2">
                    <Badge variant="secondary" className="text-xs">
                        {document.document_type.name}
                    </Badge>
                    {document.is_expired && (
                        <Badge variant="destructive" className="text-xs">
                            Vencido
                        </Badge>
                    )}
                    {!document.is_expired && document.expires_at && (
                        <Badge
                            variant="outline"
                            className="border-green-500 text-green-600 text-xs"
                        >
                            Vigente
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-3 pb-3">
                <div className="flex items-start gap-3">
                    <div className="rounded-lg bg-muted p-2.5">
                        <FileIcon className="h-6 w-6 text-muted-foreground" />
                    </div>
                    <div className="flex-1 min-w-0 space-y-1">
                        <h3
                            className="font-medium text-sm leading-tight truncate"
                            title={document.original_filename}
                        >
                            {truncateFilename(document.original_filename, 35)}
                        </h3>
                        {document.description && (
                            <p className="text-xs text-muted-foreground line-clamp-2">
                                {document.description}
                            </p>
                        )}
                    </div>
                </div>

                <div className="flex items-center justify-between text-xs text-muted-foreground pt-2 border-t">
                    <span>{formatBytes(document.file_size)}</span>
                    <span>{formatDate(document.created_at)}</span>
                </div>

                <div className="pt-1">
                    <Badge variant="outline" className="text-xs">
                        {document.branch.name}
                    </Badge>
                </div>
            </CardContent>

            <CardFooter className="flex gap-2 pt-3">
                <Button
                    variant="default"
                    size="sm"
                    className="flex-1"
                    onClick={() => onDownload(document)}
                >
                    <Download className="h-4 w-4 mr-1" />
                    Descargar
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onViewDetails(document)}
                >
                    <Eye className="h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
