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
            className={`group transition-all duration-200 hover:shadow-lg border-stone-200 dark:border-stone-800 ${
                document.is_expired ? 'border-red-500 dark:border-red-500' : ''
            }`}
        >
            <CardHeader className="space-y-2 pb-3">
                <div className="flex items-start justify-between gap-2">
                    <Badge
                        variant="secondary"
                        className="text-xs bg-[#897053]/10 text-[#897053] dark:bg-[#897053]/20 dark:text-[#b89876] border-[#897053]/20"
                    >
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
                            className="border-emerald-600 dark:border-emerald-500 text-emerald-700 dark:text-emerald-400 text-xs bg-emerald-50 dark:bg-emerald-950/30"
                        >
                            Vigente
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-3 pb-3">
                <div className="flex items-start gap-3">
                    <div className="rounded-lg bg-[#897053]/10 dark:bg-[#897053]/20 p-2.5">
                        <FileIcon className="h-6 w-6 text-[#897053] dark:text-[#b89876]" />
                    </div>
                    <div className="flex-1 min-w-0 space-y-1">
                        <h3
                            className="font-medium text-sm leading-tight truncate text-stone-900 dark:text-stone-100"
                            title={document.original_filename}
                        >
                            {truncateFilename(document.original_filename, 35)}
                        </h3>
                        {document.description && (
                            <p className="text-xs text-stone-600 dark:text-stone-400 line-clamp-2">
                                {document.description}
                            </p>
                        )}
                    </div>
                </div>

                <div className="flex items-center justify-between text-xs text-stone-600 dark:text-stone-400 pt-2 border-t border-stone-200 dark:border-stone-800">
                    <span>{formatBytes(document.file_size)}</span>
                    <span>{formatDate(document.created_at)}</span>
                </div>

                <div className="pt-1">
                    <Badge
                        variant="outline"
                        className="text-xs border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300"
                    >
                        {document.branch.name}
                    </Badge>
                </div>
            </CardContent>

            <CardFooter className="flex gap-2 pt-3">
                <Button
                    size="sm"
                    className="flex-1 bg-[#897053] hover:bg-[#6f5a42] dark:bg-[#897053] dark:hover:bg-[#a68764] text-white"
                    onClick={() => onDownload(document)}
                >
                    <Download className="h-4 w-4 mr-1" />
                    Descargar
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    className="border-[#897053] text-[#897053] hover:bg-[#897053]/10 dark:border-[#897053] dark:text-[#b89876] dark:hover:bg-[#897053]/20"
                    onClick={() => onViewDetails(document)}
                >
                    <Eye className="h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
