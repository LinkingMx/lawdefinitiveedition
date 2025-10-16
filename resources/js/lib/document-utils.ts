import {
    File,
    FileArchive,
    FileImage,
    FileSpreadsheet,
    FileText,
    FileVideo,
} from 'lucide-react';

/**
 * Format bytes to human-readable size (KB, MB, GB)
 */
export function formatBytes(bytes: number, decimals: number = 2): string {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

/**
 * Get appropriate icon component for mime type
 */
export function getFileIcon(mimeType: string) {
    if (mimeType.startsWith('image/')) {
        return FileImage;
    }

    if (mimeType.startsWith('video/')) {
        return FileVideo;
    }

    if (mimeType === 'application/pdf') {
        return FileText;
    }

    if (
        mimeType.includes('spreadsheet') ||
        mimeType.includes('excel') ||
        mimeType === 'text/csv'
    ) {
        return FileSpreadsheet;
    }

    if (
        mimeType.includes('zip') ||
        mimeType.includes('rar') ||
        mimeType.includes('tar') ||
        mimeType.includes('compressed')
    ) {
        return FileArchive;
    }

    return File;
}

/**
 * Format date to readable format (e.g., "Jan 15, 2025")
 */
export function formatDate(dateString: string): string {
    const date = new Date(dateString);

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

/**
 * Truncate long filenames while preserving extension
 */
export function truncateFilename(
    filename: string,
    maxLength: number = 30,
): string {
    if (filename.length <= maxLength) {
        return filename;
    }

    const extension = filename.split('.').pop() || '';
    const nameWithoutExt = filename.slice(0, -(extension.length + 1));
    const truncatedName = nameWithoutExt.slice(
        0,
        maxLength - extension.length - 4,
    );

    return `${truncatedName}...${extension}`;
}
