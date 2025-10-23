import { FileX2 } from 'lucide-react';

interface EmptyStateProps {
    title?: string;
    description?: string;
}

export function EmptyState({
    title = 'No se encontraron documentos',
    description = 'Aún no tienes documentos disponibles en tus sucursales.',
}: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center px-4 py-16 text-center">
            <div className="mb-4 rounded-full bg-muted p-6">
                <FileX2 className="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 className="mb-2 text-lg font-semibold">{title}</h3>
            <p className="max-w-md text-sm text-muted-foreground">
                {description}
            </p>
        </div>
    );
}
