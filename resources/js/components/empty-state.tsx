import { FileX2 } from 'lucide-react';

interface EmptyStateProps {
    title?: string;
    description?: string;
}

export function EmptyState({
    title = 'No documents found',
    description = "You don't have any documents available in your branches yet.",
}: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
            <div className="rounded-full bg-muted p-6 mb-4">
                <FileX2 className="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 className="text-lg font-semibold mb-2">{title}</h3>
            <p className="text-sm text-muted-foreground max-w-md">
                {description}
            </p>
        </div>
    );
}
